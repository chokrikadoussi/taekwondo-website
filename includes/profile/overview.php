<?php
// includes/profile/overview.php
// On suppose que $user contient :
// ['id','prenom','nom','email','role','created_at']
?>

<div class="space-y-6">
  <h2 class="text-2xl font-semibold">Bienvenue, <?= htmlspecialchars($user['prenom'], ENT_QUOTES) ?></h2>

  <ul class="bg-white shadow rounded-lg divide-y divide-gray-200">
    <li class="px-6 py-4 flex justify-between">
      <span class="font-medium text-gray-700">Prénom</span>
      <span class="text-gray-900"><?= htmlspecialchars($user['prenom'], ENT_QUOTES) ?></span>
    </li>
    <li class="px-6 py-4 flex justify-between">
      <span class="font-medium text-gray-700">Nom</span>
      <span class="text-gray-900"><?= htmlspecialchars($user['nom'], ENT_QUOTES) ?></span>
    </li>
    <li class="px-6 py-4 flex justify-between">
      <span class="font-medium text-gray-700">Email</span>
      <span class="text-gray-900"><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></span>
    </li>
    <li class="px-6 py-4 flex justify-between">
      <span class="font-medium text-gray-700">Rôle</span>
      <span class="text-gray-900"><?= htmlspecialchars(ucfirst($user['role']), ENT_QUOTES) ?></span>
    </li>
    <?php if (!empty($user['created_at'])): ?>
      <li class="px-6 py-4 flex justify-between">
        <span class="font-medium text-gray-700">Inscrit le</span>
        <span class="text-gray-900"><?= htmlspecialchars($user['created_at'], ENT_QUOTES) ?></span>
      </li>
    <?php endif; ?>
  </ul>

  <div class="pt-4">
    <a
      href="profile.php?page=edit_profile"
      class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition"
    >
      <i class="fas fa-user-edit mr-2"></i>Modifier mon profil
    </a>
  </div>
</div>
