<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 *
 * Présentation du fichier : Page de gestion des actualités
 *
 */

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

// Flags métier
$isCreate = $action === 'create';
$isEdit = $action === 'edit' && $id > 0;
$isStore = $action === 'store';
$isUpdate = $action === 'update' && $id > 0;
$isDestroy = $action === 'destroy' && $id > 0;

// Nettoyage des données soumises
$data = $_POST ? nettoyerDonnees($_POST) : array();
$errors = array(); // Initialisation du tableau d'erreurs

// Validation des donnéess en création ou mise à jour
if ($isStore || $isUpdate) {
    $errors = validerDonneesPost($data);
}

// Mode "édition" si clic edit ou update raté
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// Chargement du post pour le formulaire
if ($isEdit) {
    try {
        if ($action === 'edit') {
            $record = getPostParId($id);  // récupérer le post
            if (!$record) {
                setFlash('error', "Article introuvable pour l'édition.");
                $isEdit = false; // Sortir du mode édition
                $showForm = false; // Afficher le tableau de liste
            } else {
                $record['tags'] = implode(', ', getTagsPourPost($id)); // Récupérer les tags associés sous format csv
            }
        } else {
            // update raté => on ré-affiche les valeurs que l’utilisateur venait de saisir
            $record = $data;
        }
    } catch (Exception $e) {
        logErreur("Partial includes/profile/posts.php ", $e->getMessage(), array('id' => $id, 'action' => $action, ));
        setFlash('error', "Erreur lors du chargement des informations de l\'article.");
        $isEdit = false; // Quitter le mode édition en cas d'erreur de chargement
        $showForm = false; // Afficher la liste
    }
}
// mode création vierge
elseif ($isCreate) {
    $record = array(
        'titre' => '',
        'contenu' => '',
        'auteur' => $_SESSION['user']['id'], // Auteur est par défaut l'utilisateur connecté
        'tags' => '',
        'photo' => '',
    );
}

// Traitements store / update / destroy
// Utilisation de blocs try-catch individuels pour un contrôle fin et des messages flash

// Cas 1 : Suppression
if ($isDestroy) {
    try {
        supprimerPost($id);
        setFlash('success', 'Article supprimé.');
    } catch (Exception $e) {
        logErreur("Partial includes/profile/posts.php ", $e->getMessage(), array('id' => $id, 'action' => $action, ));
        setFlash('error', "Erreur lors de la suppression de l\'article.");
    }
}

// Cas 2 : Création
if ($isStore && empty($errors)) {
    try {
        $newId = enregistrerPost($data);
        // Si le post a été créé avec succès (donc id > 0)
        if ($newId > 0) {
            syncPostTags($newId, $data['tags'] ?? '');
            setFlash('success', 'Article créé.');
            $showForm = false; // Après création réussie, on repasse en mode affichage tableau
        } else {
            setFlash('error', 'Erreur ! Article non créé.');
        }
    } catch (Exception $e) {
        logErreur("Partial includes/profile/posts.php ", $e->getMessage(), array('data' => $data, 'action' => $action, ));
        setFlash('error', "Erreur lors de la création de l\'article.");
    }
}

// Cas 3 : Modification
if ($isUpdate && empty($errors)) {
    try {
        modifierPost($id, array(
            'titre' => $data['titre'],
            'contenu' => $data['contenu'],
            'auteur' => $data['auteur'],
            'photo' => $data['photo'],
        ));
        syncPostTags($id, $data['tags'] ?? '');
        setFlash('success', 'Article mis à jour.');
        $showForm = false; // Après mise à jour réussie, on repasse en mode affichage tableau
    } catch (Exception $e) {
        logErreur("Partial includes/profile/posts.php ", $e->getMessage(), array('id' => $id, 'data' => $data, 'action' => $action, ));
        setFlash('error', "Erreur lors de la mise à jour de l\'article.");
    }
}

// Déterminer l'affichage final : formulaire ou tableau de liste
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

// Chargement de la liste des auteurs pour le formulaire d'édition/création (si nécessaire)
$listAuteurs = array();
if ($showForm) {
    try {
        $listAuteurs = getListeAuteurs();  // Récupération de la liste des auteurs
    } catch (Exception $e) {
        logErreur("Partial includes/profile/posts.php ", $e->getMessage());
        setFlash('error', 'Impossible de charger la liste des auteurs.');
        $listAuteurs = array();
    }
}

