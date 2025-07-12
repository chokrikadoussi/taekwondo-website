<?php
session_start();
$pageTitle = 'À propos';
$pageActuelle = 'about';

require __DIR__ . '/fonction/fonctions.php';
$pdo = connexionBaseDeDonnees();

// Récupération des cours pour “Nos cours”
$sql = "
SELECT
    id,
    nom,
    niveau,
    prix,
    SUBSTRING(description, 1, 150) AS extrait_desc,
    description
FROM classes
ORDER BY niveau
";
$classes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- On passe ici en flex md: nav + contenu -->
    <div class="flex-grow container mx-auto px-4 py-12 md:flex gap-8">
        <!-- Nav latérale (desktop) -->
        <nav id="page-nav"
            class="hidden md:block sticky top-24 self-start flex-shrink-0 w-1/4 bg-white shadow-lg rounded-lg p-4">
            <ul class="space-y-2">
                <li>
                    <a href="#quisommesnous" class="flex items-center px-3 py-2 rounded-lg transition-colors
                              text-gray-700 hover:bg-blue-50 hover:text-blue-600
                              focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <i class="fas fa-info-circle w-5"></i>
                        <span class="ml-2">Qui sommes-nous</span>
                    </a>
                </li>
                <li>
                    <a href="#nosexploits" class="flex items-center px-3 py-2 rounded-lg transition-colors
                              text-gray-700 hover:bg-blue-50 hover:text-blue-600
                              focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <i class="fas fa-trophy w-5"></i>
                        <span class="ml-2">Nos exploits</span>
                    </a>
                </li>
                <li>
                    <a href="#nosvaleurs" class="flex items-center px-3 py-2 rounded-lg transition-colors
                              text-gray-700 hover:bg-blue-50 hover:text-blue-600
                              focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <i class="fas fa-heart w-5"></i>
                        <span class="ml-2">Nos valeurs</span>
                    </a>
                </li>
                <li>
                    <a href="#noscours" class="flex items-center px-3 py-2 rounded-lg transition-colors
                              text-gray-700 hover:bg-blue-50 hover:text-blue-600
                              focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <i class="fas fa-dumbbell w-5"></i>
                        <span class="ml-2">Nos cours</span>
                    </a>
                </li>
                <li>
                    <a href="#planning" class="flex items-center px-3 py-2 rounded-lg transition-colors
                              text-gray-700 hover:bg-blue-50 hover:text-blue-600
                              focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <i class="fas fa-calendar-alt w-5"></i>
                        <span class="ml-2">Planning</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Contenu (inclut menu mobile + sections) -->
        <div class="flex-grow">
            <!-- Menu mobile -->
            <nav id="page-nav-mobile" class="md:hidden mb-8 bg-white shadow-lg rounded-lg p-2 overflow-x-auto">
                <ul class="flex justify-center space-x-2">
                    <li>
                        <a href="#quisommesnous" class="flex items-center px-3 py-2 rounded-lg transition-colors
                                  text-gray-700 hover:bg-blue-50 hover:text-blue-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <i class="fas fa-info-circle"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#nosexploits" class="flex items-center px-3 py-2 rounded-lg transition-colors
                                  text-gray-700 hover:bg-blue-50 hover:text-blue-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <i class="fas fa-trophy"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#nosvaleurs" class="flex items-center px-3 py-2 rounded-lg transition-colors
                                  text-gray-700 hover:bg-blue-50 hover:text-blue-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <i class="fas fa-heart"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#noscours" class="flex items-center px-3 py-2 rounded-lg transition-colors
                                  text-gray-700 hover:bg-blue-50 hover:text-blue-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <i class="fas fa-dumbbell"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#planning" class="flex items-center px-3 py-2 rounded-lg transition-colors
                                  text-gray-700 hover:bg-blue-50 hover:text-blue-600
                                  focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <i class="fas fa-calendar-alt"></i>
                        </a>
                    </li>
                </ul>
            </nav>

            <main class="space-y-16">
                <!-- Qui sommes-nous -->
                <section id="quisommesnous">
                    <h2 class="text-2xl font-semibold mb-4">Qui sommes-nous&nbsp;?</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Fondé en 1995, le Taekwondo Club Saint-Priest est un pilier de la discipline dans la région
                        lyonnaise…
                    </p>
                </section>

                <!-- Nos exploits -->
                <section id="nosexploits">
                    <h2 class="text-2xl font-semibold mb-4">Nos exploits</h2>
                    <ul class="list-disc pl-5 text-gray-700 space-y-2">
                        <li>Champions régionaux 2023 et 2024</li>
                        <li>Plus de 50 médailles en compétitions nationales</li>
                        <li>Stage international avec Maître Kim en 2022</li>
                    </ul>
                </section>

                <!-- Nos valeurs -->
                <section id="nosvaleurs">
                    <h2 class="text-2xl font-semibold mb-4">Nos valeurs</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-xl font-bold mb-2">Respect</h3>
                            <p class="text-gray-700">Nous cultivons l’éthique et la bienveillance envers tous.</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-xl font-bold mb-2">Excellence</h3>
                            <p class="text-gray-700">La quête de la maîtrise technique et mentale est au cœur de notre
                                pratique.</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-xl font-bold mb-2">Communauté</h3>
                            <p class="text-gray-700">Nous favorisons l’entraide et l’esprit d’équipe, sur et en dehors
                                du tatami.</p>
                        </div>
                    </div>
                </section>

                <!-- Nos cours -->
                <section id="noscours">
                    <h2 class="text-2xl font-semibold mb-4">Nos cours</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <?php foreach ($classes as $c): ?>
                            <article class="bg-white rounded-lg shadow p-6 flex flex-col">
                                <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($c['nom'], ENT_QUOTES) ?></h3>
                                <p class="text-sm text-indigo-600 mb-3 uppercase tracking-wide">
                                    <?= htmlspecialchars($c['niveau'], ENT_QUOTES) ?>
                                </p>
                                <p class="text-gray-700 flex-grow">
                                    <?= htmlspecialchars($c['extrait_desc'], ENT_QUOTES) ?>…
                                </p>
                                <a href="about.php#course-<?= $c['id'] ?>"
                                    class="mt-4 inline-block self-start bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition">
                                    En savoir plus
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Planning -->
                <section id="planning">
                    <h2 class="text-2xl font-semibold mb-4">Planning des cours</h2>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full bg-white shadow rounded-lg divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jour</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Horaire</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cours</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-4 py-3">Lundi</td>
                                    <td class="px-4 py-3">18h–19h</td>
                                    <td class="px-4 py-3">Adultes</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3">Mardi</td>
                                    <td class="px-4 py-3">17h–18h</td>
                                    <td class="px-4 py-3">Enfants</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3">Mercredi</td>
                                    <td class="px-4 py-3">19h–20h</td>
                                    <td class="px-4 py-3">Compétition</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Scroll-spy minimal -->
    <script>
        const sections = document.querySelectorAll('main section[id]');
        const navLinks = document.querySelectorAll('#page-nav a, #page-nav-mobile a');
        function onScroll() {
            let current = '';
            sections.forEach(sec => {
                if (sec.getBoundingClientRect().top <= 80) current = sec.id;
            });
            navLinks.forEach(a => {
                const isActive = a.getAttribute('href') === `#${current}`;
                a.classList.toggle('text-blue-600 border-b-2 border-blue-600', isActive);
            });
        }
        document.addEventListener('scroll', onScroll);
        onScroll();
    </script>
</body>

</html>