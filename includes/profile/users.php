<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 *
 * Présentation du fichier : Page de gestion des utilisateurs
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

// Nettoyage initial des POST
$data = $_POST ? nettoyerDonnees($_POST) : array();
$errors = array(); // Initialisation du tableau d'erreurs

// Validation en création ou mise à jourr
if ($isStore || $isUpdate) {
    $errors = validerDonneesUtilisateur($data, $isUpdate ? $id : null);
}

// Mode "édition" si clic edit ou update raté
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// Chargement du record pour le formulaire
if ($isEdit) {
    try {
        if ($action === 'edit') {
            $record = getUtilisateurParId($id);
            if (!$record) {
                setFlash('error', "Utilisateur introuvable pour l'édition.");
                $isEdit = false; // Sortir du mode édition
                $showForm = false; // Afficher la liste
            }
        } else {
            $record = $data;
        }
    } catch (Exception $e) {
        logErreur("Partial includes/profile/users.php ", $e->getMessage(), array('id' => $id, 'action' => $action, ));
        setFlash('error', "Erreur lors du chargement des informations de l'utilisateur.");
        $isEdit = false; // Quitter le mode édition en cas d'erreur de chargement
        $showForm = false; // Afficher la liste
    }
}
// création vierge (record initialisé avec des valeurs vides)
elseif ($isCreate) {
    $record = array('email' => '', 'prenom' => '', 'nom' => '', 'role' => 'membre', );
}

// Traitements métier (suppression, enregistrement, mise à jour)
// Utilisation de blocs try-catch individuels pour un contrôle fin et des messages flash
if ($isDestroy) {
    try {
        supprimerUtilisateur($id);
        setFlash('success', 'Utilisateur supprimé.');
    } catch (Exception $e) {
        logErreur("Partial includes/profile/users.php ", $e->getMessage(), array('id' => $id, 'action' => $action, ));
        setFlash('error', "Erreur lors de la suppression de l'utilisateur.");
    }
}

if ($isStore && empty($errors)) {
    try {
        $data['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
        if (enregistrerUtilisateur($data)) {
            setFlash('success', 'Utilisateur créé.');
            $showForm = false; // Revenir à la liste après création réussie
        } else {
            setFlash('error', "Erreur lors de la création de l'utilisateur.");
        }
    } catch (Exception $e) {
        logErreur("Partial includes/profile/users.php ", $e->getMessage(), array('data' => $data, 'action' => $action, ));
        setFlash('error', "Erreur lors de l'enregistrement de l'utilisateur.");
    }
}

if ($isUpdate && empty($errors)) {
    try {
        $fields_to_update = array(
            'email' => $data['email'],
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'role' => $data['role'],
        );
        if (!empty($data['motdepasse'])) {
            $fields_to_update['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
        }

        if (modifierUtilisateur($id, $fields_to_update)) {
            setFlash('success', 'Utilisateur mis à jour.');
            $showForm = false; // Revenir à la liste après mise à jour réussie
        } else {
            setFlash('error', "Échec de la mise à jour de l'utilisateur.");
        }
    } catch (Exception $e) {
        logErreur("Partial includes/profile/users.php ", $e->getMessage(), array('id' => $id, 'data' => $data, 'action' => $action, ));
        setFlash('error', "Erreur lors de la mise à jour de l'utilisateur.");
    }
}

// Déterminer l'affichage final : formulaire ou tableau de liste
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

if (!$showForm) {
    try {
        $baseUrl = "profile.php?page=" . $pageActuelle;
        $all = getListeUtilisateurs(); // Récupération de tous les utilisateurs pour le tableau
        // chargement du tableau avec pagination
        $pag = paginateArray($all, 'p', 5);
        // on remplace les rows par le slice
        $rows = $pag['slice'];
        // et on récupère les infos de pagination
        extract($pag); // pageNum, perPage, total, totalPages, offset, slice
        $start = $pag['offset'] + 1;
        $end = min($pag['offset'] + $perPage, $total);
    } catch (Exception $e) {
        logErreur("Partial includes/profile/users.php ", $e->getMessage());
        setFlash('error', 'Impossible de charger la liste des utilisateurs. Veuillez réessayer plus tard.');
        $all = array(); // S'assurer que $all est un tableau vide en cas d'erreur
        $rows = array(); // S'assurer que $rows est vide pour ne pas casser la table
        // Initialiser les variables de pagination si besoin pour éviter des erreurs dans le HTML
        $pageNum = 1;
        $perPage = 5;
        $total = 0;
        $totalPages = 1;
        $offset = 0;
        $start = 0;
        $end = 0;
    }
}

// Configuration table.php
$headers = array('ID', 'Nom', 'Email', 'Rôle', 'Création', 'Modification', );
$fields = array('id', 'nom_complet', 'email', 'role', 'date_creation', 'date_modification', );
$formatters = array(
    'role' => fn($r) => "<span class=\"inline-flex px-2.5 py-1 rounded-full text-xs font-semibold "
        . ($r === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')
        . "\">" . htmlspecialchars(ucfirst($r), ENT_QUOTES) . "</span>"
    ,
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
        'confirm' => 'Supprimer cet utilisateur ?',
        'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']],
    ),
);
?>

