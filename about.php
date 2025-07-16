<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Page A Propos (présentation du club).
 * -> Découpé en plusieurs sections : Infos, Exploits, Valeurs, Planning    
 * 
 */
session_start();
$pageTitle = 'À propos';
$pageActuelle = 'about';

require __DIR__ . '/fonction/fonctions.php';
$classes = getListeCours();  // Récupération de la liste des cours

$nav = [
    'quisommesnous' => ['icon' => 'info-circle', 'label' => 'Qui sommes-nous?'],
    'nosexploits' => ['icon' => 'trophy', 'label' => 'Nos exploits'],
    'nosvaleurs' => ['icon' => 'heart', 'label' => 'Nos valeurs'],
    'noscours' => ['icon' => 'dumbbell', 'label' => 'Nos cours'],
    'planning' => ['icon' => 'calendar-alt', 'label' => 'Planning'],
];

// Jeu de données statiques à afficher
$exploits = [
    'Champions régionaux 2023 & 2024',
    'Plus de 50 médailles nationales',
    'Stage international avec Maître Kim (2022)',
    'Participation au championnat du monde 2025',
];
$valeurs = [
    'Respect' => 'Éthique et bienveillance envers tous.',
    'Excellence' => 'Quête de maîtrise technique et mentale.',
    'Communauté' => 'Esprit d’entraide sur et hors du tatami.',
];

