<?php
// includes/profile/posts.php

// 1) Lecture de l’action et de l’ID
$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

// 2) Flags métier
$isCreate = $action === 'create';
$isEdit = $action === 'edit' && $id > 0;
$isStore = $action === 'store';
$isUpdate = $action === 'update' && $id > 0;
$isDestroy = $action === 'destroy' && $id > 0;

// 3) Nettoyage initial des données soumises
$data = $_POST ? nettoyerDonnees($_POST) : [];
$errors = [];

// 4) Validation en création ou mise à jour
if ($isStore || $isUpdate) {
    $errors = validerDonneesPost($data);
}

// 5) On reste en “mode édition” si on vient de cliquer “edit” ou si un update a échoué
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// 6) Chargement du record pour le formulaire
if ($isEdit) {
    if ($action === 'edit') {
        $record = getPostParId($id);
        $record['tags'] = implode(', ', getTagsPourPost($id));
    } else {
        // update raté → on ré-affiche les valeurs que l’utilisateur venait de saisir
        $record = $data;
    }
}
// mode création vierge
elseif ($isCreate) {
    $record = [
        'titre' => '',
        'contenu' => '',
        'auteur' => $_SESSION['user']['id'],
        'tags' => '',
        'photo' => '',
    ];
}

// 7) Traitements store / update / destroy
if ($isDestroy) {
    supprimerPost($id);
    setFlash('success', 'Article supprimé.');
}

if ($isStore && empty($errors)) {
    $newId = enregistrerPost($data);
    // Si le post a été créé avec succès (donc id > 0)
    if ($newId > 0) {
        syncPostTags($newId, $data['tags'] ?? '');
        setFlash('success', 'Article créé.');
    } else {
        setFlash('error', 'Erreur ! Article non créé.');
    }
}

if ($isUpdate && empty($errors)) {
    modifierPost($id, [
        'titre' => $data['titre'],
        'contenu' => $data['contenu'],
        'auteur' => $data['auteur'],
        'photo' => $data['photo'],
    ]);
    syncPostTags($id, $data['tags'] ?? '');
    setFlash('success', 'Article mis à jour.');
}

// 8) Ou on affiche le tableau
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

if (!$showForm) {
    $all = getListePosts();

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

// 9) Configuration du composant table.php
$headers = ['ID', 'Titre', 'Extrait', 'Auteur', 'Tags', 'Photo', 'Créé le', 'Modifié le'];
$fields = ['id', 'titre', 'excerpt', 'auteur_nom', 'tags', 'photo', 'created_at', 'updated_at'];
$formatters = [
    'excerpt' => fn($txt) => htmlspecialchars(mb_strimwidth(strip_tags($txt), 0, 50, '…'), ENT_QUOTES),
];
$actions = [
    [
        'icon' => 'pencil-alt',
        'label' => 'Modifier',
        'params' => fn($r) => ['action' => 'edit', 'id' => $r['id']],
    ],
    [
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'confirm' => 'Supprimer cet article ?',
        'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']],
    ],
];
?>

<?php displayFlash(); ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Gestion des actualités</h2>
    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button
                class="w-full sm:w-auto flex items-center justify-center bg-blue-600 px-4 py-2 rounded-lg text-white font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter un article
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
            <?= $isEdit ? 'Modifier l\'article' : 'Créer un nouvel article' ?>
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

            <?php if ($isEdit): ?>
                <div>
                    <label for="auteur" class="block text-sm font-medium text-slate-700">Auteur</label>
                    <select name="auteur" id="auteur" required
                        class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach (getListeAuteurs() as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= ($a['id'] == ($record['auteur'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nom_complet'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="auteur" value="<?= $_SESSION['user']['id'] ?>">
            <?php endif; ?>

            <div class="flex flex-col-reverse sm:flex-row gap-4 pt-4 border-t border-slate-200">
                <a href="profile.php?page=posts"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-slate-100 text-slate-800 rounded-lg hover:bg-slate-200 transition">
                    Annuler
                </a>
                <button type="submit"
                    class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <?= $isEdit ? 'Mettre à jour l\'article' : 'Créer l\'article' ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>