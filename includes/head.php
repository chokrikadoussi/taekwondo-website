<?php
// head.php : à inclure entre <head>…</head>, après avoir défini $title dans la page
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Taekwondo Club – <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Accueil' ?>
</title>
<meta name="description" content="Taekwondo Club St Priest – entraînements, loisir, compétitions, communauté">
<meta name="keywords"
    content="taekwondo, club taekwondo, art martial, St Priest, entraînement, compétition, tkd, self-défense">
<link href="css/styles.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/ad02441365.js" crossorigin="anonymous"></script>