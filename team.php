<?php
session_start();
$pageTitle = 'Entraîneurs';
$pageActuelle = 'team';

require __DIR__ . '/fonction/fonctions.php';

// Récupération des entraîneurs
$trainers = getAllTrainers();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <h1 class="text-3xl font-semibold mb-8 text-center">Notre équipe</h1>
        <div></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($trainers as $t): ?>
                <article class="bg-white rounded-lg shadow p-6 flex flex-col">
                    <!-- Photo si tu en as une dans la DB, sinon avatar générique -->
                    <?php if (!empty($t['photo'] ?? '')): ?>
                        <img src="img/<?= htmlspecialchars($t['photo'], ENT_QUOTES) ?>"
                            alt="Photo de <?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?>"
                            class="h-128 w-full object-cover rounded-md mb-4">
                    <?php else: ?>
                        <div class="h-128 w-full bg-gray-100 rounded-md mb-4 flex items-center justify-center text-gray-400">
                            <i class="fas fa-user-circle fa-5x"></i>
                        </div>
                    <?php endif; ?>

                    <h2 class="text-xl font-bold mb-1"><?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?></h2>
                    <p class="text-sm text-indigo-600 mb-3 uppercase tracking-wide"><?= htmlspecialchars($t['role'], ENT_QUOTES) ?></p>

                    <p class="text-gray-700 flex-grow">
                        <?php
                        $bio = strip_tags($t['bio']);
                        echo mb_strlen($bio) > 120
                            ? htmlspecialchars(mb_substr($bio, 0, 120), ENT_QUOTES) . '…'
                            : htmlspecialchars($bio, ENT_QUOTES);
                        ?>
                    </p>

                    <a href="team.php#trainer-<?= $t['id'] ?>"
                        class="mt-4 inline-block self-start bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition">
                        En savoir plus
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>