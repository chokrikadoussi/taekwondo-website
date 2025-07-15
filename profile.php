<?php
session_start();
require __DIR__ . '/fonction/fonctions.php';

requireConnexion();
$user = $_SESSION['user'];
$role = $user['role'];

$pageTitle = "Mon espace";
$pageActuelle = $_GET['page'] ?? 'overview';
$adminPages = ['users', 'posts', 'classes', 'team', 'messages', 'settings', 'create_user', 'edit_user', 'delete_user'];

// Si l’utilisateur est admin, on récupère le nombre de messages non lus
$unreadCount = count(array_filter(getListeMessages(), fn($m) => $m['is_read'] == 0));
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="bg-black font-sans text-gray-800 min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-2 py-6 lg:py-8 grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Sidebar -->
        <aside class="lg:col-span-3 bg-white rounded-2xl shadow p-4 sticky top-24">
            <ul class="flex justify-around lg:flex-col lg:space-y-2">
                <!-- Mon profil -->
                <li>
                    <a href="profile.php"
                        class="relative flex items-center px-3 py-2 rounded <?= $pageActuelle === 'overview' ? 'bg-blue-100 text-blue-800' : 'hover:bg-blue-50' ?> transition">
                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-lg lg:mr-2">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <span class="flex-1 hidden lg:inline text-left">Mon profil</span>
                    </a>
                </li>

                <?php if ($role === 'admin'): ?>
                    <!-- Utilisateurs -->
                    <li>
                        <a href="profile.php?page=users"
                            class="relative flex items-center px-3 py-2 rounded <?= $pageActuelle === 'users' ? 'bg-blue-100 text-blue-800' : 'hover:bg-blue-50' ?> transition">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-lg lg:mr-2">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="flex-1 hidden lg:inline text-left">Utilisateurs</span>
                        </a>
                    </li>
                    <!-- Actualités -->
                    <li>
                        <a href="profile.php?page=posts"
                            class="relative flex items-center px-3 py-2 rounded <?= $pageActuelle === 'posts' ? 'bg-blue-100 text-blue-800' : 'hover:bg-blue-50' ?> transition">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-lg lg:mr-2">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <span class="flex-1 hidden lg:inline text-left">Actualités</span>
                        </a>
                    </li>
                    <!-- Cours -->
                    <li>
                        <a href="profile.php?page=classes"
                            class="relative flex items-center px-3 py-2 rounded <?= $pageActuelle === 'classes' ? 'bg-blue-100 text-blue-800' : 'hover:bg-blue-50' ?> transition">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-lg lg:mr-2">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <span class="flex-1 hidden lg:inline text-left">Cours</span>
                        </a>
                    </li>
                    <!-- Équipe -->
                    <li>
                        <a href="profile.php?page=team"
                            class="relative flex items-center px-3 py-2 rounded <?= $pageActuelle === 'team' ? 'bg-blue-100 text-blue-800' : 'hover:bg-blue-50' ?> transition">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-lg lg:mr-2">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <span class="flex-1 hidden lg:inline text-left">Entraîneurs</span>
                        </a>
                    </li>
                    <!-- Messages -->
                    <li>
                        <a href="profile.php?page=messages"
                            class="relative flex items-center pl-3 py-2 rounded <?= $pageActuelle === 'messages' ? 'bg-blue-100 text-blue-800' : 'hover:bg-blue-50' ?> transition">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-lg lg:mr-2">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <span class="flex-1 hidden lg:inline text-left">Messages</span>
                            <?php if ($unreadCount > 0): ?>
                                <span
                                    class="relative bottom-3 right-3 lg:inline-block lg:bottom-0 lg:right-0 bg-red-600 text-white text-xs font-bold leading-none px-2 py-0.5 rounded-full">
                                    <?= $unreadCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Déconnexion -->
                <li>
                    <form action="logout.php" method="post">
                        <button type="submit" name="submit-deconnect"
                            class="flex flex-col items-start lg:flex-row lg:items-center pl-3 py-2 rounded hover:bg-red-50 transition w-full">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-lg lg:mr-2">
                                <i class="fas fa-sign-out-alt text-red-600"></i>
                            </div>
                            <span class="flex-1 hidden lg:inline text-left text-red-600">Déconnexion</span>
                        </button>
                    </form>
                </li>
            </ul>
        </aside>


        <!-- Content -->
        <section class="lg:col-span-9 bg-white rounded-2xl shadow p-6">
            <?php
            switch ($pageActuelle) {
                case (in_array($pageActuelle, $adminPages) ? $pageActuelle : ''):
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
</body>

</html>