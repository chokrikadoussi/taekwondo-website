<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 *
 * Présentation du fichier : Page de gestion des cours
 * 
 * Informations techniques concernant la configuration du composant table.php :
 *   - Headers     : Détermine le nom des en-têtes affiché dans la table
 *   - Fields      : Détermine les champs techniques récupérer en base de données via fonctions get*
 *   - Formatters  : Détermine les fonctions de formattage des données récupérées (Couleur spécifique, code html a retourné)
 *   - Actions     : Détermine les actions à effectuer sur les données récupérées via des fonctions callback (Modifier, Supprimer)
 *
 * TODO:
 *
 */

// Chargement de la liste des entraineurs
try {
    $listTrainers = getListeEntraineurs(); // Chargement des entraîneurs pour les sélecteurs
} catch (Exception $e) {
    logErreur("Partial includes/profile/classes.php ", $e->getMessage());
    setFlash('error', 'Impossible de charger la liste des entraîneurs. Veuillez réessayer plus tard.');
    $listTrainers = array(); // S'assurer que $listTrainers est un tableau vide en cas d'erreur
}

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

// Flags métier permettant de diriger l'affichage
$isCreate = $action === 'create';
$isEdit = $action === 'edit' && $id > 0;
$isStore = $action === 'store';
$isUpdate = $action === 'update' && $id > 0;
$isDestroy = $action === 'destroy' && $id > 0;

// Nettoyage initial
$data = $_POST ? nettoyerDonnees($_POST) : array();
$errors = array(); // Initialisation du tableau d'erreurs

// Validation des données en cas de création ou modification
if ($isStore || $isUpdate) {
    $errors = validerDonneesCours($data);
}

// Déterminer si le formulaire doit rester visible (après "edit", "create" ou après échec de validation)
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// Chargement du cours pour le formulaire d'édition (mise à jour)
if ($isEdit) {
    try {
        if ($action === 'edit') {
            $record = getCoursParId($id);
            if (!$record) {
                setFlash('error', 'Cours introuvable pour l\'édition.');
                $isEdit = false; // Forcer la sortie du mode édition si le cours n'est pas trouvé
                $showForm = false; // Afficher la liste
            }
        } else {
            // Permet de garder les données saisies après erreur de validation
            $record = $data;
        }
    } catch (Exception $e) {
        logErreur("Partial includes/profile/classes.php ", $e->getMessage(), array('id' => $id, 'action' => $action, ));
        setFlash('error', 'Erreur lors du chargement des informations du cours.');
        $isEdit = false; // Quitter le mode édition en cas d'erreur de chargement
        $showForm = false; // Afficher la liste
    }
} elseif ($isCreate) {
    // formulaire vierge pour la création
    $record = array('nom' => '', 'niveau' => '', 'prix' => '', 'description' => '', 'team_id' => '', );
}

// Traitements store/update/destroy
// Utilisation de blocs try-catch individuels pour un contrôle fin et des messages flash

// Cas 1 : Suppression
if ($isDestroy) {
    try {
        supprimerCours($id);
        setFlash('success', 'Cours supprimé.');
    } catch (Exception $e) {
        logErreur("Partial includes/profile/classes.php ", $e->getMessage(), ['id' => $id, 'action' => $action]);
        setFlash('error', 'Erreur lors de la suppression du cours.');
    }
}

// Cas 2 : Création
if ($isStore && empty($errors)) {
    try {
        enregistrerCours($data);
        setFlash('success', 'Cours créé.');
        $showForm = false;  // Si succès, fermer le formulaire et afficher la liste
    } catch (Exception $e) {
        logErreur("Partial includes/profile/classes.php ", $e->getMessage(), ['data' => $data, 'action' => $action]);
        setFlash('error', 'Erreur lors de la création du cours.');
    }
}

// Cas 3 : Modification
if ($isUpdate && empty($errors)) {
    try {
        modifierClasse($id, array(
            'nom' => $data['nom'],
            'niveau' => $data['niveau'],
            'prix' => $data['prix'],
            'description' => $data['description'],
            'team_id' => $data['team_id'],
        ));
        setFlash('success', 'Cours mis à jour.');
        $showForm = false;  // Si succès, fermer le formulaire et afficher la liste
    } catch (Exception $e) {
        logErreur("Partial includes/profile/classes.php ", $e->getMessage(), ['id' => $id, 'data' => $data, 'action' => $action]);
        setFlash('error', 'Erreur lors de la mise à jour du cours.');
    }
}

