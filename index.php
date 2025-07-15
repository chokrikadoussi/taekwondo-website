<?php
session_start();
$pageTitle = 'Accueil';
$pageActuelle = 'home';
require __DIR__ . '/fonction/fonctions.php';

$posts = array_slice(getListePosts(150), 0, 3);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <!-- Styles et animations spécifiques au Hero -->
    <style>
        @layer utilities {
            .h-hero {
                height: calc(100vh - 70px);
            }
        }

        @keyframes slide-cycle {
            0% {
                transform: translateY(100%);
                opacity: 0;
            }

            10% {
                transform: translateY(0);
                opacity: 1;
            }

            30% {
                transform: translateY(0);
                opacity: 1;
            }

            40% {
                transform: translateY(-100%);
                opacity: 0;
            }

            100% {
                transform: translateY(-100%);
                opacity: 0;
            }
        }

        .slide-phrase {
            position: absolute;
            inset: 0;
            /* couvre tout le parent */
            display: flex;
            align-items: center;
            /* centre vertical */
            /* justify-content left by default, modifiez si besoin */
            animation: slide-cycle 9s ease-in-out infinite both;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main class="flex-grow">

        <!-- HERO -->
        <div class="bg-black text-white font-sans overflow-x-hidde">
            <section class="relative overflow-hidden flex flex-col items-center justify-center
                    md:flex-row md:items-center lg:items-center lg:justify-start
                    px-4 md:px-8 h-hero">
                <!-- Bloc texte -->
                <div class="relative z-10 max-w-[800px] p-4 bg-black/30 backdrop-blur-lg rounded-xl lg:pl-16">
                    <h1 class="relative overflow-hidden mb-6 h-[3rem] lg:h-[5rem]">
                        <div class="slide-phrase text-[2.75rem] lg:text-[5rem] font-bold leading-tight"
                            style="animation-delay: 0s">
                            Une passion
                        </div>
                        <div class="slide-phrase text-[2.75rem] lg:text-[5rem] font-bold leading-tight"
                            style="animation-delay: 3s">
                            Un combat
                        </div>
                        <div class="slide-phrase text-[2.75rem] lg:text-[5rem] font-bold leading-tight"
                            style="animation-delay: 6s">
                            Un seul club
                        </div>
                    </h1>

                    <p class="text-[1.1rem] mb-8">
                        Rejoignez le club numéro 1 des universités de Paris dès à présent
                    </p>

                    <div class="flex gap-4">
                        <a href="about.php" class="px-7 py-3 text-[0.95rem] font-medium border border-white rounded-md
                  hover:bg-white/10 transition">
                            Découvrir le club
                        </a>
                        <a href="contact.php" class="px-7 py-3 text-[0.95rem] font-medium bg-white text-black rounded-md
                  hover:bg-gray-100 transition">
                            Contactez-nous
                        </a>
                    </div>
                </div>

                <!-- Illustration -->
                <img src="img/hero-right-removebg.png" alt="Deux combattants de taekwondo en plein match"
                    class="absolute bottom-0 right-0 h-full object-cover" />
            </section>
        </div>

        <!-- News Section -->
        <section class="py-12 bg-gray-100">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-semibold text-center mb-2">Les dernières actualités du club</h2>
                <p class="text-center text-gray-600 mb-8">
                    In accumsan convallis metus. Aenean est. Donec pharetra porta odio. Duis nunc nisl, imperdiet ac,
                    tincidunt vitae, varius sit amet, felis.
                </p>

                <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($posts as $post): ?>
                        <article
                            class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col hover:shadow-xl transition-shadow">
                            <!-- Placeholder image -->
                            <div class="h-40 bg-gray-100 flex items-center justify-center text-gray-400 hidden md:inline lg:inline ">
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

        <!-- Testimonials Carousel -->
        <section id="testimonials" class="bg-white py-12">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-semibold text-center mb-8">Ils nous recommandent</h2>
                <div class="relative overflow-hidden">
                    <!-- Flèche Gauche -->
                    <button id="carousel-prev"
                        class="absolute left-0 top-1/2 transform -translate-y-1/2 text-black text-xl p-2 rounded-full z-20">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <!-- Pistes (wrapper) -->
                    <div id="carousel-wrapper" class="flex transition-transform duration-500 ease-out">
                        <?php foreach (getTemoignages() as $t): ?>
                            <figure class="flex-shrink-0 w-full md:w-1/2 lg:w-1/3 px-4">
                                <blockquote class="text-center">
                                    <p class="text-lg italic font-medium text-gray-900">
                                        “<?= htmlspecialchars($t['quote'], ENT_QUOTES) ?>”</p>
                                </blockquote>
                                <figcaption class="flex items-center justify-center mt-6 space-x-3">
                                    <div class="text-left">
                                        <cite
                                            class="block font-medium text-gray-900"><?= htmlspecialchars($t['name'], ENT_QUOTES) ?></cite>
                                        <cite
                                            class="block text-sm text-gray-500"><?= htmlspecialchars($t['role'], ENT_QUOTES) ?></cite>
                                    </div>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>

                    <!-- Flèche Droite -->
                    <button id="carousel-next"
                        class="absolute right-0 top-1/2 transform -translate-y-1/2 text-black text-xl p-2 rounded-full z-20">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>


    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>