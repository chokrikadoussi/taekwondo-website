<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Template de Heade permettant de lier les fichiers CSS et JS, les icones, les mots-clés et le titre de la page
 * 
 */
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Taekwondo Club – <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Accueil' ?>
</title>
<meta name="description" content="Taekwondo Club St Priest – entraînements, loisir, compétitions, communauté">
<meta name="keywords"
    content="taekwondo, club taekwondo, art martial, St Priest, entraînement, compétition, tkd, self-défense">
<link href="css/styles.css" rel="stylesheet">
<link rel="icon" href="img/icon-page" type="image/x-icon">
<script src="https://kit.fontawesome.com/ad02441365.js" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&family=Staatliches&display=swap"
    rel="stylesheet">