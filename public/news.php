<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 *
 * Présentation du fichier : Page d'actualités, affiche les résultats par tranche de 9 articles
 *
 * TODO: 
 * 
 */
session_start();
$pageTitle = 'Actualités';
$pageActuelle = 'news';
require __DIR__ . '/fonction/fonctions.php';

// Lecture des paramètres de filtre, tri et pagination
$filterTag = $_GET['tag'] ?? '';
$sort = $_GET['sort'] ?? 'desc';
$perPage = 9; // Nombre d'articles par page
$page = max(1, (int) ($_GET['page'] ?? 1)); // Page actuelle, minimum 1

try {
    // Récupération de tous les tags
    $allTags = getListeTags();
    // Récupération de tous les articles selon les filtres
    $allPosts = getListePosts(150, $filterTag ?: null, $sort);

    // Déterminer l'article mis en avant (le plus récent, puis le retirer du tableau principal)
    $postMisEnAvant = !empty($allPosts) ? array_shift($allPosts) : null;

    // Pagination pour les articles restants
    $total = count($allPosts); // Nombre total d'articles après avoir retiré le mis en avant
    $pages = (int) ceil($total / $perPage); // Nombre total de pages
    $offset = ($page - 1) * $perPage; // Offset pour la tranche d'articles à afficher

    // Récupération de la tranche d'articles à afficher (page actuelle)
    $posts = array_slice($allPosts, $offset, $perPage);

    // Préparation des paramètres de base pour les URLs de pagination/filtres
    $baseParams = array();
    if ($filterTag) {
        $baseParams['tag'] = $filterTag;
    }
    if ($sort) {
        $baseParams['sort'] = $sort;
    }

} catch (Exception $e) {
    // En cas d'erreur lors de la récupération des données, rediriger vers une page d'erreur
    logErreur("Page news.php ", $e->getMessage(), array('filterTag' => $filterTag, 'sort' => $sort, 'page' => $page, ));
    http_response_code(500); // Erreur interne du serveur
    include __DIR__ . '/erreur.php'; // Inclut la page d'erreur générique
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 space-y-12">
        <header class="text-center">
            <h1 class="text-4xl font-extrabold text-slate-900">Nos Actualités</h1>
            <p class="mt-2 text-lg text-slate-600">Suivez les derniers événements, résultats et annonces du club.</p>
        </header>

        <?php if ($postMisEnAvant) { ?>
            <section aria-labelledby="featured-post-title">
                <h2 class="sr-only" id="featured-post-title">À la Une</h2>
                <div
                    class="group grid grid-cols-1 lg:grid-cols-2 bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
                    <div class="relative overflow-hidden">
                        <a href="news_detail.php?id=<?= $postMisEnAvant['id'] ?>">
                            <?php if (!empty($postMisEnAvant['photo'])) { ?>
                                <img src="img/<?= htmlspecialchars($postMisEnAvant['photo'], ENT_QUOTES) ?>"
                                    alt="Image de <?= htmlspecialchars($postMisEnAvant['titre'], ENT_QUOTES) ?>"
                                    class="object-cover w-full h-100">
                            <?php } else { ?>
                                <div class="w-full h-full bg-slate-200 flex items-center justify-center">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php } ?>
                        </a>
                    </div>
                    <div class="p-8 flex flex-col justify-center">
                        <span class="text-sm font-bold uppercase tracking-wider text-blue-600">À la une</span>
                        <h3 class="mt-2 text-3xl font-bold text-slate-900 line-clamp-2">
                            <a href="news_detail.php?id=<?= $postMisEnAvant['id'] ?>"
                                class="hover:text-blue-700 transition">
                                <?= htmlspecialchars($postMisEnAvant['titre'], ENT_QUOTES) ?>
                            </a>
                        </h3>
                        <p class="mt-4 text-slate-600 line-clamp-3">
                            <?= htmlspecialchars($postMisEnAvant['excerpt'], ENT_QUOTES) ?>…
                        </p>
                        <div class="mt-6">
                            <a href="news_detail.php?id=<?= $postMisEnAvant['id'] ?>"
                                class="inline-block bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg shadow-sm hover:bg-blue-700 transition-colors">
                                Lire l'article
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>

        <div class="bg-white p-4 rounded-xl shadow-sm">
            <form id="filters-form" method="get" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <input type="hidden" name="page" value="1">

                <div class="flex items-center gap-3">
                    <i class="fas fa-tags text-slate-400" title="Catégorie"></i>
                    <div class="relative">
                        <select name="tag" id="tag-select"
                            class="appearance-none w-full sm:w-auto bg-slate-50 border border-slate-200 text-slate-800 font-medium rounded-lg px-4 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($allTags as $t) { ?>
                                <option value="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>" <?= $filterTag === $t['name'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($t['name']), ENT_QUOTES) ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-sort-amount-down text-slate-400" title="Trier par"></i>
                        <div class="relative">
                            <select name="sort" id="sort-select"
                                class="appearance-none w-full sm:w-auto bg-slate-50 border border-slate-200 text-slate-800 font-medium rounded-lg px-4 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="desc" <?= $sort === 'desc' ? 'selected' : '' ?>>Plus récentes</option>
                                <option value="asc" <?= $sort === 'asc' ? 'selected' : '' ?>>Plus anciennes</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <?php // Ce bouton n'apparaît que si un filtre est actif
                    if (!empty($filterTag) || $sort !== 'desc') { ?>
                        <a href="news.php"
                            class="flex items-center justify-center text-slate-500 hover:text-blue-600 transition"
                            title="Réinitialiser les filtres">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    <?php } ?>
                </div>
            </form>
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
                            <h2 class="text-xl font-bold text-slate-900 mb-2 line-clamp-2">
                                <a href="news_detail.php?id=<?= $post['id'] ?>"
                                    class="hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars(ucfirst($post['titre']), ENT_QUOTES) ?>
                                </a>
                            </h2>
                            <p class="text-sm text-slate-500 mb-4">
                                Par <strong><?= htmlspecialchars($post['auteur_nom'], ENT_QUOTES) ?></strong>
                                le <time><?= date('d/m/Y', strtotime($post['created_at'])) ?></time>
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

        <?php if ($pages > 1) { ?>
            <nav class="flex items-center justify-center space-x-2 mt-12" aria-label="Pagination">
                <a href="news.php?<?= http_build_query(array_merge($baseParams, ['page' => max(1, $page - 1)])) ?>"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 <?= $page <= 1 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <span class="sr-only">Précédent</span>
                    <i class="fas fa-chevron-left h-5 w-5"></i>
                </a>
                <?php for ($p = 1; $p <= $pages; $p++) { ?>
                    <a href="news.php?<?= http_build_query(array_merge($baseParams, ['page' => $p])) ?>"
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $p === $page ? 'z-10 bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-300 text-slate-500 hover:bg-slate-50' ?>">
                        <?= $p ?>
                    </a>
                <?php } ?>
                <a href="news.php?<?= http_build_query(array_merge($baseParams, ['page' => min($pages, $page + 1)])) ?>"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 <?= $page >= $pages ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <span class="sr-only">Suivant</span>
                    <i class="fas fa-chevron-right h-5 w-5"></i>
                </a>
            </nav>
        <?php } ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>

    </script>
</body>

</html>