<?php
session_start();
$pageTitle = 'Cours';
$pageActuelle = 'classes';

require __DIR__ . '/fonction/fonctions.php';
$pdo = connexionBaseDeDonnees();

// 1) Récupération des cours
$classes = $pdo
    ->query("
      SELECT
        id,
        nom,
        level,
        SUBSTRING(description, 1, 150) AS extrait_desc,
        description
      FROM classes
      ORDER BY nom
    ")
    ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <h1 class="text-3xl font-semibold mb-8 text-center">Nos cours</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($classes as $c): ?>
                <article class="bg-white rounded-lg shadow p-6 flex flex-col">
                    <!-- Si tu as une image de cours -->
                    <?php if (!empty($c['image'] ?? '')): ?>
                        <img src="<?= htmlspecialchars($c['image'], ENT_QUOTES) ?>"
                            alt="Illustration du cours <?= htmlspecialchars($c['nom'], ENT_QUOTES) ?>"
                            class="h-40 w-full object-cover rounded-md mb-4">
                    <?php endif; ?>

                    <h2 class="text-xl font-bold mb-1"><?= htmlspecialchars($c['nom'], ENT_QUOTES) ?></h2>
                    <p class="text-sm text-indigo-600 mb-3 uppercase tracking-wide">
                        <?= htmlspecialchars($c['level'], ENT_QUOTES) ?>
                    </p>

                    <p class="text-gray-700 flex-grow">
                        <?php
                        $fullDesc = strip_tags($c['description']);
                        $excerpt = htmlspecialchars($c['extrait_desc'], ENT_QUOTES);
                        echo mb_strlen($fullDesc) > 150
                            ? $excerpt . '…'
                            : $excerpt;
                        ?>
                    </p>

                    <a href="classes.php#course-<?= $c['id'] ?>"
                        class="mt-4 inline-block self-start bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition">
                        En savoir plus
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>