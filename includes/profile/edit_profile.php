<?php
// includes/profile/edit_profile.php

$errors = [];
$success = false;
$userId = $_SESSION['user']['id'];  // on sait qu’il est connecté

// Récupération initiale depuis session
$current = [
    'prenom' => $_SESSION['user']['prenom'],
    'nom' => $_SESSION['user']['nom'],
    'email' => $_SESSION['user']['email'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Nettoyage
    $raw = [
        'prenom' => $_POST['prenom'] ?? '',
        'nom' => $_POST['nom'] ?? '',
        'email' => $_POST['email'] ?? '',
        'motdepasse' => $_POST['motdepasse'] ?? '',
        'confirm_pass' => $_POST['confirm_pass'] ?? '',
        'current_pass' => $_POST['current_pass'] ?? '',
    ];
    $data = nettoyerDonnees($raw);

    // 2) Validation
    if ($data['prenom'] === '') {
        $errors[] = 'Le prénom est requis.';
    }
    if ($data['nom'] === '') {
        $errors[] = 'Le nom est requis.';
    }
    if ($data['email'] === '' || !estValideMail($data['email'])) {
        $errors[] = 'Email invalide.';
    } elseif ($data['email'] !== $current['email'] && utilisateurExiste($data['email'])) {
        $errors[] = 'Cet e-mail est déjà utilisé.';
    }

    // Si changement de mot de passe demandé
    if ($data['motdepasse'] !== '') {
        // 2a) vérifier le mot de passe courant pour plus de sécurité
        if (!authentification($current['email'], $data['current_pass'])) {
            $errors[] = 'Mot de passe actuel incorrect.';
        }
        if (!estValideMotdepasse($data['motdepasse'])) {
            $errors[] = 'Le nouveau mot de passe ne respecte pas les contraintes.';
        }
        if ($data['motdepasse'] !== $data['confirm_pass']) {
            $errors[] = 'La confirmation de mot de passe ne correspond pas.';
        }
    }

    // 3) Mise à jour
    if (empty($errors)) {
        $fields = [
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'email' => $data['email'],
        ];
        if ($data['motdepasse'] !== '') {
            $fields['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
        }

        if (modifierUtilisateur($userId, $fields)) {
            // Mise à jour session
            $_SESSION['user']['prenom'] = $data['prenom'];
            $_SESSION['user']['nom'] = $data['nom'];
            $_SESSION['user']['email'] = $data['email'];
            $success = true;
        } else {
            $errors[] = 'Échec de la mise à jour.';
        }
    }
}
?>

<div class="space-y-6">
    <h2 class="text-xl font-semibold mb-4">Modifier mon profil</h2>

    <?php if ($success): ?>
        <div class="p-4 bg-green-100 text-green-800 rounded">Profil mis à jour avec succès.</div>
    <?php elseif (!empty($errors)): ?>
        <div class="p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4 bg-white p-6 rounded shadow">
        <!-- Prénom & Nom -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" name="prenom" id="prenom" required
                    value="<?= htmlspecialchars($data['prenom'] ?? $current['prenom'], ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="nom" id="nom" required
                    value="<?= htmlspecialchars($data['nom'] ?? $current['nom'], ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required
                value="<?= htmlspecialchars($data['email'] ?? $current['email'], ENT_QUOTES) ?>"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Changement de mot de passe -->
        <fieldset class="space-y-2">
            <legend class="text-sm font-medium text-gray-700">Changer mon mot de passe (optionnel)</legend>

            <!-- Mot de passe actuel -->
            <div>
                <label for="current_pass" class="block text-sm text-gray-600">Mot de passe actuel</label>
                <input type="password" name="current_pass" id="current_pass"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Nouveau mot de passe & confirmation -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="motdepasse" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                    <input type="password" name="motdepasse" id="motdepasse"
                        class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="confirm_pass" class="block text-sm font-medium text-gray-700">Confirmer mot de
                        passe</label>
                    <input type="password" name="confirm_pass" id="confirm_pass"
                        class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </fieldset>

        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
            Enregistrer les modifications
        </button>
    </form>
</div>