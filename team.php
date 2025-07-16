<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Page de présentation des entraineurs
 * 
 * TODO: 
 * 
 */
session_start();
$pageTitle = 'Entraîneurs';
$pageActuelle = 'team';
require __DIR__ . '/fonction/fonctions.php';

$trainers = getListeEntraineurs();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-slate-50">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <header class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-slate-900">Notre Équipe Pédagogique</h1>
            <p class="mt-2 text-lg text-slate-600 max-w-2xl mx-auto">Des passionnés certifiés et expérimentés, dédiés à
                votre progression et à la transmission des valeurs du Taekwondo.</p>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($trainers as $t): ?>
                <div class="group perspective">
                    <div
                        class="card-inner relative w-full h-full transition-transform duration-700 transform-style-preserve-3d">

                        <article class="card-front bg-white rounded-xl shadow-lg overflow-hidden flex flex-col h-full">
                            <div class="aspect-square bg-slate-100">
                                <?php if (!empty($t['photo'])): ?>
                                    <img src="img/<?= htmlspecialchars($t['photo'], ENT_QUOTES) ?>"
                                        alt="Photo de <?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?>"
                                        class="object-cover w-full h-full">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i class="fas fa-user-circle fa-6x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <h2 class="text-2xl font-bold text-slate-900 mb-1">
                                    <?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?>
                                </h2>
                                <p class="text-slate-600 flex-grow mb-4 line-clamp-4">
                                    <?php
                                    $bio = strip_tags($t['bio']);
                                    echo mb_strlen($bio) > 120
                                        ? htmlspecialchars(mb_substr($bio, 0, 120), ENT_QUOTES) . '…'
                                        : htmlspecialchars($bio, ENT_QUOTES);
                                    ?>
                                </p>
                                <button
                                    class="btn-flip mt-auto w-full bg-slate-800 text-white py-2 px-4 rounded-lg hover:bg-slate-900 transition cursor-pointer font-semibold">
                                    En savoir plus
                                </button>
                            </div>
                        </article>

                        <article
                            class="card-back absolute inset-0 bg-white rounded-xl shadow-lg flex flex-col backface-hidden transform rotate-y-180 overflow-y-auto">
                            <div class="p-6 flex-grow flex flex-col">
                                <h2 class="text-2xl font-bold text-slate-900 mb-1">
                                    <?= htmlspecialchars($t['nom_complet'], ENT_QUOTES) ?>
                                </h2>
                                <div class="text-slate-700 leading-relaxed space-y-4 flex-grow">
                                    <?= nl2br(htmlspecialchars($t['bio'], ENT_QUOTES)) ?>
                                </div>
                                <button
                                    class="btn-unflip mt-6 w-full bg-slate-200 text-slate-800 py-2 px-4 rounded-lg hover:bg-slate-300 transition cursor-pointer font-semibold">
                                    Retour
                                </button>
                            </div>
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