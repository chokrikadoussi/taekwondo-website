<?php
session_start();
$pageTitle = 'Actualités';
$pageActuelle = 'news';

require __DIR__ . '/fonction/fonctions.php';
$pdo = connexionBaseDeDonnees();

// 1) Récupération des posts
$posts = $pdo
    ->query("
      SELECT
        id,
        titre,
        SUBSTRING(contenu, 1, 200) AS extrait,
        DATE_FORMAT(created_at, '%d %M %Y') AS date_publication
      FROM posts
      ORDER BY created_at DESC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <h1 class="text-3xl font-semibold mb-8 text-center">Nos actualités</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($posts as $post): ?>
                <article class="bg-white rounded-lg shadow p-6 flex flex-col">
                    <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($post['titre'], ENT_QUOTES) ?></h2>
                    <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($post['date_publication'], ENT_QUOTES) ?></p>
                    <p class="text-gray-700 flex-grow">
                        <?= htmlspecialchars($post['extrait'], ENT_QUOTES) ?>…
                    </p>
                    <a href="news_detail.php?id=<?= $post['id'] ?>"
                        class="mt-4 inline-block self-start bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition">
                        Lire la suite
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>