<?php
// public/includes/profile/classes.php

// 1) Charger les données
$pdo = connexionBaseDeDonnees();
$rows = $pdo
    ->query("
        SELECT
          classes.id AS id,
          classes.nom AS nom,
          classes.description AS description,
          CONCAT(team.prenom, ' ', team.nom) AS team,
          classes.price as prix,
          DATE_FORMAT(classes.date_creation, '%d-%m-%Y') AS date_creation
        FROM classes
        LEFT JOIN team ON team.id = classes.team_id
        ORDER BY nom
    ")
    ->fetchAll();

// 2) Définir les entêtes et champs
$headers = ['ID', 'Nom du cours', 'Description', 'Entraineur', 'Prix annuel', 'Création'];
$fields = ['id', 'nom', 'description', 'team', 'prix', 'date_creation'];

// 3) Pas de formatters particuliers
$formatters = [];

// 4) Actions Éditer / Supprimer
$actions = [
    [
        'url' => fn($r) => "profile.php?page=edit_class&id={$r['id']}",
        'icon' => 'pencil-alt',
        'label' => 'Modifier',
        'method' => 'get',
    ],
    [
        'url' => fn($r) => "profile.php?page=delete_class",
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'method' => 'post',
        'confirm' => 'Supprimer ce cours ?',
        'params' => fn($r) => ['class_id' => $r['id']],
    ],
];
?>
<div class="space-y-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Gestion des cours</h2>
        <a href="profile.php?page=create_class"
            class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded">
            <i class="fas fa-plus mr-2"></i>Ajouter un cours
        </a>
    </div>

    <?php include __DIR__ . '/../components/table.php'; ?>
</div>