<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Regroupement de toutes les constantes du site web (bdd, log...)
 *  
 */

// Constantes de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'tkd');
define('DB_CHARSET', 'utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', '');

// Constantes de sécurité Mot de passe
define('PSSWD_MIN_LEN', 8);

// Constantes de log
define('LOG_PATH', 'logs/bdd_erreurs.log');