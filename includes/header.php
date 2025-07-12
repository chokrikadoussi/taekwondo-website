<?php

?>
<header class="bg-white shadow">
    <div class="mx-auto max-w-7xl px-4 py-4 flex items-center justify-between">
        <a href="index.php" class="flex items-center" aria-label="Aller à l’accueil">
            <img src="img/logo.png" alt="Logo Taekwondo Pise" class="h-10 w-auto mr-2">
            <span class="text-xl font-semibold">Taekwondo Pise</span>
        </a>
        <nav aria-label="Menu principal">
            <ul class="flex space-x-6">
                <li><a href="index.php" class="hover:text-accent">Accueil</a></li>
                <li><a href="about.php" class="hover:text-accent">Le Club</a></li>
                <li><a href="team.php" class="hover:text-accent">Équipe</a></li>
                <li><a href="news.php" class="hover:text-accent">Actualités</a></li>
                <li><a href="contact.php" class="hover:text-accent">Contact</a></li>
                <?php if (!isset($_SESSION['user']['id'])) { ?>
                    <li><a href="login.php" class="hover:text-accent">Connexion</a></li>
                    <li><a href="register.php" class="hover:text-accent">Inscription</a></li>
                <?php } else { ?>
                    <li><a href="profile.php" class="hover:text-accent">Mon Compte</a></li>
                    <li>
                        <form action="logout.php" method="post" class="inline">
                            <button type="submit" name="submit-deconnect"
                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                Déconnexion
                            </button>
                        </form>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </div>
</header>