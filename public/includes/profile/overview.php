<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 *
 * Présentation du fichier : Page Mon profil
 *
 * Informations techniques concernant la configuration du composant table.php :
 * - Headers     : Détermine le nom des en-têtes affiché dans la table
 * - Fields      : Détermine les champs techniques récupérer en base de données via fonctions get*
 * - Formatters  : Détermine les fonctions de formattage des données récupérées (Couleur spécifique, code html a retourné)
 * - Actions     : Détermine les actions à effectuer sur les données récupérées via des fonctions callback (Modifier, Supprimer)
 *
 * TODO:
 *
 */
$action = $_POST['action'] ?? '';
$isEdit = ($_POST['action'] ?? '') === 'edit';

// Initialisation de $errors pour les cas d'affichage d'erreur
$errors = array();

// Si POST “save”, on traite la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
  $raw = array(
    'prenom' => $_POST['prenom'] ?? '',
    'nom' => $_POST['nom'] ?? '',
    'email' => $_POST['email'] ?? '',
    'motdepasse' => $_POST['motdepasse'] ?? '',
    'confirm' => $_POST['confirm'] ?? '',
    'current_pass' => $_POST['current_pass'] ?? '',
  );

  $data = nettoyerDonnees($raw);

  // Validation des données du profil via la fonction générique pour les utilisateurs
  // On passe l'ID de l'utilisateur actuel pour exclure son propre email lors de la vérification d'unicité
  $errors = validerDonneesUtilisateur($data, $_SESSION['user']['id']);

  // Si un nouveau mot de passe a été saisi
  if (!empty($data['motdepasse'])) {
    try {
      // Vérifier le mot de passe actuel
      if (!authentification($_SESSION['user']['email'], $data['current_pass'])) {
        array_push($errors, 'Mot de passe actuel incorrect.');
      }
    } catch (Exception $e) {
      logErreur("Partial incldues/profile/overview.php ", $e->getMessage(), array('id' => $_SESSION['user']['id'], 'action' => 'authentification_current_pass', ));
      array_push($errors, 'Erreur lors de la vérification du mot de passe actuel. Veuillez réessayer plus tard.');
    }
  }

  // Si aucune erreur après toutes les validations
  if (empty($errors)) {
    $fields_to_update = array(
      'prenom' => $data['prenom'],
      'nom' => $data['nom'],
      'email' => $data['email'],
    );

    // Ajouter le mot de passe haché si un nouveau mot de passe a été fourni et validé
    if (!empty($data['motdepasse'])) {
      $fields_to_update['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
    }

    try {
      // Mise à jour de la session de l'utilisateur avec les nouvelles données
      if (modifierUtilisateur($_SESSION['user']['id'], $fields_to_update)) {
        $_SESSION['user']['prenom'] = $data['prenom'];
        $_SESSION['user']['nom'] = $data['nom'];
        $_SESSION['user']['email'] = $data['email'];
        setFlash('success', 'Profil mis à jour avec succès.');
      } else {
        // modifierUtilisateur a retourné false, mais sans exception
        setFlash('error', 'Échec de la mise à jour, aucune modification effectuée. Réessayez.');
        $isEdit = true; // Reste en mode édition pour permettre de corriger
      }
    } catch (Exception $e) {
      logErreur("Partial incldues/profile/overview.php ", $e->getMessage(), array('user_id' => $_SESSION['user']['id'], 'fields' => $fields_to_update, ));
      setFlash('error', 'Une erreur est survenue lors de la mise à jour du profil. Veuillez réessayer plus tard.');
      $isEdit = true; // Reste en mode édition en cas d'erreur technique
    }
  } else {
    // Si des erreurs de validation sont présentes, les afficher via des flash messages
    foreach ($errors as $e) {
      setFlash('error', $e);
    }
    $isEdit = true; // Rester en mode édition pour permettre à l'utilisateur de corriger
  }
}

// Données courantes pour l'affichage du profil ou le pré-remplissage du formulaire
// Initialiser avec les données de session qui sont toujours à jour si la modification a réussi
// ou avec les données POST nettoyées si la validation a échoué.
$current = array(
  'prenom' => $data['prenom'] ?? $_SESSION['user']['prenom'],
  'nom' => $data['nom'] ?? $_SESSION['user']['nom'],
  'email' => $data['email'] ?? $_SESSION['user']['email'],
  'role' => $_SESSION['user']['role'],
  'date_creation' => $_SESSION['user']['date_creation'] ?? '',
);
?>