// Affichage du planning
$planningEntier = getCoursPlanning();
$mappingJour = [2 => 'Lundi', 3 => 'Mardi', 4 => 'Mercredi', 5 => 'Jeudi', 6 => 'Vendredi', 7 => 'Samedi']; // Mapping des jours de la semaine depuis la bdd
$jours = array_keys($mappingJour);
$start = 12;  // Première heure à afficher dans le planning
$end = 22;    // Dernière heure à afficher dans le planning
$planning = array();
$skip = array();
// Stockage des heures pour chaque cours existant
foreach ($planningEntier as $cours) {
    $j = (int) $cours['jour'];
    $h = (int) $cours['heure_debut'];
    $planning[$j][$h][] = $cours;
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 lg:flex lg:gap-12">
        <aside class="hidden lg:block sticky top-28 self-start w-64 flex-shrink-0">
            <nav class="bg-white rounded-lg shadow-md p-4">
                <ul class="space-y-2">
                    <?php foreach ($nav as $id => $item) { ?>
                        <li>
                            <a href="#<?= $id ?>"
                                class="flex items-center px-3 py-2 rounded-lg text-slate-700 font-medium hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200">
                                <i class="fas fa-fw fa-<?= $item['icon'] ?> text-slate-400 mr-3"></i>
                                <span><?= $item['label'] ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        </aside>

        <section class="flex-grow space-y-20 max-w-5xl">
            <article id="quisommesnous" class="text-center scroll-mt-24">
                <h1 class="text-4xl lg:text-5xl font-extrabold text-slate-900">
                    À propos de notre Club
                </h1>
                <p class="mt-4 text-lg text-slate-600">Découvrez notre histoire, nos valeurs et ce qui fait la force de
                    notre communauté.</p>
            </article>

            <article class="space-y-4 scroll-mt-24">
                <h2 class="text-3xl font-bold text-slate-900">Qui sommes-nous&nbsp;?</h2>
                <div class="bg-white p-6 rounded-lg shadow-md space-y-4 text-slate-700 leading-relaxed">
                    <p>
                        Fondé en 1995, le Taekwondo Club Saint-Priest est un pilier de la discipline dans la région
                        lyonnaise, réunissant passion, rigueur et esprit d’équipe. Nos entraîneurs, experts et
                        compétiteurs de haut niveau, vous accompagnent dans votre progression, quel que soit votre âge
                        ou votre expérience.
                    </p>
                    <p>
                        <strong class="text-slate-800">Pourquoi pratiquer au Taekwondo Saint-Priest ?</strong><br>
                        Art martial d'origine sud-coréenne, le Taekwondo véhicule des valeurs de maîtrise de soi, de
                        respect. Notre équipe s'efforce d'inculquer ces principes à nos élèves afin de leur donner une
                        force d'épanouissement et de cultiver un esprit ouvert.
                    </p>
                </div>
            </article>

            <article id="nosexploits" class="space-y-6 scroll-mt-24">
                <h2 class="text-3xl font-bold text-slate-900">Nos exploits</h2>
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($exploits as $e) { ?>
                        <li class="bg-white p-6 rounded-lg shadow-md flex items-start space-x-4">
                            <div
                                class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full h-12 w-12 flex items-center justify-center">
                                <i class="fas fa-medal text-xl"></i>
                            </div>
                            <span class="font-medium text-slate-700 pt-3"><?= $e ?></span>
                        </li>
                    <?php } ?>
                </ul>
            </article>

            <article id="nosvaleurs" class="space-y-6 scroll-mt-24">
                <h2 class="text-3xl font-bold text-slate-900">Nos valeurs</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($valeurs as $titre => $desc) { ?>
                        <div
                            class="bg-white p-6 rounded-lg shadow-md transition duration-300 hover:shadow-xl hover:-translate-y-1">
                            <h3 class="text-xl font-bold text-blue-600 mb-2"><?= $titre ?></h3>
                            <p class="text-slate-700"><?= $desc ?></p>
                        </div>
                    <?php } ?>
                </div>
            </article>

            <article id="noscours" class="relative group scroll-mt-24">
                <h2 class="text-3xl font-bold text-slate-900 text-center mb-6">Nos cours</h2>
                <button id="courses-prev"
                    class="absolute left-0 top-1/2 z-10 transform -translate-y-1/2 -translate-x-4 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white transition-opacity opacity-0 group-hover:opacity-100 disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left text-blue-600 h-5 w-5"></i>
                </button>
                <button id="courses-next"
                    class="absolute right-0 top-1/2 z-10 transform -translate-y-1/2 translate-x-4 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white transition-opacity opacity-0 group-hover:opacity-100 disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right text-blue-600 h-5 w-5"></i>
                </button>
                <div id="courses-carousel" class="flex gap-6 overflow-x-auto py-4 px-2 scroll-snap-x snap-mandatory">
                    <?php foreach ($classes as $c) { ?>
                        <div
                            class="snap-start flex-shrink-0 w-10/12 md:w-1/2 lg:w-[32%] bg-white p-8 rounded-2xl shadow-lg flex flex-col">
                            <h3 class="text-2xl font-bold text-blue-600 mb-3"><?= htmlspecialchars($c['nom'], ENT_QUOTES) ?>
                            </h3>
                            <div class="flex items-baseline mb-4">
                                <span
                                    class="text-4xl font-extrabold text-slate-900"><?= htmlspecialchars($c['prix'], ENT_QUOTES) ?>€</span>
                                <span class="ml-2 text-slate-500">/ mois</span>
                            </div>
                            <p class="text-slate-600 mb-6 flex-grow"><?= htmlspecialchars($c['niveau'], ENT_QUOTES) ?></p>
                        </div>
                    <?php } ?>
                </div>
            </article>

            <article id="planning" class="space-y-6 scroll-mt-24">
                <h2 class="text-3xl font-bold text-slate-900">Planning des cours</h2>
                <div class="overflow-x-auto bg-white rounded-lg shadow-md p-2">
                    <table class="min-w-full table-auto border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="w-20 px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">
                                    Heure</th>
                                <?php foreach ($jours as $j) { ?>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">
                                        <?= $mappingJour[$j] ?>
                                    </th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php for ($h = $start; $h <= $end; $h++) { ?>
                                <tr>
                                    <td class="px-4 py-2 text-right font-medium text-sm text-slate-400">
                                        <?= sprintf('%02d:00', $h) ?>
                                    </td>
                                    <?php foreach ($jours as $j) { ?>
                                        <?php if (!empty($skip[$j][$h]))
                                            continue; ?>
                                        <?php if (!empty($planning[$j][$h])) { ?>
                                            <?php foreach ($planning[$j][$h] as $c) {
                                                $heureFin = (int) $c['heure_fin'];
                                                $span = max(1, $heureFin - $h);
                                                for ($i = $h; $i < $h + $span; $i++)
                                                    $skip[$heureFin][$i] = true;
                                                ?>
                                                <td rowspan="<?= $span ?>" class="p-1 align-top">
                                                    <div
                                                        class="bg-blue-50 border-l-4 border-blue-500 rounded-md p-3 h-full flex flex-col">
                                                        <div class="font-bold text-sm text-blue-800">
                                                            <?= strtoupper(htmlspecialchars($c['nom'], ENT_QUOTES)) ?>
                                                        </div>
                                                        <div class="text-sm text-blue-700">
                                                            <?= ucfirst(htmlspecialchars($c['niveau'], ENT_QUOTES)) ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500 mt-auto pt-1">
                                                            <?= sprintf('%02d:00–%02d:00', $h, $heureFin) ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <td class="h-16"></td>
                                        <?php } ?>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/main.js" defer></script>
</body>

</html>