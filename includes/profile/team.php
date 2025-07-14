<?php
// 1) Lecture action + ID
$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

// 2) Flags métier
$isCreate = $action === 'create';
$isEdit = $action === 'edit' && $id > 0;
$isStore = $action === 'store';
$isUpdate = $action === 'update' && $id > 0;
$isDestroy = $action === 'destroy' && $id > 0;

// 3) Nettoyage initial
$data = $_POST ? nettoyerDonnees($_POST) : [];
$errors = [];

// 4) Validation en store/update
if ($isStore || $isUpdate) {
    $errors = validateTrainerData($data, $isUpdate ? $id : null);
}

// 5) Détermine le « mode édition » si clic edit ou update raté
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// 6) Chargement du record pour le form
if ($isEdit) {
    if ($action === 'edit') {
        $record = getTrainerById($id);
    } else {
        // update raté → on réaffiche les données saisies
        $record = $data;
    }
}
// création vierge
elseif ($isCreate) {
    $record = ['prenom' => '', 'nom' => '', 'bio' => '', 'photo' => ''];
}

// 7) Traitements store/update/destroy
if ($isDestroy) {
    deleteEntraineur($id);
    setFlash('success', 'Entraîneur supprimé.');
}

if ($isStore && empty($errors)) {
    enregistrerEntraineur($data);
    setFlash('success', 'Entraîneur créé.');
}

if ($isUpdate && empty($errors)) {
    modifierEntraineur($id, [
        'prenom' => $data['prenom'],
        'nom' => $data['nom'],
        'bio' => $data['bio'],
        'photo' => $data['photo'] ?? null
    ]);
    setFlash('success', 'Entraîneur mis à jour.');
}

// 8) Choix form ou tableau
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

// 9) Si pas de form, on charge le tableau
if (!$showForm) {
    $rows = connexionBaseDeDonnees()
        ->query("
            SELECT id,
                   CONCAT(prenom,' ',nom) AS nom_complet,
                   LEFT(bio,100) AS extrait_bio,
                   DATE_FORMAT(created_at,'%d-%m-%Y') AS date_creation,
                   DATE_FORMAT(updated_at,'%d-%m-%Y') AS date_modification
            FROM team
            ORDER BY id
        ")
        ->fetchAll(PDO::FETCH_ASSOC);
}

// 10) Config du composant table.php
$headers = ['ID', 'Nom', 'Extrait bio', 'Création', 'Modification'];
$fields = ['id', 'nom_complet', 'extrait_bio', 'date_creation', 'date_modification'];
$formatters = []; // pas de format particulier
$actions = [
    ['icon' => 'pencil-alt', 'label' => 'Modifier', 'params' => fn($r) => ['action' => 'edit', 'id' => $r['id']]],
    ['icon' => 'trash-alt', 'label' => 'Supprimer', 'confirm' => 'Supprimer cet entraîneur ?', 'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']]],
];
?>

<?php displayFlash() ?>

<div class="flex justify-between mb-4">
    <h2 class="text-xl font-semibold">Gestion des entraîneurs</h2>
    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button class="bg-green-600 px-4 py-2 rounded text-white hover:bg-green-700">
                <i class="fas fa-plus mr-1"></i>Ajouter
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (!$showForm): ?>

    <!-- Tableau générique -->
    <?php include __DIR__ . '/../components/table.php'; ?>

<?php else: ?>

    <!-- Affichage des erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulaire create/edit -->
    <form method="post" class="space-y-4 bg-white p-6 rounded shadow">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'store' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif ?>

        <div>
            <label for="prenom" class="block text-sm font-medium">Prénom</label>
            <input type="text" name="prenom" id="prenom" required
                value="<?= htmlspecialchars($record['prenom'] ?? '', ENT_QUOTES) ?>"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="nom" class="block text-sm font-medium">Nom</label>
            <input type="text" name="nom" id="nom" required
                value="<?= htmlspecialchars($record['nom'] ?? '', ENT_QUOTES) ?>"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="bio" class="block text-sm font-medium">Biographie</label>
            <textarea name="bio" id="bio" rows="4" required
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($record['bio'] ?? '', ENT_QUOTES) ?></textarea>
        </div>

        <div>
            <label for="photo" class="block text-sm font-medium">URL Photo (optionnel)</label>
            <input type="url" name="photo" id="photo" value="<?= htmlspecialchars($record['photo'] ?? '', ENT_QUOTES) ?>"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex space-x-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
            </button>
            <a href="profile.php?page=team"
                class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded">
                Annuler
            </a>
        </div>
    </form>
<?php endif; ?>