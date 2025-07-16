<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Page d'erreur qui s'affiche lorsque la bdd est en erreur
 * 
 * TODO: Vérifier l'appel de la page lors d'une erreur de connexion à la bdd
 * 
 */
$pageTitle = 'Erreur base de données';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800">

    <main class="flex-grow container mx-auto px-4 py-16 text-center">
        <h1 class="text-3xl font-bold mb-4">Oups !</h1>
        <p class="text-lg mb-6">Impossible de se connecter à la base de données pour le moment.</p>
        <a href="index.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 transition">
            Retour à l’accueil
        </a>
    </main>
</body>

</html>