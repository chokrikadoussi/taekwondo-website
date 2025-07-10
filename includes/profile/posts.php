<?php
// public/includes/profile/posts.php

// 1) Charger les données
$pdo = connexionBaseDeDonnees();
$rows = $pdo
    ->query("
        SELECT 
          posts.id,
          posts.titre AS titre,
          CONCAT(users.prenom, ' ', users.nom) AS auteur,
          posts.contenu AS contenu,
          DATE_FORMAT(posts.created_at, '%d-%m-%Y') AS date_creation,
          DATE_FORMAT(posts.updated_at, '%d-%m-%Y') AS date_modification
        FROM posts
        LEFT JOIN users ON users.id = posts.auteur
        ORDER BY posts.created_at DESC
    ")
    ->fetchAll();

// 2) Définir les entêtes et champs
$headers = ['Id', 'Titre', 'Auteur', 'Contenu', 'Création', 'Modification'];
$fields = ['id', 'titre', 'auteur', 'contenu', 'date_creation', 'date_modification'];

// 3) Formatter la taille du contenu
$formatters = [
  // Formatter pour tronquer le contenu à 120 caractères
  'contenu' => function($val, $row) {
    $max = 120;
    $short = mb_strlen($val) > $max
      ? mb_substr($val, 0, $max) . '…'
      : $val;
    // on échappe le HTML
    return '<span title="' . htmlspecialchars($val, ENT_QUOTES) . '">' 
         . htmlspecialchars($short, ENT_QUOTES) 
         . '</span>';
  },
  // (On peut aussi formatter l'auteur, la date, etc. s’il le faut)
];

// 4) Actions Éditer / Supprimer
$actions = [
    [
        'url' => fn($r) => "profile.php?page=edit_post&id={$r['id']}",
        'icon' => 'pencil-alt',
        'label' => 'Modifier',
        'method' => 'get',
    ],
    [
        'url' => fn($r) => "profile.php?page=delete_post",
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'method' => 'post',
        'confirm' => 'Supprimer cet article ?',
        'params' => fn($r) => ['post_id' => $r['id']],
    ],
];
?>
<div class="space-y-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Gestion des actualités</h2>
        <a href="profile.php?page=create_post"
            class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded">
            <i class="fas fa-plus mr-2"></i>Nouvel article
        </a>
    </div>

    <?php include __DIR__ . '/../components/table.php'; ?>
</div>