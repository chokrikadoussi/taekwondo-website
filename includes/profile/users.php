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
    'role' => fn($r) => "<span class=\"inline-flex px-2.5 py-1 rounded-full text-xs font-semibold "
        . ($r === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800')
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