<div class="space-y-6">
  <?php displayFlash(); // Les messages flash sont conservés ?>

  <?php if ($isEdit) { ?>
    <h2 class="text-2xl font-bold text-slate-800">Modifier mon profil</h2>
    <div class="bg-white rounded-lg shadow-md p-6 border border-slate-200">
      <form method="post" class="space-y-6">
        <input type="hidden" name="action" value="save">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="prenom" class="block text-sm font-medium text-slate-700">Prénom</label>
            <input type="text" name="prenom" id="prenom" required
              value="<?= htmlspecialchars($current['prenom'], ENT_QUOTES) ?>"
              class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label for="nom" class="block text-sm font-medium text-slate-700">Nom</label>
            <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($current['nom'], ENT_QUOTES) ?>"
              class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
          <input type="email" name="email" id="email" required
            value="<?= htmlspecialchars($current['email'], ENT_QUOTES) ?>"
            class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <fieldset class="space-y-4 border-t border-slate-200 pt-6">
          <legend class="text-base font-semibold text-slate-800">Changer mon mot de passe</legend>
          <p class="text-sm text-slate-500 -mt-3">Laissez les champs vides si vous ne souhaitez pas le modifier.</p>

          <div>
            <label for="current_pass" class="block text-sm font-medium text-slate-700">Mot de passe actuel</label>
            <input type="password" name="current_pass" id="current_pass"
              class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="motdepasse" class="block text-sm font-medium text-slate-700">Nouveau mot de passe</label>
              <input type="password" name="motdepasse" id="motdepasse"
                class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label for="confirm" class="block text-sm font-medium text-slate-700">Confirmer</label>
              <input type="password" name="confirm" id="confirm"
                class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>
        </fieldset>

        <div class="flex flex-col-reverse sm:flex-row gap-4 pt-4">
          <a href="profile.php?page=overview"
            class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-slate-200 text-slate-800 rounded-lg hover:bg-slate-300 transition">
            Annuler
          </a>
          <button type="submit"
            class="w-full sm:flex-1 text-center px-5 py-2.5 font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Enregistrer les modifications
          </button>
        </div>
      </form>
    </div>

  <?php } else { ?>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
      <h2 class="text-2xl font-bold text-slate-800">Mon profil</h2>
      <form method="post">
        <button type="submit" name="action" value="edit"
          class="w-full sm:w-auto inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
          <i class="fas fa-pencil-alt mr-2"></i>Modifier
        </button>
      </form>
    </div>

    <div class="bg-white rounded-lg shadow-md border border-slate-200">
      <dl class="divide-y divide-slate-200">
        <div class="px-6 py-4 grid grid-cols-3 gap-4">
          <dt class="text-sm font-medium text-slate-500">Prénom</dt>
          <dd class="text-base text-slate-900 col-span-2"><?= htmlspecialchars($current['prenom'], ENT_QUOTES) ?></dd>
        </div>
        <div class="px-6 py-4 grid grid-cols-3 gap-4">
          <dt class="text-sm font-medium text-slate-500">Nom</dt>
          <dd class="text-base text-slate-900 col-span-2"><?= htmlspecialchars($current['nom'], ENT_QUOTES) ?></dd>
        </div>
        <div class="px-6 py-4 grid grid-cols-3 gap-4">
          <dt class="text-sm font-medium text-slate-500">Email</dt>
          <dd class="text-base text-slate-900 col-span-2"><?= htmlspecialchars($current['email'], ENT_QUOTES) ?></dd>
        </div>
        <div class="px-6 py-4 grid grid-cols-3 gap-4">
          <dt class="text-sm font-medium text-slate-500">Rôle</dt>
          <dd class="text-base text-slate-900 col-span-2">
            <?= htmlspecialchars(ucfirst($current['role']), ENT_QUOTES) ?>
          </dd>
        </div>
        <div class="px-6 py-4 grid grid-cols-3 gap-4">
          <dt class="text-sm font-medium text-slate-500">Membre depuis le</dt>
          <dd class="text-base text-slate-900 col-span-2"><?= htmlspecialchars($current['date_creation'], ENT_QUOTES) ?>
          </dd>
        </div>

      </dl>
    </div>

  <?php } ?>
</div>