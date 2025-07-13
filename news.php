<?php
session_start();
$pageTitle = 'Actualités';
$pageActuelle = 'news';

require __DIR__ . '/fonction/fonctions.php';

// 1) Récupération des posts
$posts = getAllPosts(150);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8 text-center">Nos actualités</h1>

        <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($posts as $post): ?>
                <article
                    class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col hover:shadow-xl transition-shadow">
                    <!-- Placeholder image -->
                    <div class="h-40 bg-gray-100 flex items-center justify-center text-gray-400">
                        <i class="fas fa-newspaper fa-2x"></i>
                    </div>

                    <div class="p-6 flex flex-col flex-grow">
                        <h2 class="text-2xl font-semibold mb-2">
                            <?= htmlspecialchars($post['titre'], ENT_QUOTES) ?>
                        </h2>
                        <p class="text-sm text-gray-500 mb-4">
                            Ecrit par <strong><?= htmlspecialchars($post['auteur_nom'], ENT_QUOTES) ?></strong>, le <i><?= htmlspecialchars($post['created_at'], ENT_QUOTES) ?></i>
                        </p>
                        <p class="text-gray-700 flex-grow mb-4">
                            <?= htmlspecialchars($post['excerpt'], ENT_QUOTES) ?>…
                        </p>

                        <?php if (!empty($post['tags'])): ?>
                            <div class="flex flex-wrap justify-end gap-4 mb-4">
                                <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
                                        <?= htmlspecialchars(trim($tag), ENT_QUOTES) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <a href="news_detail.php?id=<?= $post['id'] ?>"
                            class="mt-auto inline-block self-start bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                            Lire la suite
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>