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
    $errors = validatePostData($data, $isUpdate ? $id : null);
}

// 5) On reste en “mode édition” si on vient de cliquer “edit” ou si un update a échoué
$isEdit = $isEdit || ($isUpdate && !empty($errors));

// 6) Chargement du record pour le formulaire
if ($isEdit) {
    if ($action === 'edit') {
        $record = getPostById($id);
        $record['tags'] = implode(', ', getTagsForPost($id));
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
    ];
}

// 7) Traitements store / update / destroy
if ($isDestroy) {
    deletePost($id);
    setFlash('success', 'Article supprimé.');
}

if ($isStore && empty($errors)) {
    $newId = enregistrerPost($data);
    syncPostTags($newId, $data['tags'] ?? '');
    setFlash('success', 'Article créé.');
}

if ($isUpdate && empty($errors)) {
    modifierPost($id, [
        'titre' => $data['titre'],
        'contenu' => $data['contenu'],
        'auteur' => $data['auteur'],
    ]);
    syncPostTags($id, $data['tags'] ?? '');
    setFlash('success', 'Article mis à jour.');
}

// 8) Ou on affiche le tableau
$showForm = $isCreate || $isEdit || (!empty($errors) && ($isStore || $isUpdate));

if (!$showForm) {
    $all = getAllPosts();

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
$headers = ['ID', 'Titre', 'Extrait', 'Auteur', 'Tags', 'Créé le', 'Modifié le'];
$fields = ['id', 'titre', 'excerpt', 'auteur_nom', 'tags', 'created_at', 'updated_at'];
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

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Gestion des actualités</h2>
    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                <i class="fas fa-plus mr-1"></i>Ajouter
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (!$showForm): ?>

    <!-- Tableau générique -->
    <?php include __DIR__ . '/../components/table.php'; ?>
    <?php include __DIR__ . '/../components/pagination.php'; ?>

<?php else: ?>

    <!-- Affichage des erreurs -->
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
            <label class="block text-sm font-medium">Titre</label>
            <input type="text" name="titre" required value="<?= htmlspecialchars($record['titre'] ?? '', ENT_QUOTES) ?>"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <?php if ($isEdit) { ?>
                <label class="block text-sm font-medium">Auteur</label>
                <select name="auteur" required class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                    <?php foreach (getAllAuthors() as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($a['id'] == ($record['auteur'] ?? 0)) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nom_complet'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php } else { ?>
                <input type="hidden" name="auteur" value="<?= $_SESSION['user']['id'] ?>">
            <?php } ?>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contenu</label>
            <textarea name="contenu" rows="6" required
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($record['contenu'] ?? '', ENT_QUOTES) ?></textarea>
        </div>

        <div>
            <label for="tags" class="block text-sm font-medium text-gray-700">
                Tags (séparés par des virgules)
            </label>
            <input type="text" name="tags" id="tags" value="<?= htmlspecialchars($record['tags'] ?? '') ?>"
                placeholder="ex : compétition, stage, club"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            <small class="text-gray-500">Chaque mot sera transformé en tag.</small>
        </div>

        <div class="flex space-x-4">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
                <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
            </button>
            <a href="profile.php?page=posts"
                class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded transition">
                Annuler
            </a>
        </div>
    </form>

<?php endif; ?>