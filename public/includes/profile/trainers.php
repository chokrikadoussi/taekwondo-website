<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 *
 * Présentation du fichier : Page de gestion des entraineurs
 *
 * TODO:
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

// Nettoyage des données
$data = $_POST ? nettoyerDonnees($_POST) : array();
$errors = array(); // Initialisation du tableau d'erreurs

// Validation en création ou mise à jour
if ($isStore || $isUpdate) {
    $errors = validerDonnesEntraineur($data);
}

// Mode "édition" si clic edit ou update raté
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// Chargement du record pour le formulaire
if ($isEdit) {
    try {
        if ($action === 'edit') {
            $record = getEntraineurParId($id);  // Récupération des données de l'entraineur
            if (!$record) {
                setFlash('error', "Entraîneur introuvable pour l'édition.");
                $isEdit = false; // Sortir du mode édition si non trouvé
                $showForm = false; // Afficher le tableau de liste
            }
        } else {
            // update raté → on réaffiche les données saisies
            $record = $data;
        }
    } catch (Exception $e) {
        logErreur("Partial includes/profile/trainers.php ", $e->getMessage(), array('id' => $id, 'action' => $action, ));
        setFlash('error', "Erreur lors du chargement des informations de l'entraîneur.");
        $isEdit = false; // Quitter le mode édition en cas d'erreur de chargement
        $showForm = false; // Afficher la liste
    }
}
// création vierge
elseif ($isCreate) {
    $record = array('prenom' => '', 'nom' => '', 'bio' => '', 'photo' => '', );
}

// 7) Traitements store/update/destroy
// Utilisation de blocs try-catch individuels pour un contrôle fin et des messages flash
if ($isDestroy) {
    try {
        supprimerEntraineur($id);
        setFlash('success', 'Entraîneur supprimé.');
    } catch (Exception $e) {
        logErreur("Partial includes/profile/trainers.php ", $e->getMessage(), array('id' => $id, 'action' => $action, ));
        setFlash('error', "Erreur lors de la suppression de l'entraîneur.");
    }
}

if ($isStore && empty($errors)) {
    try {
        enregistrerEntraineur($data);
        setFlash('success', 'Entraîneur créé.');
        $showForm = false; // Réinitialiser $showForm à false pour afficher le tableau après succès
    } catch (Exception $e) {
        logErreur("Partial includes/profile/trainers.php ", $e->getMessage(), array('data' => $data, 'action' => $action, ));
        setFlash('error', "Erreur lors de la création de l'entraîneur.");
    }
}

if ($isUpdate && empty($errors)) {
    try {
        modifierEntraineur($id, array(
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'bio' => $data['bio'],
            'photo' => $data['photo'] ?? null,
        ));
        setFlash('success', 'Entraîneur mis à jour.');
        $showForm = false; // Réinitialiser $showForm à false pour afficher le tableau après succès
    } catch (Exception $e) {
        logErreur("Partial includes/profile/trainers.php ", $e->getMessage(), array('id' => $id, 'data' => $data, 'action' => $action, ));
        setFlash('error', "Erreur lors de la mise à jour de l'entraîneur.");
    }
}

// Déterminer l'affichage final : formulaire ou tableau de liste
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

if (!$showForm) {
    try {
        $all = getListeEntraineurs(); // Récupération de tous les entraîneurs pour le tableau
    } catch (Exception $e) {
        logErreur("Partial includes/profile/trainers.php ", $e->getMessage());
        setFlash('error', 'Impossible de charger la liste des entraîneurs. Veuillez réessayer plus tard.');
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

// Config du composant table.php
$headers = array('ID', 'Nom', 'Extrait bio', 'Création', 'Modification', );
$fields = array('id', 'nom_complet', 'extrait_bio', 'date_creation', 'date_modification', );
$formatters = array(); // pas de format particulier
$actions = array(
    array('icon' => 'pencil-alt', 'label' => 'Modifier', 'params' => fn($r) => ['action' => 'edit', 'id' => $r['id']], ),
    array('icon' => 'trash-alt', 'label' => 'Supprimer', 'confirm' => 'Supprimer cet entraîneur ?', 'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']], ),
);
?>

<?php displayFlash() ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Gestion des entraîneurs</h2>
    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button
                class="w-full sm:w-auto flex items-center justify-center bg-blue-600 px-4 py-2 rounded-lg text-white font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter un entraîneur
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (!$showForm): ?>
    <div class="bg-white rounded-lg shadow-md border border-slate-200 overflow-hidden">
        <?php include __DIR__ . '/../components/table.php'; ?>
        <?php include __DIR__ . '/../components/pagination.php'; ?>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-6 border border-slate-200">
        <h3 class="text-xl font-bold text-slate-800 mb-6">
            <?= $isEdit ? 'Modifier l\'entraîneur' : 'Créer un nouvel entraîneur' ?>
        </h3>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-700 border-l-4 border-red-500 rounded-md" role="alert">
                <p class="font-bold mb-2">Erreurs de validation :</p>
                <ul class="list-disc pl-5 space-y-1">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'store' ?>">
            <?php if ($isEdit): ?><input type="hidden" name="id"
                    value="<?= htmlspecialchars($id, ENT_QUOTES) ?>"><?php endif ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="prenom" class="block text-sm font-medium text-slate-700">Prénom</label>
                    <input type="text" name="prenom" id="prenom" required
                        value="<?= htmlspecialchars($record['prenom'] ?? '', ENT_QUOTES) ?>"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="nom" class="block text-sm font-medium text-slate-700">Nom</label>
                    <input type="text" name="nom" id="nom" required
                        value="<?= htmlspecialchars($record['nom'] ?? '', ENT_QUOTES) ?>"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="bio" class="block text-sm font-medium text-slate-700">Biographie</label>
                <textarea name="bio" id="bio" rows="5" required
                    class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($record['bio'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <div>
                <label for="photo" class="block text-sm font-medium text-slate-700">URL de la Photo</label>
                <input type="text" name="photo" id="photo" placeholder="img/nom-image.jpg"
                    value="<?= htmlspecialchars($record['photo'] ?? '', ENT_QUOTES) ?>"
                    class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-4 pt-4 border-t border-slate-200">
                <a href="profile.php?page=trainers"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-slate-100 text-slate-800 rounded-lg hover:bg-slate-200 transition">
                    Annuler
                </a>
                <button type="submit"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>