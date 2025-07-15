<?php
session_start();
$pageTitle = 'À propos';
$pageActuelle = 'about';

require __DIR__ . '/fonction/fonctions.php';
$classes = getListeCours();

$nav = [
    'quisommesnous' => ['icon' => 'info-circle',    'label' => 'Qui sommes-nous?'],
    'nosexploits'   => ['icon' => 'trophy',         'label' => 'Nos exploits'],
    'nosvaleurs'    => ['icon' => 'heart',          'label' => 'Nos valeurs'],
    'noscours'      => ['icon' => 'dumbbell',       'label' => 'Nos cours'],
    'planning'      => ['icon' => 'calendar-alt',   'label' => 'Planning'],
];

$exploits = [
    'Champions régionaux 2023 & 2024',
    'Plus de 50 médailles nationales',
    'Stage international avec Maître Kim (2022)',
];

$valeurs = [
    'Respect'     => 'Éthique et bienveillance envers tous.',
    'Excellence'  => 'Quête de maîtrise technique et mentale.',
    'Communauté'  => 'Esprit d’entraide sur et hors du tatami.',
];

// Planning
$rawSchedule = getCoursPlanning();
$dayNames = [2=>'Lundi',3=>'Mardi',4=>'Mercredi',5=>'Jeudi',6=>'Vendredi',7=>'Samedi'];
$days = array_keys($dayNames);
$start = 12; $end = 22;
$schedule = $skip = [];
foreach ($rawSchedule as $r) {
    $d = (int)$r['jour'];
    $h = (int)$r['heure_debut'];
    $schedule[$d][$h][] = $r;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <?php include __DIR__.'/includes/head.php'; ?>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800 overflow-x-hidden">
  <?php include __DIR__.'/includes/header.php'; ?>

  <main class="flex-grow mx-auto px-4 py-12 lg:flex lg:gap-8 max-w-screen w-full">
    <!-- desktop nav -->
    <aside class="hidden lg:block sticky top-24 self-start w-80 bg-white rounded-lg shadow p-4">
      <ul class="space-y-3">
        <?php foreach($nav as $id=>$item): ?>
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

    <section class="flex-grow space-y-16">
      <!-- header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900">À propos du Club</h1>
        <p class="mt-2 text-gray-600">Découvrez notre histoire, nos valeurs et ce qui fait la force de notre communauté.</p>
      </div>

      <!-- Qui sommes-nous -->
      <article id="quisommesnous" class="space-y-4">
        <h2 class="text-2xl font-semibold">Qui sommes-nous&nbsp;?</h2>
        <p class="bg-white p-6 rounded-lg shadow leading-relaxed">
          Fondé en 1995, le Taekwondo Club Saint-Priest est un pilier de la discipline dans la région lyonnaise, réunissant passion, rigueur et esprit d’équipe. Nos entraîneurs, experts et compétiteurs de haut niveau, vous accompagnent dans votre progression, quel que soit votre âge ou votre expérience.
        </p>

          <br>Pourquoi pratiquer au Taekwondo Saint-Priest ?<br>
Art martial d'origine sud-coréenne, le Taekwondo véhicule des valeurs de maîtrise de soi, de respect. Notre équipe s'efforce d'inculquer ces principes à nos élèves afin de leur donner une force d'épanouissement et de cultiver un esprit ouvert.
      </article>

      <!-- Nos exploits -->
      <article id="nosexploits" class="space-y-4">
        <h2 class="text-2xl font-semibold">Nos exploits</h2>
        <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach($exploits as $e): ?>
            <li class="bg-white p-6 rounded-lg shadow flex items-start">
              <i class="fas fa-medal text-blue-600 text-2xl mr-4 mt-1"></i>
              <span><?= $e ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </article>

      <!-- Nos valeurs -->
      <article id="nosvaleurs" class="space-y-4">
        <h2 class="text-2xl font-semibold">Nos valeurs</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <?php foreach($valeurs as $t=>$d): ?>
            <div class="bg-white p-6 rounded-lg shadow">
              <h3 class="text-xl font-bold text-blue-600 mb-2"><?= $t ?></h3>
              <p><?= $d ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </article>

      <!-- Nos cours carousel -->
      <article id="noscours" class="relative py-16">
        <h2 class="text-2xl font-semibold text-center mb-6">Nos cours</h2>
        <button id="courses-prev" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white">
          <i class="fas fa-chevron-left text-blue-600"></i>
        </button>
        <button id="courses-next" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white">
          <i class="fas fa-chevron-right text-blue-600"></i>
        </button>
        <div id="courses-carousel" class="flex gap-6 overflow-x-auto px-2 scroll-snap-x snap-mandatory">
          <?php foreach($classes as $c): ?>
            <article class="snap-start flex-shrink-0 w-full md:w-1/2 lg:w-1/3 bg-white p-6 rounded-2xl shadow">
              <h3 class="text-2xl font-bold text-blue-600 mb-3"><?= htmlspecialchars($c['nom'],ENT_QUOTES) ?></h3>
              <div class="flex items-baseline mb-4">
                <span class="text-4xl font-extrabold text-gray-900"><?= htmlspecialchars($c['prix'],ENT_QUOTES) ?>€</span>
                <span class="ml-2 text-gray-500">/ mois</span>
              </div>
              <p class="text-gray-700 mb-4"><?= htmlspecialchars($c['niveau'],ENT_QUOTES) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </article>

      <!-- Planning -->
      <article id="planning" class="space-y-4">
        <h2 class="text-2xl font-semibold">Planning des cours</h2>
        <div class="overflow-x-auto bg-white rounded-lg shadow">
          <table class="min-w-full table-auto divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Heure</th>
                <?php foreach($days as $d): ?>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"><?= $dayNames[$d] ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php for($h=$start;$h<=$end;$h++): ?>
                <tr>
                  <td class="px-4 py-2 font-medium"><?= sprintf('%02d:00',$h) ?></td>
                  <?php foreach($days as $d): ?>
                    <?php if(!empty($skip[$d][$h])) { continue; } ?>
                    <?php if(!empty($schedule[$d][$h])): ?>
                      <?php foreach($schedule[$d][$h] as $c): 
                        $endH = (int)$c['heure_fin'];
                        $span = max(1,$endH-$h);
                        for($i=$h;$i<$h+$span;$i++) $skip[$d][$i]=true;
                      ?>
                        <td rowspan="<?= $span ?>" class="px-4 py-2 align-top border-t">
                          <div class="font-semibold text-blue-600"><?= strtoupper(htmlspecialchars($c['nom'],ENT_QUOTES)) ?></div>
                          <div class="text-sm"><?= ucfirst(htmlspecialchars($c['niveau'],ENT_QUOTES)) ?></div>
                          <div class="text-xs text-gray-500"><?= sprintf('%02d:00–%02d:00',$h,$endH) ?></div>
                        </td>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <td class="px-4 py-2 border-t h-16"></td>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </main>

  <?php include __DIR__.'/includes/footer.php'; ?>
  <script src="js/main.js"></script>
</body>
</html>
