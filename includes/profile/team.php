<?php
// public/includes/profile/team.php

// 1) Charger les données
$pdo = connexionBaseDeDonnees();
$rows = $pdo
    ->query("
        SELECT
          id,
          CONCAT(prenom, ' ', nom) AS nom_complet,
          bio,
          DATE_FORMAT(created_at, '%d-%m-%Y') AS date_creation
        FROM team
        ORDER BY nom_complet
    ")
    ->fetchAll();

// 2) Définir les entêtes et champs
$headers = ['ID', 'Nom', 'Biographie', 'Création'];
$fields = ['id', 'nom_complet', 'bio', 'date_creation'];

// 3) Pas de formatters particuliers
$formatters = [];

// 4) Actions Éditer / Supprimer
$actions = [
    [
        'url' => fn($r) => "profile.php?page=edit_team&id={$r['id']}",
        'icon' => 'pencil-alt',
        'label' => 'Modifier',
        'method' => 'get',
    ],
    [
        'url' => fn($r) => "profile.php?page=delete_team",
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'method' => 'post',
        'confirm' => 'Supprimer cet entraîneur ?',
        'params' => fn($r) => ['team_id' => $r['id']],
    ],
];
?>
<div class="space-y-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Gestion des entraîneurs</h2>
        <a href="profile.php?page=create_team"
            class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded">
            <i class="fas fa-plus mr-2"></i>Ajouter un entraîneur
        </a>
    </div>

    <?php include __DIR__ . '/../components/table.php'; ?>
</div>