// Si on affiche le tableau
if (!$showForm) {
    try {
        $all = getListePosts(); // Récupération de tous les articles pour le tableau
    } catch (Exception $e) {
        logErreur("Partial includes/profile/posts.php ", $e->getMessage());
        setFlash('error', 'Impossible de charger la liste des actualités. Veuillez réessayer plus tard.');
        $all = array(); // S'assurer que $all est un tableau vide en cas d'erreur
    }

    $baseUrl = "profile.php?page=" . $pageActuelle;
    // chargement du tableau avec pagination
    $pag = paginateArray($all, 'p', 5);
    // on remplace les rows par le slice
    $rows = $pag['slice'];
    // et on récupère les infos de pagination
    extract($pag); // pageNum, perPage, total, totalPages, offset, slice
    $start = $pag['offset'] + 1;
    $end = min($pag['offset'] + $perPage, $total);
}

// Configuration du composant table.php
$headers = array('ID', 'Titre', 'Extrait', 'Auteur', 'Tags', 'Photo', 'Créé le', 'Modifié le', );
$fields = array('id', 'titre', 'excerpt', 'auteur_nom', 'tags', 'photo', 'created_at', 'updated_at', );
$formatters = array(
    'excerpt' => fn($txt) => htmlspecialchars(mb_strimwidth(strip_tags($txt), 0, 50, '…'), ENT_QUOTES),
);
$actions = array(
    array(
        'icon' => 'pencil-alt',
        'label' => 'Modifier',
        'params' => fn($r) => ['action' => 'edit', 'id' => $r['id']],
    ),
    array(
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'confirm' => 'Supprimer cet article ?',
        'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']],
    ),
);
?>

<?php displayFlash(); ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Gestion des actualités</h2>
    <?php if (!$showForm) { ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button
                class="w-full sm:w-auto flex items-center justify-center bg-blue-600 px-4 py-2 rounded-lg text-white font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter un article
            </button>
        </form>
    <?php } ?>
</div>


<?php if (!$showForm) { ?>
    <div class="bg-white rounded-lg shadow-md border border-slate-200 overflow-hidden">
        <?php include __DIR__ . '/../components/table.php'; ?>
        <?php include __DIR__ . '/../components/pagination.php'; ?>
    </div>
<?php } else { ?>
    <div class="bg-white rounded-lg shadow-md p-6 border border-slate-200">
        <h3 class="text-xl font-bold text-slate-800 mb-6">
            <?= $isEdit ? 'Modifier l\'article' : 'Créer un nouvel article' ?>
        </h3>

        <?php if (!empty($errors)) { ?>
            <div class="mb-6 p-4 bg-red-100 text-red-700 border-l-4 border-red-500 rounded-md" role="alert">
                <p class="font-bold mb-2">Erreurs de validation :</p>
                <ul class="list-disc pl-5 space-y-1">
                    <?php foreach ($errors as $e) { ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <form method="post" class="space-y-6">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'store' ?>">
            <?php if ($isEdit) { ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES) ?>">
            <?php } ?>

            <div>
                <label for="titre" class="block text-sm font-medium text-slate-700">Titre</label>
                <input type="text" id="titre" name="titre" required
                    value="<?= htmlspecialchars($record['titre'] ?? '', ENT_QUOTES) ?>"
                    class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="contenu" class="block text-sm font-medium text-slate-700">Contenu</label>
                <textarea name="contenu" id="contenu" rows="8" required
                    class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($record['contenu'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tags" class="block text-sm font-medium text-slate-700">Tags</label>
                    <input type="text" name="tags" id="tags"
                        value="<?= htmlspecialchars($record['tags'] ?? '', ENT_QUOTES) ?>"
                        placeholder="compétition, stage, club"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-500">Séparés par des virgules.</p>
                </div>
                <div>
                    <label for="photo" class="block text-sm font-medium text-slate-700">URL de la photo</label>
                    <input type="text" name="photo" id="photo"
                        value="<?= htmlspecialchars($record['photo'] ?? '', ENT_QUOTES) ?>" placeholder="img/nom-image.jpg"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <?php if ($isEdit) { // && !empty($listAuteurs) TODO: rendre cette condition utilisable ?>
                <div>
                    <label for="auteur" class="block text-sm font-medium text-slate-700">Auteur</label>
                    <select name="auteur" id="auteur" required
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php
                        foreach ($listAuteurs as $a) { ?>
                            <option value="<?= $a['id'] ?>" <?= ($a['id'] == ($record['auteur'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nom_complet'], ENT_QUOTES) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            <?php } else { ?>
                <input type="hidden" name="auteur" value="<?= $_SESSION['user']['id'] ?>">
            <?php } ?>

            <div class="flex flex-col-reverse sm:flex-row gap-4 pt-4 border-t border-slate-200">
                <a href="profile.php?page=posts"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-slate-100 text-slate-800 rounded-lg hover:bg-slate-200 transition">
                    Annuler
                </a>
                <button type="submit"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <?= $isEdit ? "Mettre à jour l'article" : "Créer l'article" ?>
                </button>
            </div>
        </form>
    </div>
<?php } ?>