<?php
$pdo = connexionBaseDeDonnees();
$listTrainers = $pdo
    ->query("SELECT id, CONCAT(prenom,' ',nom) AS nom_complet FROM team ORDER BY nom_complet")
    ->fetchAll(PDO::FETCH_ASSOC);

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

// 4) Validation en cas de création ou modification
if ($isStore || $isUpdate) {
    $errors = validateClasseData($data, $isUpdate ? $id : null);
}

// 5) Déterminer si le formulaire doit rester visible
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// 6) Chargement du record
if ($isEdit) {
    if ($action === 'edit') {
        $record = getClasseById($id) ?: redirectToProfile('classes');
    } else {
        // réaffiche données saisies après erreur
        $record = $data;
    }
} elseif ($isCreate) {
    // formulaire vierge
    $record = ['nom' => '', 'niveau' => '', 'prix' => '', 'description' => '', 'team_id' => ''];
}

// 7) Traitements store/update/destroy
if ($isDestroy) {
    deleteClasse($id);
    setFlash('success', 'Cours supprimé.');
    redirectToProfile('classes');
}

if ($isStore && empty($errors)) {
    enregistrerClasse($data);
    setFlash('success', 'Cours créé.');
    redirectToProfile('classes');
}

if ($isUpdate && empty($errors)) {
    modifierClasse($id, [
        'nom' => $data['nom'],
        'niveau' => $data['niveau'],
        'prix' => $data['prix'],
        'description' => $data['description'],
        'team_id' => $data['team_id'],
    ]);
    setFlash('success', 'Cours mis à jour.');
    redirectToProfile('classes');
}

// 8) Affichage : form ou table
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

if (!$showForm) {
    $rows = $pdo->query("
        SELECT
          c.id,
          c.nom,
          c.niveau,
          CONCAT(c.prix,' €') AS prix_aff,
          LEFT(c.description,100) AS extrait_desc,
          CONCAT(t.prenom,' ',t.nom) AS entraineur,
          DATE_FORMAT(c.date_creation,'%d-%m-%Y') AS date_creation,
          DATE_FORMAT(c.updated_at,'%d-%m-%Y') AS date_modification
        FROM classes c
        LEFT JOIN team t ON t.id = c.team_id
        ORDER BY c.nom
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// 9) Configuration table.php
$headers = ['ID', 'Nom', 'Niveau', 'Prix', 'Extrait', 'Entraîneur', 'Création', 'Modif.'];
$fields = ['id', 'nom', 'niveau', 'prix_aff', 'extrait_desc', 'entraineur', 'date_creation', 'date_modification'];
$formatters = [];
$actions = [
    ['icon' => 'pencil-alt', 'label' => 'Modifier', 'params' => fn($r) => ['action' => 'edit', 'id' => $r['id']]],
    ['icon' => 'trash-alt', 'label' => 'Supprimer', 'confirm' => 'Supprimer ce cours ?', 'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']]],
];
?>

<?php displayFlash() ?>

<div class="flex justify-between mb-4">
    <h2 class="text-xl font-semibold">Gestion des cours</h2>
    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button class="bg-green-600 px-4 py-2 rounded text-white hover:bg-green-700">
                <i class="fas fa-plus mr-1"></i>Ajouter un cours
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (!$showForm): ?>

    <?php include __DIR__ . '/../components/table.php'; ?>

<?php else: ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4 bg-white p-6 rounded shadow">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'store' ?>">
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif ?>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="nom" class="block text-sm font-medium">Nom du cours</label>
                <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($record['nom'], ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="niveau" class="block text-sm font-medium">Niveau</label>
                <select name="niveau" id="niveau" class="w-full border p-2 rounded">
                    <?php foreach (['débutant', 'intermédiaire', 'avancé', 'tous niveaux'] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= ($record['niveau'] ?? '') === $lvl ? 'selected' : '' ?>>
                            <?= ucfirst($lvl) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="team_id" class="block text-sm font-medium">Entraîneur</label>
                <select name="team_id" id="team_id" required
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($listTrainers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= (string) ($record['team_id'] ?? '') === (string) $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div>
                <label for="prix" class="block text-sm font-medium">Prix (€)</label>
                <input type="number" name="prix" id="prix" step="0.01" required
                    value="<?= htmlspecialchars($record['prix'], ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium">Description</label>
            <textarea name="description" id="description" rows="4" required
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($record['description'], ENT_QUOTES) ?></textarea>
        </div>

        <div class="flex space-x-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
            </button>
            <a href="profile.php?page=classes"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded">
                Annuler
            </a>
        </div>
    </form>
<?php endif; ?>