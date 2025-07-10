<?php
// includes/profile/delete_user.php

// 1) Récupération de l’ID selon la méthode
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idToDelete = (int) ($_POST['user_id'] ?? 0);
} else {
    $idToDelete = (int) ($_GET['id'] ?? 0);
}

if ($idToDelete <= 0) {
    echo '<p class="text-red-600">Identifiant invalide.</p>';
    return;
}

// 2) Si POST, on supprime et on redirige
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    deleteUser($idToDelete);
    header('Location: profile.php?page=users');
    exit;
}

// 3) Sinon (GET), on affiche la confirmation
$pdo = connexionBaseDeDonnees();
$stmt = $pdo->prepare('SELECT id, prenom, nom, email, role FROM users WHERE id = ?');
$stmt->execute([$idToDelete]);
$user = $stmt->fetch();

if (!$user) {
    echo '<p class="text-red-600">Utilisateur introuvable.</p>';
    return;
}
?>

<div class="max-w-md mx-auto mt-12 bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">Confirmer la suppression</h2>
    <p>Vous êtes sur le point de supprimer :</p>
    <ul class="mt-4 list-disc pl-5 text-gray-700">
        <li><strong>ID :</strong> <?= htmlspecialchars($user['id'], ENT_QUOTES) ?></li>
        <li><strong>Nom :</strong> <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom'], ENT_QUOTES) ?></li>
        <li><strong>Email :</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES) ?></li>
        <li><strong>Rôle :</strong> <?= htmlspecialchars(ucfirst($user['role']), ENT_QUOTES) ?></li>
    </ul>

    <form method="post" class="mt-6 flex space-x-4">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <button type="submit"
            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition">Confirmer la
            suppression</button>
        <a href="profile.php?page=users&p=1"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded transition">Annuler</a>
    </form>
</div>