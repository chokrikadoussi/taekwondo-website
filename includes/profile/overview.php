<?php

// 1) Détection du POST “edit” ou “save”
$action = $_POST['action'] ?? '';
$isEdit = $action === 'edit';

// 2) Si POST “save”, on traite la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
  $raw = [
    'prenom' => $_POST['prenom'] ?? '',
    'nom' => $_POST['nom'] ?? '',
    'email' => $_POST['email'] ?? '',
    'motdepasse' => $_POST['motdepasse'] ?? '',
    'confirm_pass' => $_POST['confirm_pass'] ?? '',
    'current_pass' => $_POST['current_pass'] ?? '',
  ];
  $data = nettoyerDonnees($raw);
  $errors = [];

  // --- validations identiques à avant ---
  if ($data['prenom'] === '')
    $errors[] = 'Le prénom est requis.';
  if ($data['nom'] === '')
    $errors[] = 'Le nom est requis.';
  if ($data['email'] === '' || !estValideMail($data['email'])) {
    $errors[] = 'Email invalide.';
  } elseif (
    $data['email'] !== $_SESSION['user']['email']
    && isUtilisateur($data['email'])
  ) {
    $errors[] = 'Cet email est déjà utilisé.';
  }
  if ($data['motdepasse'] !== '') {
    if (!authentification($_SESSION['user']['email'], $data['current_pass'])) {
      $errors[] = 'Mot de passe actuel incorrect.';
    }
    if (!estValideMotdepasse($data['motdepasse'])) {
      $errors[] = 'Le nouveau mot de passe ne respecte pas les contraintes.';
    }
    if ($data['motdepasse'] !== $data['confirm_pass']) {
      $errors[] = 'La confirmation du mot de passe ne correspond pas.';
    }
  }

  if (empty($errors)) {
    $fields = [
      'prenom' => $data['prenom'],
      'nom' => $data['nom'],
      'email' => $data['email'],
    ];
    if ($data['motdepasse'] !== '') {
      $fields['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
    }
    if (modifierUtilisateur($_SESSION['user']['id'], $fields)) {
      // maj session + flash + reload
      $_SESSION['user']['prenom'] = $data['prenom'];
      $_SESSION['user']['nom'] = $data['nom'];
      $_SESSION['user']['email'] = $data['email'];
      setFlash('success', 'Profil mis à jour avec succès.');
    } else {
      setFlash('error', 'Échec de la mise à jour, réessayez.');
      $isEdit = true;
    }
  } else {
    foreach ($errors as $e) {
      setFlash('error', $e);
    }
    $isEdit = true;
  }
}

// 3) Données courantes
$current = [
  'prenom' => $_SESSION['user']['prenom'],
  'nom' => $_SESSION['user']['nom'],
  'email' => $_SESSION['user']['email'],
];
?>

<div class="space-y-6">
  <?php displayFlash(); ?>

  <?php if ($isEdit): ?>
    <!-- FORMULAIRE D’ÉDITION -->
    <div class="bg-white rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-4">Modifier mon profil</h2>
      <form method="post" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
            <input type="text" name="prenom" id="prenom" required
              value="<?= htmlspecialchars($current['prenom'], ENT_QUOTES) ?>"
              class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
            <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($current['nom'], ENT_QUOTES) ?>"
              class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" name="email" id="email" required
            value="<?= htmlspecialchars($current['email'], ENT_QUOTES) ?>"
            class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
        </div>

        <fieldset class="space-y-2">
          <legend class="text-sm font-medium text-gray-700">Changer mon mot de passe (optionnel)</legend>
          <div>
            <label for="current_pass" class="block text-sm text-gray-600">Mot de passe actuel</label>
            <input type="password" name="current_pass" id="current_pass"
              class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="motdepasse" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
              <input type="password" name="motdepasse" id="motdepasse"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label for="confirm_pass" class="block text-sm font-medium text-gray-700">Confirmer</label>
              <input type="password" name="confirm_pass" id="confirm_pass"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
          </div>
        </fieldset>

        <div class="flex items-center space-x-4 pt-4">
          <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition sm:w-full">
            Enregistrer
          </button>
          <a href="profile.php?page=overview" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300 sm:w-full text-center">Annuler</a>
        </div>
      </form>
    </div>

  <?php else: ?>

    <!-- AFFICHAGE DU PROFIL (carte) -->
    <div class="flex items-center justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Mon profil</h2>

      <form method="post">
        <button type="submit" name="action" value="edit"
          class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
          <i class="fas fa-user-edit mr-2"></i>Modifier
        </button>
      </form>
    </div>

    <div class="bg-white rounded-lg overflow-hidden">
      <div class="px-6 py-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col">
          <span class="text-sm font-medium text-gray-500">Prénom</span>
          <span
            class="mt-1 text-lg font-semibold text-gray-800"><?= htmlspecialchars($current['prenom'], ENT_QUOTES) ?></span>
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-medium text-gray-500">Nom</span>
          <span
            class="mt-1 text-lg font-semibold text-gray-800"><?= htmlspecialchars($current['nom'], ENT_QUOTES) ?></span>
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-medium text-gray-500">Email</span>
          <span
            class="mt-1 text-lg font-semibold text-gray-800"><?= htmlspecialchars($current['email'], ENT_QUOTES) ?></span>
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-medium text-gray-500">Rôle</span>
          <span
            class="mt-1 text-lg font-semibold text-gray-800"><?= htmlspecialchars(ucfirst($_SESSION['user']['role']), ENT_QUOTES) ?></span>
        </div>
        <?php if (!empty($_SESSION['user']['created_at'])): ?>
          <div class="flex flex-col sm:col-span-2">
            <span class="text-sm font-medium text-gray-500">Inscrit depuis le</span>
            <span
              class="mt-1 text-lg font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['user']['created_at'], ENT_QUOTES) ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php endif; ?>
</div>