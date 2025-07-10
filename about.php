<?php
// about.php
$pageTitle = 'Le Club & Actualités';
$pageActuelle = 'about';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 space-y-16">

        <!-- SECTION À PROPOS -->
        <section id="a-propos" class="prose lg:prose-xl mx-auto">
            <h1>À propos du Taekwondo Club Saint-Priest</h1>
            <p><!-- Texte descriptif du club --></p>
            <ul class="list-disc ml-6">
                <li><!-- Point fort 1 --></li>
                <li><!-- Point fort 2 --></li>
                <li><!-- Point fort 3 --></li>
            </ul>
        </section>

        <!-- SECTION BLOG / ACTUALITÉS -->
        <section id="blog">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-semibold">Actualités du club</h2>
                <!-- Bouton “Nouvel article” (visible aux admins) -->
                <a href="profile.php?page=create_post"
                    class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition">
                    <i class="fas fa-plus mr-2"></i>Nouvel article
                </a>
            </div>

            <!-- GRID DES POSTS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!--
          Boucle posts :
          foreach ($posts as $post) {
            <article>…</article>
          }
        -->
                <article class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                    <div class="h-48 bg-gray-200"></div> <!-- Image placeholder -->
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-xl font-bold mb-2"><!-- Titre --></h3>
                        <p class="text-sm text-gray-500 mb-4"><!-- Date & auteur --></p>
                        <p class="flex-grow text-gray-700"><!-- Extrait --></p>
                        <a href="post.php?id=…" class="mt-4 text-blue-600 hover:underline font-medium">Lire la suite
                            →</a>
                    </div>
                </article>
                <!-- fin boucle -->
            </div>

            <!-- PAGINATION -->
            <nav class="mt-8 flex justify-center space-x-2">
                <!--
          if $totalPages > 1 :
            <a href="?page=about&p={{prev}}#blog">« Précédent</a>
            boucle numéros de pages
            <a href="?page=about&p={{next}}#blog">Suivant »</a>
        -->
            </nav>
        </section>

    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>

</html>