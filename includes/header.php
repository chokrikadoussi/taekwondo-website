<header class="sticky top-0 left-0 w-full bg-black text-white z-20 shadow">
    <div class="mx-auto px-4 py-4 md:px-8 h-20 flex items-center justify-between">
        <!-- Logo -->
        <a href="index.php" class="flex items-center space-x-2" aria-label="Aller à l’accueil">
            <img src="img/logo.png" alt="Logo Taekwondo Pise" class="h-10 w-auto mr-2">
            <span class="text-xl font-bold md:hidden">Taekwondo Pise</span>
        </a>

        <!-- Bouton mobile -->
        <button id="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false" class="md:hidden p-2 text-xl">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Menu mobile (caché par défaut) -->
        <div id="mobile-menu"
            class="absolute top-full left-4 right-4 bg-black text-white rounded-b-lg shadow-lg hidden md:hidden z-20">
            <ul class="flex flex-col divide-y divide-gray-800">
                <li><a href="index.php" class="block px-4 py-3">Accueil</a></li>
                <li><a href="about.php" class="block px-4 py-3">Le Club</a></li>
                <li><a href="team.php" class="block px-4 py-3">Équipe</a></li>
                <li><a href="news.php" class="block px-4 py-3">Actualités</a></li>
                <li><a href="contact.php" class="block px-4 py-3">Contact</a></li>
                <?php if (!isset($_SESSION['user']['id'])): ?>
                    <li><a href="login.php" class="block px-4 py-3">Connexion</a></li>
                    <li><a href="register.php" class="block px-4 py-3">Inscription</a></li>
                <?php else: ?>
                    <li><a href="profile.php" class="block px-4 py-3">Mon Compte</a></li>
                    <li>
                        <form action="logout.php" method="post" class="w-full">
                            <button type="submit" name="submit-deconnect" class="w-full text-left px-4 py-3">
                                Déconnexion
                            </button>
                        </form>
                    </li>
                <?php endif; ?>
            </ul>
        </div>


        <!-- Liens de navigation -->
        <nav aria-label="Menu principal" class="hidden md:flex md:items-center space-y-4 md:space-y-0 md:space-x-8">
            <ul class="flex space-x-2 md:space-x-2 lg:space-x-2">
                <li><a href="index.php"
                        class="block px-5 py-2 rounded-md text-sm font-medium <?= $pageActuelle === 'home' ? 'bg-white text-black' : 'hover:bg-gray-800 hover:text-accent' ?>">Accueil</a>
                </li>
                <li><a href="about.php"
                        class="block px-5 py-2 rounded-md text-sm font-medium <?= $pageActuelle === 'about' ? 'bg-white text-black' : 'hover:bg-gray-800 hover:text-accent' ?>">Le
                        Club</a></li>
                <li><a href="team.php"
                        class="block px-5 py-2 rounded-md text-sm font-medium <?= $pageActuelle === 'team' ? 'bg-white text-black' : 'hover:bg-gray-800 hover:text-accent' ?>">Équipe</a>
                </li>
                <li><a href="news.php"
                        class="block px-5 py-2 rounded-md text-sm font-medium <?= $pageActuelle === 'news' ? 'bg-white text-black' : 'hover:bg-gray-800 hover:text-accent' ?>">Actualités</a>
                </li>
                <li><a href="contact.php"
                        class="block px-5 py-2 rounded-md text-sm font-medium <?= $pageActuelle === 'contact' ? 'bg-white text-black' : 'hover:bg-gray-800 hover:text-accent' ?>">Contact</a>
                </li>
            </ul>
        </nav>
        <div class="hidden md:flex items-center space-x-2 md:space-x-4 lg:space-x-4">
            <?php if (!isset($_SESSION['user']['id'])) { ?>
                <a href="login.php"
                    class="block px-7 py-2 text-sm font-medium bg-white text-black rounded-md hover:bg-gray-100 transition">Connexion</a>
                <a href="register.php" class="block px-3 py-2 rounded-md text-sm font-medium">Inscription</a>
            <?php } else { ?>
                <a href="profile.php" class="block px-3 py-2 rounded-md text-sm font-medium">Mon Compte</a>
                <form action="logout.php" method="post" class="inline">
                    <button type="submit" name="submit-deconnect"
                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition font-medium">
                        Déconnexion
                    </button>
                </form>
            <?php } ?>
        </div>
    </div>
</header>