// Déterminer l'affichage final : formulaire ou tableau de liste
// $showForm est recalculé car les opérations CRUD peuvent le modifier pour forcer l'affichage du tableau.
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

// Affichage du tableau
if (!$showForm) {
    try {
        $all = getListeCours(); // Récupération de tous les cours pour le tableau
    } catch (Exception $e) {
        logErreur("Partial includes/profile/classes.php ", $e->getMessage());
        setFlash('error', 'Impossible de charger la liste des cours. Veuillez réessayer plus tard.');
        $all = array(); // S'assurer que $all est un tableau vide en cas d'erreur
    }

    $baseUrl = "profile.php?page=" . $pageActuelle;
    // chargement du tableau avec pagination
    $pag = paginateArray($all, 'p', 5);
    // on remplace les rows par le slice paginé
    $rows = $pag['slice'];
    // et on récupère les infos de pagination
    extract($pag); // pageNum, perPage, total, totalPages, offset, slice
    $start = $pag['offset'] + 1;
    $end = min($pag['offset'] + $perPage, $total);
}

// Configuration des en-têtes et champs pour le composant table.php
$headers = array('ID', 'Nom', 'Niveau', 'Prix', 'Extrait', 'Entraîneur', 'Création', 'Modif.', );
$fields = array('id', 'nom', 'niveau', 'prix_aff', 'extrait_desc', 'entraineur', 'date_creation', 'date_modification', );
$formatters = array();
$actions = array(
    // Utilisation de arrow function permettant d'accéder à l'ID de la ligne sélectionner par ligne
    array('icon' => 'pencil-alt', 'label' => 'Modifier', 'params' => fn($r) => ['action' => 'edit', 'id' => $r['id']]),
    array('icon' => 'trash-alt', 'label' => 'Supprimer', 'confirm' => 'Supprimer ce cours ?', 'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']]),
);
?>

<?php displayFlash() ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Gestion des cours</h2>
    <?php if (!$showForm) { ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button
                class="w-full sm:w-auto flex items-center justify-center bg-blue-600 px-4 py-2 rounded-lg text-white font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter un cours
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
            <?= $isEdit ? 'Modifier le cours' : 'Créer un nouveau cours' ?>
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
            <?php if ($isEdit) { ?><input type="hidden" name="id"
                    value="<?= htmlspecialchars($id, ENT_QUOTES) ?>"><?php } ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nom" class="block text-sm font-medium text-slate-700">Nom du cours</label>
                    <input type="text" name="nom" id="nom" required
                        value="<?= htmlspecialchars($record['nom'] ?? '', ENT_QUOTES) ?>"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="niveau" class="block text-sm font-medium text-slate-700">Niveau</label>
                    <select name="niveau" id="niveau"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach (['débutant', 'intermédiaire', 'avancé', 'tous niveaux'] as $lvl) { ?>
                            <option value="<?= $lvl ?>" <?= ($record['niveau'] ?? '') === $lvl ? 'selected' : '' ?>>
                                <?= ucfirst($lvl) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="team_id" class="block text-sm font-medium text-slate-700">Entraîneur</label>
                    <select name="team_id" id="team_id" required
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choisir --</option>
                        <?php foreach ($listTrainers as $t) { ?>
                            <option value="<?= $t['id'] ?>" <?= (string) ($record['team_id'] ?? '') === (string) $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="prix" class="block text-sm font-medium text-slate-700">Prix mensuel (€)</label>
                    <input type="number" name="prix" id="prix" step="0.01" required
                        value="<?= htmlspecialchars($record['prix'] ?? '', ENT_QUOTES) ?>"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" id="description" rows="4" required
                    class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($record['description'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-4 pt-4 border-t border-slate-200">
                <a href="profile.php?page=classes"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-slate-100 text-slate-800 rounded-lg hover:bg-slate-200 transition">
                    Annuler
                </a>
                <button type="submit"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <?= $isEdit ? 'Mettre à jour le cours' : 'Créer le cours' ?>
                </button>
            </div>
        </form>
    </div>
<?php } ?>