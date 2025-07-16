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
    $errors = validerDonnesEntraineur($data);  // TODO: ajouter excludeId si besoin
}

// 5) Détermine le « mode édition » si clic edit ou update raté
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// 6) Chargement du record pour le form
if ($isEdit) {
    if ($action === 'edit') {
        $record = getEntraineurParId($id);
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
    supprimerEntraineur($id);
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
    $all = getListeEntraineurs();

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
                <label for="photo" class="block text-sm font-medium text-slate-700">URL de la Photo (ex:
                    `img/nom.jpg`)</label>
                <input type="text" name="photo" id="photo"
                    value="<?= htmlspecialchars($record['photo'] ?? '', ENT_QUOTES) ?>"
                    class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-4 pt-4 border-t border-slate-200">
                <a href="profile.php?page=team"
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