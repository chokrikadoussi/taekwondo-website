<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Script de déconnexion, vide les données de session puis détruit celle-ci
 * Rédirection vers la page d'accueil
 * 
 * 
 */
session_start();

if (isset($_POST["submit-deconnect"])) {
    $_SESSION = array();
    session_destroy();
}

header('Location: index.php');
exit;