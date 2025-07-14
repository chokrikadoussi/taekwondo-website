<?php
session_start();
$pageTitle = 'À propos';
$pageActuelle = 'about';

require __DIR__ . '/fonction/fonctions.php';
$classes = getAllClasses();

$nav = [
    'quisommesnous' => ['icon' => 'info-circle', 'label' => 'Qui sommes-nous?'],
    'nosexploits' => ['icon' => 'trophy', 'label' => 'Nos exploits'],
    'nosvaleurs' => ['icon' => 'heart', 'label' => 'Nos valeurs'],
    'noscours' => ['icon' => 'dumbbell', 'label' => 'Nos cours'],
    'planning' => ['icon' => 'calendar-alt', 'label' => 'Planning']
];
$exploits = [
    'Champions régionaux 2023 & 2024',
    'Plus de 50 médailles nationales',
    'Stage international avec Maître Kim (2022)'
];
$valeurs = [
    'Respect' => 'Éthique et bienveillance envers tous.',
    'Excellence' => 'Quête de maîtrise technique et mentale.',
    'Communauté' => 'Esprit d’entraide sur et hors du tatami.'
];
$planning = [
    ['Lundi', '18h–19h', 'Adultes', 'débutant'],
    ['Mardi', '17h–18h', 'Enfants', 'intermédiaire'],
    ['Mercredi', '19h–20h', 'Compétition', 'avancé']
];

// 1) Récupérer le planning tel que renvoyé par votre nouvelle requête
$rawSchedule = getCourseSchedule();

// 2) Mapper les indices DAYOFWEEK → noms
$dayNames = [
    2 => 'Lundi',
    3 => 'Mardi',
    4 => 'Mercredi',
    5 => 'Jeudi',
    6 => 'Vendredi',
    7 => 'Samedi',
];
// On ne conservera que les clés 2 à 7
$days = array_keys($dayNames);

$start = 12;
$end = 22;

// 3) Organiser par indice numérique ensuite par heure
$schedule = [];
foreach ($rawSchedule as $r) {
    $idx = (int) $r['jour'];                // 2..7
    $h = (int) $r['heure_debut'];
    $schedule[$idx][$h][] = $r;
}

