<?php
session_start();
$pageTitle = 'Actualités';
$pageActuelle = 'news';

require __DIR__ . '/fonction/fonctions.php';

// 1) Lecture des paramètres de filtre/tri/pagination
$filterTag = $_GET['tag'] ?? null;
$sort = $_GET['sort'] ?? 'desc';
$perPage = 9;                                              // nombre d’articles par page
$page = max(1, (int) ($_GET['page'] ?? 1)); // page courante, >=1

// 2) Récupération de la liste des tags pour le filtre
$allTags = getAllTags();

// 3) Récupération des posts filtrés / triés
$allPosts = getAllPosts(150, $filterTag, $sort);
$total = count($allPosts);
$pages = (int) ceil($total / $perPage);

// 4) Extraire la « fenêtre » pour la page courante
$offset = ($page - 1) * $perPage;
$posts = array_slice($allPosts, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 space-y-6">
        <h1 class="text-3xl font-bold text-center">Nos actualités</h1>

        <!-- Filtre & Tri -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <!-- Filtre par tag -->
            <div class="flex flex-wrap gap-2">
                <span class="font-medium">Filtrer par tag :</span>
                <a href="news.php?sort=<?= $sort ?>"
                    class="px-2 py-1 rounded <?= $filterTag === null ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
                    Tous
                </a>
                <?php foreach ($allTags as $t): ?>
                    <a href="news.php?tag=<?= urlencode($t['name']) ?>&sort=<?= $sort ?>"
                        class="px-2 py-1 rounded <?= $filterTag === $t['name'] ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
                        <?= htmlspecialchars($t['name'], ENT_QUOTES) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Tri par date (dropdown) -->
            <div class="flex items-center gap-2">
                <label for="sort-select" class="font-medium">Trier :</label>
                <select id="sort-select" onchange="location.href=this.value" class="border rounded px-2 py-1">
                    <?php
                    // Générer l’URL courante en préservant le filtre de tag
                    $baseUrl = 'news.php?'
                        . ($filterTag ? 'tag=' . urlencode($filterTag) . '&' : '');
                    ?>
                    <option value="<?= $baseUrl . 'sort=desc' ?>" <?= $sort === 'desc' ? 'selected' : '' ?>>
                        + Récentes
                    </option>
                    <option value="<?= $baseUrl . 'sort=asc' ?>" <?= $sort === 'asc' ? 'selected' : '' ?>>
                        + Anciennes
                    </option>
                </select>
            </div>
        </div>

        <!-- Grille des articles -->
        <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($posts as $post): ?>
                <article
                    class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col hover:shadow-xl transition-shadow">
                    <a href="news_detail.php?id=<?= $post['id'] ?>">
                        <!-- Placeholder image -->
                        <div class="h-40 bg-gray-100 flex items-center justify-center text-gray-400">
                            <i class="fas fa-newspaper fa-2x"></i>
                        </div>

                        <div class="p-6 flex flex-col flex-grow">
                            <h2 class="text-2xl font-semibold mb-2">
                                <?= htmlspecialchars($post['titre'], ENT_QUOTES) ?>
                            </h2>
                            <p class="text-sm text-gray-500 mb-4">
                                Ecrit par <strong><?= htmlspecialchars($post['auteur_nom'], ENT_QUOTES) ?></strong>, le
                                <i><?= htmlspecialchars($post['created_at'], ENT_QUOTES) ?></i>
                            </p>
                            <p class="text-gray-700 flex-grow mb-4">
                                <?= htmlspecialchars($post['excerpt'], ENT_QUOTES) ?>…
                            </p>

                            <?php if (!empty($post['tags'])): ?>
                                <div class="flex flex-wrap justify-end gap-2 mb-4">
                                    <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                        <a href="news.php?tag=<?= urlencode(trim($tag)) ?>&sort=<?= $sort ?>"
                                            class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
                                            <?= htmlspecialchars(trim($tag), ENT_QUOTES) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <a href="news_detail.php?id=<?= $post['id'] ?>"
                                class="mt-auto inline-block self-start bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                                Lire la suite
                            </a>
                        </div>
                    </a>
                </article>

            <?php endforeach; ?>
        </div>
        <?php if ($pages > 1): ?>
            <nav class="flex items-center justify-center space-x-2 mt-12">
                <!-- Previous -->
                <a href="?page=<?= max(1, $page - 1) ?>"
                    class="<?= $page === 1 ? 'opacity-50 cursor-not-allowed' : '' ?> px-3 py-1 rounded border"
                    aria-disabled="<?= $page === 1 ? 'true' : 'false' ?>">
                    Précédent
                </a>

                <!-- Numéros de pages -->
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <a href="?page=<?= $p ?>"
                        class="px-3 py-1 rounded border <?= $p === $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <!-- Next -->
                <a href="?page=<?= min($pages, $page + 1) ?>"
                    class="<?= $page === $pages ? 'opacity-50 cursor-not-allowed' : '' ?> px-3 py-1 rounded border"
                    aria-disabled="<?= $page === $pages ? 'true' : 'false' ?>">
                    Suivant
                </a>
            </nav>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>