<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Template de Header et navigation du site
 * 
 */

// On définit les liens de navigation dans un tableau pour une meilleure maintenabilité
$navLinks = [
    'Accueil' => ['url' => 'index.php', 'id' => 'home'],
    'Le Club' => ['url' => 'about.php', 'id' => 'about'],
    'Équipe' => ['url' => 'team.php', 'id' => 'team'],
    'Actualités' => ['url' => 'news.php', 'id' => 'news'],
    'Contact' => ['url' => 'contact.php', 'id' => 'contact'],
];
?>
<header class="sticky top-0 left-0 w-full bg-black text-white z-50 shadow-md">
    <div class="container mx-auto px-4 flex items-center justify-between h-[72px]">
        <a href="index.php" class="flex items-center space-x-3" aria-label="Aller à l’accueil">
            <img src="img/logo.png" alt="Logo Taekwondo Pise" class="h-10 w-auto">
            <span class="text-xl font-bold">Taekwondo Pise</span>
        </a>

        <nav aria-label="Menu principal" class="hidden md:flex items-center gap-2">
            <ul class="flex items-center gap-2">
                <?php foreach ($navLinks as $label => $link): ?>
                    <li>
                        <a href="<?= $link['url'] ?>" class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200
                                  <?= ($pageActuelle === $link['id'])
                                      ? 'bg-white text-black'
                                      : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
                            <?= $label ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="hidden md:flex items-center gap-4">
            <?php if (!isset($_SESSION['user']['id'])): ?>
                <a href="login.php"
                    class="px-5 py-2 text-sm font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition">Connexion</a>
                <a href="register.php"
                    class="px-5 py-2 text-sm font-semibold bg-white text-black rounded-lg hover:bg-slate-200 transition">Inscription</a>
            <?php else: ?>
                <a href="profile.php"
                    class="px-5 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Mon
                    Compte</a>
            <?php endif; ?>
        </div>

        <button id="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false"
            class="md:hidden p-2 text-xl relative z-50">
            <i id="nav-toggle-icon" class="fas fa-bars transition-transform duration-300"></i>
        </button>
    </div>

    <div id="mobile-menu" class="fixed top-0 left-0 w-full h-full bg-black/90 backdrop-blur-sm z-40
                transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden">
        <div class="flex justify-end p-4">
            <button id="nav-close" aria-label="Fermer le menu" class="p-2 text-2xl text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav>
            <ul class="flex flex-col items-center justify-center h-full gap-y-6 -mt-16">
                <?php foreach ($navLinks as $label => $link): ?>
                    <li><a href="<?= $link['url'] ?>"
                            class="text-2xl font-bold text-slate-200 hover:text-white"><?= $label ?></a></li>
                <?php endforeach; ?>

                <li class="pt-8">
                    <?php if (!isset($_SESSION['user']['id'])): ?>
                        <a href="login.php"
                            class="px-8 py-3 text-lg font-semibold bg-white text-black rounded-lg hover:bg-slate-200 transition">Connexion</a>
                    <?php else: ?>
                        <a href="profile.php"
                            class="px-8 py-3 text-lg font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Mon
                            Compte</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
</header>