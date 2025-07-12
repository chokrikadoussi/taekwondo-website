<?php
session_start();
$pageTitle = 'Actualité';
$pageActuelle = 'news';

require __DIR__ . '/fonction/fonctions.php';
$pdo = connexionBaseDeDonnees();

// Récupérer l’ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: news.php');
    exit;
}

// Charger le post
$stmt = $pdo->prepare("
  SELECT titre, contenu, DATE_FORMAT(created_at, '%d %M %Y') AS date_publication
  FROM posts
  WHERE id = ?
");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    header('Location: news.php');
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
        <h1 class="text-3xl font-semibold mb-2"><?= htmlspecialchars($post['titre'], ENT_QUOTES) ?></h1>
        <p class="text-gray-500 mb-6"><?= htmlspecialchars($post['date_publication'], ENT_QUOTES) ?></p>
        <div class="prose">
            <?= nl2br(htmlspecialchars($post['contenu'], ENT_QUOTES)) ?>
        </div>
        <a href="news.php" class="mt-8 inline-block text-blue-600 hover:underline">
            ← Retour aux actualités
        </a>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>