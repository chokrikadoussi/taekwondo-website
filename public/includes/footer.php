<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Template de Footer du site, liens vers  les réseaux du Club de St Priest et remerciement
 * 
 * TODO: Ajouter page de mentions légales, Politique de confidentialité et FAQ
 * 
 */
?>
<footer class="bg-black border-t mt-12 relative bottom-0 w-full">
    <div class="mx-auto max-w-7xl px-4 py-6 text-center text-sm text-white">
        <p>&copy; <?= date('Y') ?> Taekwondo Club St Priest. Tous droits réservés.</p>

        <!-- Icônes réseaux sociaux -->
        <div class="mt-4 flex justify-center space-x-6">
            <a href="https://www.facebook.com/tkdsaintpriest" target="_blank" rel="noopener" aria-label="Facebook"
                class="hover:text-accent">
                <i class="fab fa-facebook fa-lg"></i>
            </a>
            <a href="https://www.instagram.com/tkdsaintpriest" target="_blank" rel="noopener" aria-label="Instagram"
                class="hover:text-accent">
                <i class="fab fa-instagram fa-lg"></i>
            </a>
            <a href="https://x.com/tkdsaintpriest" target="_blank" rel="noopener" aria-label="Twitter"
                class="hover:text-accent">
                <i class="fab fa-twitter fa-lg"></i>
            </a>
        </div>

        <p class="mt-2">
            Merci au Master 2 PISE pour leur formation et leur soutien • Promotion 2024-2025
        </p>
    </div>
</footer>
<script src="js/main.js"></script>