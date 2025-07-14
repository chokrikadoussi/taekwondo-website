<?php
session_start();
$pageTitle = 'Actualité';
$pageActuelle = 'news';

require __DIR__ . '/fonction/fonctions.php';

// Récupérer l’ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: news.php');
    exit;
}

// Charger le post
$post = getPostById($id);
if (!$post) {
    http_response_code(404);
    include __DIR__ . '/erreur.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 max-w-3xl">
        <h1 class="text-3xl font-semibold mb-2"><?= ucfirst(htmlspecialchars($post['titre'], ENT_QUOTES)) ?></h1>
        <p class="text-gray-500 mb-6">Ecrit par
            <strong><?= htmlspecialchars($post['auteur_nom'], ENT_QUOTES) ?></strong>, le
            <i><?= htmlspecialchars($post['date_publication'], ENT_QUOTES) ?></i>
        </p>
        <div class="prose">
            <?= htmlspecialchars($post['contenu'], ENT_QUOTES) ?>
        </div>
        <a href="news.php" class="mt-8 inline-block text-blue-600 hover:underline">
            ← Retour aux actualités
        </a>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>