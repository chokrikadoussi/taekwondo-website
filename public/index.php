<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Page d'Accueil du site web. Point d'entrée de l'application.
 * -> Découpé en plusieurs sections : Hero, Dernières news, Témoignages
 *      
 * TODO:
 * - Ajouter le quizz d'orientation après la section hero
 * 
 */
session_start();
$pageTitle = 'Accueil';
$pageActuelle = 'home';
require __DIR__ . '/fonction/fonctions.php';

// Récupérer les trois derniers posts pour la section "Dernières actualités"
$posts = array_slice(getListePosts(150), 0, 3);

?>

<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main class="flex-grow">

        <!-- Hero Section -->
        <div class="bg-black text-white overflow-hidden relative">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent z-20"></div>

            <section class="relative container mx-auto px-4 flex items-center lg:pt-80 justify-center h-hero">
                <div class="z-30 w-full max-w-3xl p-8 bg-black/40 backdrop-blur-lg rounded-2xl border border-white/20">

                    <h1 class="relative overflow-hidden mb-4 h-24 md:h-32 lg:h-50">
                        <div class="slide-phrase font-display text-6xl md:text-8xl lg:text-9xl tracking-wider delay-0">
                            <span class="bg-gradient-to-r from-blue-400 to-sky-300 bg-clip-text text-transparent">Une
                                passion</span>
                        </div>
                        <div class="slide-phrase font-display text-6xl md:text-8xl lg:text-9xl tracking-wider delay-3">
                            <span class="bg-gradient-to-r from-blue-400 to-sky-300 bg-clip-text text-transparent">Un
                                combat</span>
                        </div>
                        <div class="slide-phrase font-display text-6xl md:text-8xl lg:text-9xl tracking-wider delay-6">
                            <span class="bg-gradient-to-r from-blue-400 to-sky-300 bg-clip-text text-transparent">Un
                                seul club</span>
                        </div>
                    </h1>

                    <p class="text-lg text-slate-300 mb-8">
                        Rejoignez le club de Taekwondo de Saint-Priest et dépassez vos limites au sein d'une communauté
                        passionnée.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="about.php"
                            class="w-full sm:flex-1 text-center px-7 py-3 font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Découvrir le club
                        </a>
                        <a href="contact.php"
                            class="w-full sm:flex-1 text-center px-7 py-3 font-semibold bg-white text-slate-900 rounded-lg hover:bg-slate-200 transition">
                            Contactez-nous
                        </a>
                    </div>
                </div>

                <img src="img/hero-right-removebg.png" alt="Deux combattants de taekwondo"
                    class="absolute bottom-0 right-0 h-full object-cover z-10" />
        </div>

        <!-- News Section -->
        <section class="py-20 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-extrabold text-slate-900">Les dernières actualités</h2>
                    <p class="mt-2 text-lg text-slate-600">Restez informé de la vie du club.</p>
                </div>
                <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($posts as $post) { ?>
                        <article
                            class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col transition duration-300 hover:shadow-xl hover:-translate-y-1">
                            <a href="news_detail.php?id=<?= $post['id'] ?>"
                                class="h-48 bg-slate-200 flex items-center justify-center text-slate-400">
                                <?php if (!empty($post['photo'])) { ?>
                                    <img src="img/<?= htmlspecialchars($post['photo'], ENT_QUOTES) ?>"
                                        alt="Image de <?= htmlspecialchars($post['titre'], ENT_QUOTES) ?>"
                                        class="object-cover w-full h-full">
                                <?php } else { ?>
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-image fa-6x"></i>
                                    </div>
                                <?php } ?>
                            </a>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex-grow">
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <?php foreach (explode(',', $post['tags'] ?? '') as $tag) {
                                            if (!trim($tag)) {
                                                continue;
                                            } ?>
                                            <a href="news.php?tag=<?= urlencode(trim($tag)) ?>"
                                                class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-semibold hover:bg-blue-200">
                                                <?= htmlspecialchars(trim($tag), ENT_QUOTES) ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2 line-clamp-2">
                                        <a href="news_detail.php?id=<?= $post['id'] ?>"
                                            class="hover:text-blue-600 transition-colors">
                                            <?= htmlspecialchars(ucfirst($post['titre']), ENT_QUOTES) ?>
                                        </a>
                                    </h3>
                                    <p class="text-sm text-slate-500 mb-4">
                                        Par <strong><?= htmlspecialchars($post['auteur_nom'], ENT_QUOTES) ?></strong> le
                                        <time><?= date('d/m/Y', strtotime($post['created_at'])) ?></time>
                                    </p>
                                    <p class="text-slate-700 line-clamp-3">
                                        <?= htmlspecialchars($post['excerpt'], ENT_QUOTES) ?>…
                                    </p>
                                </div>
                                <div class="mt-6">
                                    <a href="news_detail.php?id=<?= $post['id'] ?>"
                                        class="font-semibold text-blue-600 hover:text-blue-700">
                                        Lire la suite &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php } ?>
                </div>
                <div class="mt-12 text-center">
                    <a href="news.php"
                        class="inline-block bg-slate-800 text-white font-semibold py-3 px-6 rounded-lg shadow-sm hover:bg-slate-900 transition-colors">
                        Voir toutes les actualités
                    </a>
                </div>
            </div>
        </section>

        <!-- Témoignages Section -->
        <section id="testimonials" class="bg-white py-20">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-extrabold text-slate-900">Ils nous recommandent</h2>
                    <p class="mt-2 text-lg text-slate-600">La parole est à nos membres.</p>
                </div>

                <div class="relative group">
                    <div id="carousel-wrapper" class="overflow-hidden">
                        <div id="carousel-track" class="flex transition-transform duration-500 ease-in-out">
                            <?php foreach (getTemoignages() as $t) { ?>
                                <figure class="flex-shrink-0 w-full md:w-1/2 lg:w-1/3 px-4">
                                    <div class="bg-slate-50 p-8 rounded-lg h-full">
                                        <i class="fas fa-quote-left text-blue-200 text-5xl"></i>
                                        <blockquote class="mt-4">
                                            <p class="text-lg italic font-medium text-slate-800">
                                                “<?= htmlspecialchars($t['quote'], ENT_QUOTES) ?>”
                                            </p>
                                        </blockquote>
                                        <figcaption class="flex items-center mt-6 space-x-3">
                                            <img class="h-12 w-12 rounded-full object-cover"
                                                src="https://i.pravatar.cc/100?u=<?= urlencode($t['name']) ?>"
                                                alt="Avatar de <?= htmlspecialchars($t['name'], ENT_QUOTES) ?>">
                                            <div>
                                                <cite
                                                    class="block font-semibold text-slate-900"><?= htmlspecialchars($t['name'], ENT_QUOTES) ?></cite>
                                                <cite
                                                    class="block text-sm text-slate-500"><?= htmlspecialchars($t['role'], ENT_QUOTES) ?></cite>
                                            </div>
                                        </figcaption>
                                    </div>
                                </figure>
                            <?php } ?>
                        </div>
                    </div>

                    <button id="carousel-prev"
                        class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white transition-opacity opacity-0 group-hover:opacity-100">
                        <i class="fas fa-chevron-left text-blue-600 h-6 w-6"></i>
                    </button>
                    <button id="carousel-next"
                        class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white transition-opacity opacity-0 group-hover:opacity-100">
                        <i class="fas fa-chevron-right text-blue-600 h-6 w-6"></i>
                    </button>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>