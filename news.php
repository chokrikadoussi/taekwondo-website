<?php
session_start();
$pageTitle = 'Actualités';
$pageActuelle = 'news';
require __DIR__ . '/fonction/fonctions.php';

// 1) Lecture des paramètres
$filterTag = $_GET['tag'] ?? '';
$sort = $_GET['sort'] ?? 'desc';
$perPage = 9;
$page = max(1, (int) ($_GET['page'] ?? 1));

// 2) Tags + posts
$allTags = getListeTags();
$allPosts = getListePosts(150, $filterTag ?: null, $sort);
$total = count($allPosts);
$pages = (int) ceil($total / $perPage);
$offset = ($page - 1) * $perPage;
$posts = array_slice($allPosts, $offset, $perPage);

// pour construire les URLs en conservant tag+sort
$baseParams = [];
if ($filterTag)
    $baseParams['tag'] = $filterTag;
if ($sort)
    $baseParams['sort'] = $sort;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-gray-50">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 space-y-8">
        <h1 class="text-3xl font-bold text-center">Nos actualités</h1>

        <!-- Filtre & Tri -->
        <form method="get" class="flex flex-wrap justify-between items-center gap-4 mb-8">
            <!-- Conserver le sort actuel si on change le tag -->
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort, ENT_QUOTES) ?>">
            <!-- Conserver le tag actuel si on change le sort -->
            <input type="hidden" name="tag" value="<?= htmlspecialchars($filterTag, ENT_QUOTES) ?>">
            <!-- Toujours repartir à la page 1 quand on filtre/tri -->
            <input type="hidden" name="page" value="1">

            <!-- Sélecteur de tag -->
            <div class="flex items-center gap-2">
                <label for="tag-select" class="font-medium">Filtrer par tag :</label>
                <select name="tag" id="tag-select" class="border rounded px-3 py-1">
                    <option value="" <?= $filterTag === '' ? 'selected' : '' ?>>Tous</option>
                    <?php foreach ($allTags as $t): ?>
                        <option value="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>" <?= $filterTag === $t['name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sélecteur de tri -->
            <div class="flex items-center gap-2">
                <label for="sort-select" class="font-medium">Trier :</label>
                <select name="sort" id="sort-select" class="border rounded px-3 py-1">
                    <option value="desc" <?= $sort === 'desc' ? 'selected' : '' ?>>Plus récentes</option>
                    <option value="asc" <?= $sort === 'asc' ? 'selected' : '' ?>>Plus anciennes</option>
                </select>
            </div>

            <!-- Bouton d’application -->
            <div>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded transition">
                    Appliquer
                </button>
            </div>
        </form>

        <!-- Grille d’articles -->
        <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($posts as $post): ?>
                <article
                    class="bg-white rounded-2xl shadow hover:shadow-xl transform hover:-translate-y-1 transition flex flex-col">
                    <a href="news_detail.php?id=<?= $post['id'] ?>" class="flex-1 flex flex-col">
                        <div class="h-40 bg-gray-100 flex items-center justify-center text-gray-400">
                            <i class="fas fa-newspaper fa-2x"></i>
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <h2 class="text-2xl font-semibold mb-2 line-clamp-2">
                                <?= htmlspecialchars($post['titre'], ENT_QUOTES) ?>
                            </h2>
                            <p class="text-sm text-gray-500 mb-4">
                                Par <strong><?= htmlspecialchars($post['auteur_nom'], ENT_QUOTES) ?></strong> —
                                <time><?= htmlspecialchars($post['created_at'], ENT_QUOTES) ?></time>
                            </p>
                            <p class="text-gray-700 flex-grow mb-4 line-clamp-3">
                                <?= htmlspecialchars($post['excerpt'], ENT_QUOTES) ?>…
                            </p>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <?php foreach (explode(',', $post['tags'] ?? '') as $tag):
                                    $tag = trim($tag);
                                    if (!$tag)
                                        continue; ?>
                                    <a href="news.php?<?= http_build_query(array_merge($baseParams, ['tag' => $tag])) ?>"
                                        class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        <?= htmlspecialchars($tag, ENT_QUOTES) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </a>
                    <div class="p-6 pt-0">
                        <a href="news_detail.php?id=<?= $post['id'] ?>"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                            Lire la suite
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
            <nav class="flex items-center justify-center space-x-2 mt-12">
                <!-- Prev -->
                <a href="news.php?<?= http_build_query(array_merge($baseParams, ['page' => max(1, $page - 1)])) ?>"
                    class="px-3 py-1 rounded border <?= $page === 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                    Précédent
                </a>

                <!-- numéros -->
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <a href="news.php?<?= http_build_query(array_merge($baseParams, ['page' => $p])) ?>" class="px-3 py-1 rounded border 
           <?= $p === $page
               ? 'bg-blue-600 text-white border-blue-600'
               : 'hover:bg-gray-100' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <!-- Next -->
                <a href="news.php?<?= http_build_query(array_merge($baseParams, ['page' => min($pages, $page + 1)])) ?>"
                    class="px-3 py-1 rounded border <?= $page === $pages ? 'opacity-50 pointer-events-none' : '' ?>">
                    Suivant
                </a>
            </nav>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>