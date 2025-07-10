<?php
session_start();
require __DIR__ . '/fonction/fonctions.php';

// Vérifie si l'utilisateur est connecté, si non alors il est redirigé vers la page de connexion (cf. fonction isConnected())
isConnected();

$user = $_SESSION["user"];
$role = $user["role"];

$pageTitle = "Profile";
$pageActuelle = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'overview';
$pagesAdmin = array('users', 'posts', 'classes', 'team', 'messages', 'settings', 'create_user', 'edit_user', 'delete_user', );

// Si l’utilisateur est admin, on récupère le nombre de messages non lus
$unreadCount = 0;
if ($role === 'admin') {
    $pdo = connexionBaseDeDonnees();
    $unreadCount = (int) $pdo
        ->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")
        ->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar -->
        <aside class="w-full md:w-1/4 bg-gray-100 rounded-lg p-4">
            <nav class="space-y-1">
                <a href="profile.php"
                    class="block px-4 py-2 rounded hover:bg-blue-100 <?= $pageActuelle === 'overview' ? 'bg-blue-200' : '' ?>">Mon
                    profil</a>

                <?php if ($role === 'admin') { ?>
                    <?php foreach (array(
                        'users' => 'Gestion utilisateurs',
                        'posts' => 'Actualités',
                        'classes' => 'Cours',
                        'team' => 'Entraîneurs',
                        'messages' => 'Messages',
                        'settings' => 'Paramètres',
                    ) as $key => $value) { ?>
                        <a href="profile.php?page=<?= $key ?>"
                            class="block px-4 py-2 rounded hover:bg-blue-100 <?= $pageActuelle === $key ? 'bg-blue-200' : '' ?>">
                            <span><?= htmlspecialchars($value, ENT_QUOTES) ?></span>
                            <?php if ($key === 'messages' && $unreadCount > 0): ?>
                                <span class="ml-2 inline-block bg-red-600 text-white text-xs font-medium px-2 py-0.5 rounded-full">
                                    <?= $unreadCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php }
                } ?>

                <form action="logout.php" method="post" class="mt-4">
                    <button type="submit" name="submit-deconnect"
                        class="w-full text-left px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Déconnexion</button>
                </form>
            </nav>
        </aside>

        <!-- Contenu -->
        <section class="w-full md:w-3/4 bg-white rounded-lg shadow p-6">
            <?php
            switch ($pageActuelle) {
                case (in_array($pageActuelle, $pagesAdmin) ? $pageActuelle : ''):
                    if ($role === 'admin') {
                        include "includes/profile/" . $pageActuelle . ".php";
                    } else {
                        echo '<p class="text-red-600">Accès refusé.</p>';
                    }
                    break;
                case 'edit_profile':
                    include 'includes/profile/edit_profile.php';
                    break;
                default:
                    include 'includes/profile/overview.php';
                    break;
            }
            ?>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>

</html>