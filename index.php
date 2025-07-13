<?php
session_start();
require __DIR__ . '/fonction/fonctions.php';

$posts = array_slice(getAllPosts(150), 0, 3);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <br>
    <main class="flex-grow">

        <!-- Hero Section -->
        <section class="flex flex-col md:flex-row items-center bg-white">
            <img src="img/hero-left.jpg" alt="Entraînement Taekwondo" class="w-full md:w-1/2 h-auto object-cover">
            <div class="w-full md:w-1/2 p-6 md:p-12">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Taekwondo Club Saint-Priest</h1>
                <h2 class="text-xl md:text-2xl text-gray-600 mb-6">Un art martial, Une passion, Une communauté </h2>
                <p class="text-gray-700 mb-6">
                    Fusce suscipit cursus sem. Vivamus risus mi, egestas ac, imperdiet varius, faucibus quis, leo.
                    Aenean tincidunt. Donec suscipit. Cras id justo quis nibh scelerisque dignissim. Aliquam sagittis
                    elementum dolor. Aenean consectetuer justo in pede. Curabitur ullamcorper ligula nec orci.
                    Aliquam purus turpis, aliquam id, ornare vitae, porttitor non, wisi.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="about.php"
                        class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded text-center transition">
                        Découvrir le club
                    </a>
                    <a href="#"
                        class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded text-center transition">
                        Réserver un cours d’essai
                    </a>
                </div>
            </div>
        </section>

        <!-- Separator -->
        <div class="my-12 border-t border-gray-200"></div>

        <!-- Questionnaire Section -->
        <section class="bg-gray-50 py-8">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-2xl font-semibold mb-4">Quel cours est fait pour vous ?</h2>
                <p class="text-gray-600 mb-6">
                    Répondez à ce questionnaire pour découvrir lequel de nos cours est fait pour vous !
                </p>
                <form action="index.php" method="post" class="inline-block">
                    <button type="submit" name="submit-qst-cours"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded transition">
                        Trouver mon cours
                    </button>
                </form>
            </div>
        </section>

        <!-- Separator -->
        <div class="my-12 border-t border-gray-200"></div>

        <!-- News Section -->
        <!-- News Section -->
        <section class="py-12 bg-gray-100">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-semibold text-center mb-2">Les dernières actualités du club</h2>
                <p class="text-center text-gray-600 mb-8">
                    In accumsan convallis metus. Aenean est. Donec pharetra porta odio. Duis nunc nisl, imperdiet ac,
                    tincidunt vitae, varius sit amet, felis.
                </p>

                <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-2">
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
                                    Ecrit par <strong><?= htmlspecialchars($post['auteur_nom'], ENT_QUOTES) ?></strong>, le
                                    <i><?= htmlspecialchars($post['created_at'], ENT_QUOTES) ?></i>
                                </p>
                                <p class="text-gray-700 flex-grow mb-4">
                                    <?= htmlspecialchars($post['excerpt'], ENT_QUOTES) ?>…
                                </p>

                                <?php if (!empty($post['tags'])): ?>
                                    <div class="flex flex-wrap justify-end gap-4 mb-4">
                                        <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                            <span
                                                class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
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

                <div class="mt-8 text-center">
                    <a href="news.php" class="inline-block text-gray-700 hover:text-blue-600 transition">
                        Voir toutes les actualités
                    </a>
                </div>
            </div>
        </section>


    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>

</html>