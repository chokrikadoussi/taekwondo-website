<?php
session_start();
require __DIR__ . '/fonction/fonctions.php';

requireConnexion();
$user = $_SESSION['user'];
$role = $user['role'];

$pageTitle = "Mon espace";
$pageActuelle = $_GET['page'] ?? 'overview';

$unreadCount = ($role === 'admin') ? count(array_filter(getListeMessages(), fn($m) => $m['is_read'] == 0)) : 0;

// AMÉLIORATION : La navigation est gérée par un tableau pour une meilleure maintenabilité
$profileNav = [
    'overview' => ['label' => 'Mon profil', 'icon' => 'user-circle', 'admin' => false],
    'users' => ['label' => 'Utilisateurs', 'icon' => 'users', 'admin' => true],
    'posts' => ['label' => 'Actualités', 'icon' => 'newspaper', 'admin' => true],
    'classes' => ['label' => 'Cours', 'icon' => 'chalkboard-teacher', 'admin' => true],
    'team' => ['label' => 'Entraîneurs', 'icon' => 'users-cog', 'admin' => true],
    'messages' => ['label' => 'Messages', 'icon' => 'envelope', 'admin' => true, 'count' => $unreadCount],
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        <aside class="lg:col-span-3">
            <div class="sticky top-28 bg-white rounded-xl shadow-md p-4">
                <ul class="flex justify-around lg:flex-col lg:space-y-1">
                    <?php foreach ($profileNav as $pageId => $navItem): ?>
                        <?php if ($navItem['admin'] && $role !== 'admin')
                            continue; // On n'affiche que les liens autorisés ?>
                        <li>
                            <a href="profile.php?page=<?= $pageId ?>"
                                class="relative flex items-center p-3 rounded-lg font-medium transition-colors duration-200
                                      <?= $pageActuelle === $pageId ? 'bg-blue-100 text-blue-700' : 'text-slate-600 hover:bg-slate-100' ?>">

                                <i class="fas fa-fw fa-<?= $navItem['icon'] ?> w-6 text-center text-lg"></i>
                                <span class="ml-3 hidden lg:inline"><?= $navItem['label'] ?></span>

                                <?php if (!empty($navItem['count'])): ?>
                                    <span
                                        class="absolute top-2 right-2 lg:relative lg:top-auto lg:right-auto lg:ml-auto bg-red-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
                                        <?= $navItem['count'] ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <li>
                        <form action="logout.php" method="post" class="w-full">
                            <button type="submit" name="submit-deconnect"
                                class="w-full flex items-center p-3 rounded-lg text-red-600 font-medium hover:bg-red-50 transition-colors duration-200">
                                <i class="fas fa-fw fa-sign-out-alt w-6 text-center text-lg"></i>
                                <span class="ml-3 hidden lg:inline">Déconnexion</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </aside>

        <section class="lg:col-span-9 bg-white rounded-xl shadow-md p-6 lg:p-8">
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Bonjour,
                    <?= htmlspecialchars($user['prenom'], ENT_QUOTES) ?> !</h1>
                <p class="mt-1 text-slate-500">Bienvenue dans votre espace personnel.</p>
            </header>

            <?php
            // La logique du routeur est conservée, elle est excellente
            switch ($pageActuelle) {
                case 'users':
                case 'posts':
                case 'classes':
                case 'team':
                case 'messages':
                    if ($role === 'admin') {
                        include __DIR__ . "/includes/profile/{$pageActuelle}.php";
                    } else {
                        echo '<p class="text-red-600">Accès refusé.</p>';
                    }
                    break;
                default:
                    include __DIR__ . '/includes/profile/overview.php';
                    break;
            }
            ?>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>

</html>