$skip = [];

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 md:flex md:gap-8">
        <!-- Navigation latérale (desktop) -->
        <aside class="hidden md:block fixed top-28 left-10 w-64 bg-white rounded-lg shadow p-4">
            <ul class="space-y-3">
                <?php foreach ($nav as $id => $item): ?>
                    <li>
                        <a href="#<?= $id ?>"
                            class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                            <i class="fas fa-<?= $item['icon'] ?> w-5"></i>
                            <span class="ml-2"><?= $item['label'] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Contenu principal -->
        <section class="flex-grow space-y-16">
            <!-- Titre général -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-extrabold text-gray-900">À propos du Club</h1>
                <p class="mt-2 text-gray-600">Découvrez notre histoire, nos valeurs et ce qui fait la force de notre
                    communauté.</p>
            </div>

            <!-- Qui sommes-nous -->
            <article id="quisommesnous">
                <h2 class="text-2xl font-semibold mb-4">Qui sommes-nous ?</h2>
                <p class="bg-white rounded-lg shadow p-6 leading-relaxed">
                    Fondé en 1995, le Taekwondo Club Saint-Priest est un pilier de la discipline dans la région
                    lyonnaise,
                    réunissant passion, rigueur et esprit d’équipe. Nos entraîneurs, experts et compétiteurs de haut
                    niveau,
                    vous accompagnent dans votre progression, quel que soit votre âge ou votre expérience.
                </p>
            </article>

            <!-- Nos exploits -->
            <article id="nosexploits">
                <h2 class="text-2xl font-semibold mb-6">Nos exploits</h2>
                <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($exploits as $e): ?>
                        <li class="bg-white rounded-lg shadow p-6 flex items-start">
                            <i class="fas fa-medal text-blue-600 text-2xl mr-4 mt-1"></i>
                            <span class="flex-grow"><?= $e ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>

            <!-- Nos valeurs -->
            <article id="nosvaleurs">
                <h2 class="text-2xl font-semibold mb-6">Nos valeurs</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php

                    foreach ($valeurs as $titre => $desc): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-xl font-bold text-blue-600 mb-2"><?= $titre ?></h3>
                            <p><?= $desc ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <!-- Nos cours (carrousel) -->
            <article id="noscours" class="relative py-16">
                <h2 class="text-2xl font-semibold mb-6 text-center">Nos cours</h2>

                <!-- Flèches -->
                <button id="courses-prev"
                    class="absolute left-4 top-1/2 z-10 transform -translate-y-1/2 bg-white/90 hover:bg-white p-2 rounded-full shadow">
                    <i class="fas fa-chevron-left text-blue-600"></i>
                </button>
                <button id="courses-next"
                    class="absolute right-4 top-1/2 z-10 transform -translate-y-1/2 bg-white/90 hover:bg-white p-2 rounded-full shadow">
                    <i class="fas fa-chevron-right text-blue-600"></i>
                </button>

                <div id="courses-carousel" class="flex overflow-x-auto scroll-snap-x snap-mandatory gap-8 px-4 md:px-8">
                    <?php foreach ($classes as $c): ?>
                        <article
                            class="snap-start flex-shrink-0 w-full md:w-[45%] lg:w-[30%] bg-white rounded-2xl shadow-lg p-6">
                            <h3 class="text-2xl font-bold text-blue-600 mb-4"><?= htmlspecialchars($c['nom'], ENT_QUOTES) ?>
                            </h3>
                            <div class="flex items-baseline mb-4">
                                <span
                                    class="text-4xl font-extrabold text-gray-900"><?= htmlspecialchars($c['prix'], ENT_QUOTES) ?>€</span>
                                <span class="ml-2 text-gray-500">/ mois</span>
                            </div>
                            <p class="text-gray-700 mb-6"><?= htmlspecialchars($c['niveau'], ENT_QUOTES) ?></p>
                            <a href="classes.php?id=<?= $c['id'] ?>"
                                class="inline-block px-5 py-2 bg-blue-50 text-blue-600 font-semibold rounded-full hover:bg-blue-600 hover:text-white transition">
                                En savoir plus
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </article>

            <!-- Planning -->
            <article id="planning" class="mt-12">
                <h2 class="text-2xl font-semibold mb-6">Planning des cours</h2>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full table-auto divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Heure</th>
                                <?php foreach ($days as $d): ?>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?= $dayNames[$d] ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php for ($h = $start; $h <= $end; $h++): ?>
                                <tr>
                                    <td class="px-4 py-2 font-medium"><?= sprintf('%02d:00', $h) ?></td>
                                    <?php foreach ($days as $d): ?>
                                        <?php
                                        if (!empty($skip[$d][$h])) {
                                            // déjà pris par un rowspan
                                            continue;
                                        }
                                        if (!empty($schedule[$d][$h])) {
                                            foreach ($schedule[$d][$h] as $c) {
                                                $fd = (int) $c['heure_fin'];
                                                $span = max(1, $fd - $h);
                                                for ($i = $h; $i < $h + $span; $i++) {
                                                    $skip[$d][$i] = true;
                                                }
                                                ?>
                                                <td rowspan="<?= $span ?>" class="px-4 py-3 align-top border-t">
                                                    <div class="font-semibold text-blue-600">
                                                        <?= htmlspecialchars(strtoupper($c['nom']), ENT_QUOTES) ?>
                                                    </div>
                                                    <div class="text-sm"><?= htmlspecialchars(ucfirst($c['niveau']), ENT_QUOTES) ?></div>
                                                    <div class="text-xs text-gray-500">
                                                        <?= sprintf('%02d:00 – %02d:00', $h, $fd) ?>
                                                    </div>
                                                </td>
                                                <?php
                                            }
                                        } else {
                                            // cellule vide
                                            echo '<td class="px-4 py-3 border-t h-16"></td>';
                                        }
                                        ?>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>

</html>