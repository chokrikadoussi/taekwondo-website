<?php
// --- 1) Lecture de l’action et de l’ID
$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

// --- 2) Flags métier
$isCreate = $action === 'create';
$isEdit = $action === 'edit' && $id > 0;
$isStore = $action === 'store';
$isUpdate = $action === 'update' && $id > 0;
$isDestroy = $action === 'destroy' && $id > 0;

// --- 3) Nettoyage initial des POST
$data = $_POST ? nettoyerDonnees($_POST) : [];
$errors = [];

// --- 4) Validation en cas de creation ou update
if ($isStore || $isUpdate) {
    $errors = validerDonneesUtilisateur($data, $isUpdate ? $id : null);
}

// --- 5) Détermine si on reste en mode édition après un update raté
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// --- 6) Chargement du record pour le form
if ($isEdit) {
    // édition initiale
    if ($action === 'edit') {
        $record = getUtilisateurParId($id);
    }
    // update raté
    else {
        $record = $data;
    }
}
// création vierge
elseif ($isCreate) {
    $record = ['email' => '', 'prenom' => '', 'nom' => '', 'role' => 'membre'];
}

// --- 7) Traitements métier
if ($isDestroy) {
    supprimerUtilisateur($id);
    setFlash('success', 'Utilisateur supprimé.');
}

if ($isStore && empty($errors)) {
    $data['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
    enregistrerUtilisateur($data);
    setFlash('success', 'Utilisateur créé.');
}

if ($isUpdate && empty($errors)) {
    if (!empty($data['motdepasse'])) {
        $data['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
    }
    modifierUtilisateur($id, [
        'email' => $data['email'],
        'prenom' => $data['prenom'],
        'nom' => $data['nom'],
        'role' => $data['role']
    ]);
    setFlash('success', 'Utilisateur mis à jour.');
}

// --- 8) Affichage : choix entre form ou tableau
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

if (!$showForm) {

    $baseUrl = "profile.php?page=" . $pageActuelle;

    $all = getListeUtilisateurs();    
    // chargement du tableau
    $pag = paginateArray($all, 'p', 5);
    // on remplace les rows par le slice
    $rows = $pag['slice'];
    // et on récupère les infos de pagination
    extract($pag); // pageNum, perPage, total, totalPages, offset, slice
    $start = $pag['offset'] + 1;
    $end = min($pag['offset'] + $perPage, $total);

}

// --- 9) Configuration table.php
$headers = ['ID', 'Nom', 'Email', 'Rôle', 'Création', 'Modification'];
$fields = ['id', 'nom_complet', 'email', 'role', 'date_creation', 'date_modification'];
$formatters = [
    'role' => fn($r) => "<span class=\"inline-flex px-2 py-0.5 rounded text-xs font-medium "
        . ($r === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800')
        . "\">" . htmlspecialchars(ucfirst($r), ENT_QUOTES) . "</span>"
];
$actions = [
    [
        'icon' => 'pencil-alt',
        'label' => 'Modifier',
        'params' => fn($r) => ['action' => 'edit', 'id' => $r['id']]
    ],
    [
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'confirm' => 'Supprimer cet utilisateur ?',
        'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']]
    ],
];
?>

<!-- Affichage -->
<?php displayFlash(); ?>

<!-- Toolbar -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Gestion des utilisateurs</h2>
    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button class="bg-blue-600 px-4 py-2 rounded text-white hover:bg-blue-700">
                <i class="fas fa-plus mr-1"></i>Ajouter
            </button>
        </form>
    <?php endif; ?>
</div>

<!-- Liste ou formulaire -->
<?php if (!$showForm): ?>
    <div>
        <?php include __DIR__ . '/../components/table.php'; ?>
        <?php include __DIR__ . '/../components/pagination.php'; ?>
    </div>
    
<?php else: ?>
    <!-- Affichage des erreurs si présentes -->
    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulaire create/edit -->
    <form method="post" class="space-y-4 bg-white p-6 rounded">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'store' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif; ?>


        <div>
            <label for="email">Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($record['email'] ?? '') ?>"
                class="w-full border p-2 rounded">
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            <div>
                <label for="prenom">Prénom</label>
                <input type="text" name="prenom" required value="<?= htmlspecialchars($record['prenom'] ?? '') ?>"
                    class="w-full border p-2 rounded">
            </div>
            <div>
                <label for="nom">Nom</label>
                <input type="text" name="nom" required value="<?= htmlspecialchars($record['nom'] ?? '') ?>"
                    class="w-full border p-2 rounded">
            </div>
        </div>

        <div>
            <label for="role">Rôle</label>
            <select name="role" class="w-full border p-2 rounded">
                <option value="membre" <?= $record['role'] === 'membre' ? 'selected' : '' ?>>Membre</option>
                <option value="admin" <?= $record['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            <div>
                <label>Mot de passe <?= $isEdit ? '<small>(laisser vide pour garder)</small>' : '' ?></label>
                <input type="password" name="motdepasse" <?= $isEdit ? '' : 'required' ?> class="w-full border p-2 rounded">
            </div>
            <div>
                <label>Confirmation</label>
                <input type="password" name="confirm" <?= $isEdit ? '' : 'required' ?> class="w-full border p-2 rounded">
            </div>
        </div>

        <div class="flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
            </button>
            <a href="profile.php?page=users" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
                Annuler
            </a>
        </div>
    </form>

<?php endif; ?>