<?php
// includes/profile/users.php
// 1) Récupération des données
$co = connexionBaseDeDonnees();
$sql = "
    SELECT 
      id,
      CONCAT(prenom, ' ', nom) AS nom_complet,
      email,
      role,
      DATE_FORMAT(created_at, '%d-%m-%Y') AS date_creation,
      DATE_FORMAT(updated_at, '%d-%m-%Y') AS date_modification
    FROM users
    ORDER BY id
";
$rows = $co->query($sql)->fetchAll();

// 2) Définition des en-têtes et des clés de champs
$headers = ['ID', 'Nom', 'Email', 'Rôle', 'Création', 'Modification'];
$fields = ['id', 'nom_complet', 'email', 'role', 'date_creation', 'date_modification'];

// 3) Formatter la colonne “role” en badge Tailwind
$formatters = [
    'role' => function ($val) {
        // parenthésage correct pour PHP 8+
        $classes = $val === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';

        return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium '
            . $classes
            . '">' . htmlspecialchars(ucfirst($val), ENT_QUOTES) . '</span>';
    }
];

// 4) Actions “Modifier” et “Supprimer”
$actions = [
    [
        'url' => fn($r) => "profile.php?page=edit_user&id={$r['id']}",
        'icon' => 'pencil-alt',
        'label' => 'Modifier',
        'method' => 'get',
    ],
    [
        'url' => fn($r) => "profile.php?page=delete_user",
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'method' => 'post',
        'confirm' => 'Supprimer cet utilisateur ?',
        'params' => fn($r) => ['user_id' => $r['id']],
    ],
];
?>

<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Gestion des utilisateurs</h2>
    <a href="profile.php?page=create_user"
        class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded transition">
        <i class="fas fa-user-plus mr-2"></i>Ajouter un utilisateur
    </a>
</div>

<?php
// 5) Inclusion du composant générique
include __DIR__ . '/../components/table.php';