<!-- Affichage -->
<?php displayFlash(); ?>

<!-- Toolbar -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Gestion des utilisateurs</h2>
    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button
                class="w-full sm:w-auto flex items-center justify-center bg-blue-600 px-4 py-2 rounded-lg text-white font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter un utilisateur
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (!$showForm): ?>
    <div class="bg-white rounded-lg shadow-md border border-slate-200">
        <?php include __DIR__ . '/../components/table.php'; ?>
        <?php include __DIR__ . '/../components/pagination.php'; ?>
    </div>

<?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-6 border border-slate-200">
        <h3 class="text-xl font-bold text-slate-800 mb-6">
            <?= $isEdit ? 'Modifier l\'utilisateur' : 'Créer un nouvel utilisateur' ?>
        </h3>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-700 border-l-4 border-red-500 rounded-md" role="alert">
                <p class="font-bold mb-2">Erreurs de validation :</p>
                <ul class="list-disc pl-5 space-y-1">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'store' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES) ?>">
            <?php endif; ?>

            <div class="grid lg:grid-cols-2 gap-6">
                <div>
                    <label for="prenom" class="block text-sm font-medium text-slate-700">Prénom</label>
                    <input type="text" name="prenom" id="prenom" required
                        value="<?= htmlspecialchars($record['prenom'] ?? '') ?>"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="nom" class="block text-sm font-medium text-slate-700">Nom</label>
                    <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($record['nom'] ?? '') ?>"
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($record['email'] ?? '') ?>"
                    class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-slate-700">Rôle</label>
                <select name="role" id="role"
                    class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="membre" <?= ($record['role'] ?? 'membre') === 'membre' ? 'selected' : '' ?>>Membre</option>
                    <option value="admin" <?= ($record['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <fieldset class="border-t border-slate-200 pt-6 space-y-4">
                <legend class="text-base font-semibold text-slate-800 -mt-3">Mot de passe</legend>
                <p class="text-sm text-slate-500 -mt-4">
                    <?= $isEdit ? 'Laissez les champs vides pour ne pas le modifier.' : 'Le mot de passe est requis à la création.' ?>
                </p>
                <div class="grid lg:grid-cols-2 gap-6">
                    <div>
                        <label for="motdepasse" class="block text-sm font-medium text-slate-700">Mot de passe</label>
                        <input type="password" name="motdepasse" id="motdepasse" <?= !$isEdit ? 'required' : '' ?>
                            class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="confirm" class="block text-sm font-medium text-slate-700">Confirmation</label>
                        <input type="password" name="confirm" id="confirm" <?= !$isEdit ? 'required' : '' ?>
                            class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </fieldset>

            <div class="flex flex-col-reverse sm:flex-row gap-4 pt-4 border-t border-slate-200">
                <a href="profile.php?page=users"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-slate-100 text-slate-800 rounded-lg hover:bg-slate-200 transition">
                    Annuler
                </a>
                <button type="submit"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <?= $isEdit ? 'Mettre à jour l\'utilisateur' : 'Créer l' . "'" . 'utilisateur' ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>