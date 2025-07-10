<?php
// public/includes/profile/messages.php

// 1) Traitement POST (marquer lu / supprimer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['msg_id'])) {
    $id = (int) $_POST['msg_id'];
    if ($_POST['action'] === 'mark_read') {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")
            ->execute([$id]);
    }
    if ($_POST['action'] === 'delete_msg') {
        $pdo->prepare("DELETE FROM messages WHERE id = ?")
            ->execute([$id]);
    }
    header('Location: profile.php?page=messages');
    exit;
}

// 2) Comptage des non lus pour le badge
$unreadCount = (int) $pdo
    ->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")
    ->fetchColumn();

// 3) Chargement des messages pour le tableau
$rows = $pdo
    ->query("
      SELECT
        id,
        nom,
        email,
        LEFT(contenu, 80) AS extrait,
        is_read,
        DATE_FORMAT(created_at, '%d-%m-%Y %H:%i') AS date_envoi
      FROM messages
      ORDER BY created_at DESC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);

// 4) Définition pour table.php
$headers = ['De', 'E-mail', 'Message', 'Date', 'Statut'];
$fields = ['nom', 'email', 'extrait', 'date_envoi', 'is_read'];
$formatters = [
    'is_read' => function ($val) {
        return $val
            ? '<span class="text-green-600">Lu</span>'
            : '<span class="text-red-600">Nouveau</span>';
    },
    'extrait' => function ($val, $row) {
        // ajouter ellipse
        return htmlspecialchars($val, ENT_QUOTES) . '…';
    }
];
$actions = [
    [
        'url' => fn($r) => "profile.php?page=messages",
        'icon' => 'envelope-open',
        'label' => 'Marquer lu',
        'method' => 'post',
        'confirm' => null,
        'params' => fn($r) => ['action' => 'mark_read', 'msg_id' => $r['id']],
        // n'afficher que si non lu
        'show_if' => fn($r) => $r['is_read'] == 0,
    ],
    [
        'url' => fn($r) => "profile.php?page=messages",
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'method' => 'post',
        'confirm' => 'Supprimer ce message ?',
        'params' => fn($r) => ['action' => 'delete_msg', 'msg_id' => $r['id']],
    ],
];
?>
<div class="space-y-6">
    <!-- Toolbar -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Messages</h2>
        <?php if ($unreadCount > 0): ?>
            <span class="inline-block bg-red-600 text-white text-xs font-medium px-2 py-1 rounded-full">
                <?= $unreadCount ?> nouveau<?= $unreadCount > 1 ? 'x' : '' ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Tableau via composant -->
    <?php include __DIR__ . '/../components/table.php'; ?>
</div>