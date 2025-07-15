<?php
session_start();
$pageTitle = 'Entraîneurs';
$pageActuelle = 'team';

require __DIR__ . '/fonction/fonctions.php';

// Récupération des entraîneurs
$trainers = getListeEntraineurs();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-gray-50">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <h1 class="text-3xl font-semibold mb-8 text-center text-gray-900">Notre équipe</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($trainers as $t): ?>
                <div class="group perspective">
                    <div class="card-inner relative w-full transition-transform duration-700 transform-style-preserve-3d">

                        <!-- Face avant -->
                        <article class="card-front bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col h-full">
                            <div class="relative h-128 bg-gray-100">
                                <?php if (!empty($t['photo'])): ?>
                                    <img src="img/<?= htmlspecialchars($t['photo'], ENT_QUOTES) ?>" alt=""
                                        class="object-cover w-full h-full">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <i class="fas fa-user-circle fa-6x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?>
                                </h2>
                                <p class="text-gray-700 flex-grow mb-4">
                                    <?php
                                    $bio = strip_tags($t['bio']);
                                    echo mb_strlen($bio) > 100
                                        ? htmlspecialchars(mb_substr($bio, 0, 100), ENT_QUOTES) . '…'
                                        : htmlspecialchars($bio, ENT_QUOTES);
                                    ?>
                                </p>
                                <button
                                    class="btn-flip mt-auto bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition cursor-pointer">
                                    En savoir plus
                                </button>
                            </div>
                        </article>

                        <!-- Face arrière -->
                        <article
                            class="card-back absolute inset-0 bg-white rounded-2xl shadow-lg p-6 backface-hidden transform rotate-y-180">
                            <h2 class="text-xl font-bold mb-4"><?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?></h2>
                            <p class="text-gray-800 leading-relaxed mb-6">
                                <?= nl2br(htmlspecialchars($t['bio'], ENT_QUOTES)) ?>
                            </p>
                            <button
                                class="btn-unflip bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition cursor-pointer">
                                Retour
                            </button>
                        </article>

                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>

</html>