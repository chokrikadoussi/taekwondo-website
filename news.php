<?php
session_start();
$pageTitle = 'Actualités';
$pageActuelle = 'news';

require __DIR__ . '/fonction/fonctions.php';

// 1) Lecture des paramètres de filtre/tri
$filterTag = $_GET['tag'] ?? null;
$sort = $_GET['sort'] ?? 'desc';

// 2) Récupération de la liste des tags pour le filtre
$allTags = getAllTags();

// 3) Récupération des posts filtrés / triés
$posts = getAllPosts(150, $filterTag, $sort);
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

        <!-- Filtre et tri -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <!-- Filtre par tag -->
            <div class="flex flex-wrap gap-2">
                <span class="font-medium">Filtrer par tag :</span>
                <a href="news.php"
                    class="px-2 py-1 rounded <?= $filterTag === null ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
                    Tous
                </a>
                <?php foreach ($allTags as $tag): ?>
                    <a href="news.php?tag=<?= urlencode($tag['name']) ?>&sort=<?= $sort ?>"
                        class="px-2 py-1 rounded <?= $filterTag === $tag['name'] ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
                        <?= htmlspecialchars($tag['name'], ENT_QUOTES) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Tri par date -->
            <div class="flex items-center gap-2">
                <span class="font-medium">Trier :</span>
                <a href="news.php?<?= $filterTag ? "tag=" . urlencode($filterTag) . "&" : '' ?>sort=desc"
                    class="px-2 py-1 rounded <?= $sort === 'desc' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
                    Plus récentes
                </a>
                <a href="news.php?<?= $filterTag ? "tag=" . urlencode($filterTag) . "&" : '' ?>sort=asc"
                    class="px-2 py-1 rounded <?= $sort === 'asc' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
                    Plus anciennes
                </a>
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
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>