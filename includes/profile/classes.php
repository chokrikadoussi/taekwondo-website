<?php

$listTrainers = getListeEntraineurs();

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
    $errors = validerDonnesCours($data);
}

// 5) Déterminer si le formulaire doit rester visible
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// 6) Chargement du record
if ($isEdit) {
    if ($action === 'edit') {
        $record = getCoursParId($id);
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
    supprimerCours($id);
    setFlash('success', 'Cours supprimé.');
}

if ($isStore && empty($errors)) {
    enregistrerCours($data);
    setFlash('success', 'Cours créé.');
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
}

// 8) Affichage : form ou table
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

if (!$showForm) {
    $all = getListeCours();

    $baseUrl = "profile.php?page=" . $pageActuelle;
    // chargement du tableau
    $pag = paginateArray($all, 'p', 5);
    // on remplace les rows par le slice
    $rows = $pag['slice'];
    // et on récupère les infos de pagination
    extract($pag); // pageNum, perPage, total, totalPages, offset, slice
    $start = $pag['offset'] + 1;
    $end = min($pag['offset'] + $perPage, $total);
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

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Gestion des cours</h2>
    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button
                class="w-full sm:w-auto flex items-center justify-center bg-blue-600 px-4 py-2 rounded-lg text-white font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter un cours
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
            <?= $isEdit ? 'Modifier le cours' : 'Créer un nouveau cours' ?>
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
            <?php if ($isEdit): ?><input type="hidden" name="id"
                    value="<?= htmlspecialchars($id, ENT_QUOTES) ?>"><?php endif ?>

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
                        <?php foreach (['débutant', 'intermédiaire', 'avancé', 'tous niveaux'] as $lvl): ?>
                            <option value="<?= $lvl ?>" <?= ($record['niveau'] ?? '') === $lvl ? 'selected' : '' ?>>
                                <?= ucfirst($lvl) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="team_id" class="block text-sm font-medium text-slate-700">Entraîneur</label>
                    <select name="team_id" id="team_id" required
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choisir --</option>
                        <?php foreach ($listTrainers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= (string) ($record['team_id'] ?? '') === (string) $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach ?>
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
<?php endif; ?>