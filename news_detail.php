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
$post = getPostParId($id);
if (!$post) {
    http_response_code(404);
    include __DIR__ . '/erreur.php';
    exit;
}

// Récupérer ses tags
$tags = getTagsPourPost($id);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <!-- Retour -->
        <div class="mb-6">
            <a href="news.php"
               class="inline-flex items-center text-gray-600 hover:text-blue-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Retour aux actualités
            </a>
        </div>

        <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <?php if (!empty($post['photo'])): ?>
                <div class="relative h-64 sm:h-80 md:h-96 overflow-hidden">
                    <img src="img/<?= htmlspecialchars($post['photo'], ENT_QUOTES) ?>"
                         alt="<?= htmlspecialchars($post['titre'], ENT_QUOTES) ?>"
                         class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <h1 class="absolute bottom-4 left-4 text-white text-2xl sm:text-3xl md:text-4xl font-bold drop-shadow-lg">
                        <?= htmlspecialchars($post['titre'], ENT_QUOTES) ?>
                    </h1>
                </div>
            <?php else: ?>
                <h1 class="text-3xl font-bold text-gray-900 p-6">
                    <?= htmlspecialchars($post['titre'], ENT_QUOTES) ?>
                </h1>
            <?php endif; ?>

            <div class="p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-sm text-gray-500">
                    <div>
                        Écrit par <strong><?= htmlspecialchars($post['auteur_nom'], ENT_QUOTES) ?></strong>
                        le <?= htmlspecialchars($post['date_publication'], ENT_QUOTES) ?>
                    </div>
                    <?php if ($tags): ?>
                        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
                            <?php foreach ($tags as $t): ?>
                                <a href="news.php?tag=<?= urlencode($t) ?>"
                                   class="text-xs font-medium px-2 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200 transition">
                                    <?= htmlspecialchars($t, ENT_QUOTES) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="prose prose-lg max-w-none text-gray-700">
                    <?= nl2br(htmlspecialchars($post['contenu'], ENT_QUOTES)) ?>
                </div>

                <!-- Bouton de partage (exemple) -->
                <div class="mt-8 flex space-x-4">
                    <span class="font-medium text-gray-600">Partagez :</span>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>&text=<?= urlencode($post['titre']) ?>"
                       target="_blank"
                       class="text-blue-500 hover:text-blue-700 transition">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>"
                       target="_blank"
                       class="text-blue-600 hover:text-blue-800 transition">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                </div>
            </div>
        </article>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>
