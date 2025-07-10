<?php
// includes/profile/edit_user.php
// Accessible uniquement aux admins

$errors = [];
$success = false;
$userId = (int) ($_GET['id'] ?? 0);

if ($userId <= 0) {
    echo '<p class="text-red-600">Identifiant invalide.</p>';
    return;
}

// Récupérer les données actuelles
$pdo = connexionBaseDeDonnees();
$stmt = $pdo->prepare('SELECT id, email, prenom, nom, role FROM users WHERE id = ?');
$stmt->execute([$userId]);
$u = $stmt->fetch();

if (!$u) {
    echo '<p class="text-red-600">Utilisateur introuvable.</p>';
    return;
}

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage minimal
    $raw = [
        'email' => $_POST['email'] ?? '',
        'prenom' => $_POST['prenom'] ?? '',
        'nom' => $_POST['nom'] ?? '',
        'role' => $_POST['role'] ?? $u['role'],
        'motdepasse' => $_POST['motdepasse'] ?? '',
        'confirm' => $_POST['confirm'] ?? '',
    ];
    $data = nettoyerDonnees($raw);

    // Validation
    if ($data['email'] === '' || !estValideMail($data['email'])) {
        $errors[] = 'Email invalide.';
    }
    if ($data['prenom'] === '') {
        $errors[] = 'Le prénom est requis.';
    }
    if ($data['nom'] === '') {
        $errors[] = 'Le nom est requis.';
    }
    // Si on veut changer le mot de passe, on le valide
    if ($data['motdepasse'] !== '') {
        if (!estValideMotdepasse($data['motdepasse'])) {
            $errors[] = 'Le mot de passe ne respecte pas les contraintes.';
        }
        if ($data['motdepasse'] !== $data['confirm']) {
            $errors[] = 'La confirmation ne correspond pas.';
        }
    }

    // Mise à jour
    if (empty($errors)) {
        $fields = [
            'email' => $data['email'],
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'role' => $data['role'],
        ];
        // On n'ajoute mdp_securise que si l'admin a saisi quelque chose
        if ($data['motdepasse'] !== '') {
            $fields['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
        }

        if (modifierUtilisateur($userId, $fields)) {
            $success = true;
            // Recharger les valeurs affichées
            $u = array_merge($u, [
                'email' => $fields['email'],
                'prenom' => $fields['prenom'],
                'nom' => $fields['nom'],
                'role' => $fields['role'],
            ]);
        } else {
            $errors[] = 'Échec de la mise à jour.';
        }
    }
}
?>

<div class="space-y-6">
    <h2 class="text-xl font-semibold mb-4">Modifier l’utilisateur #<?= $userId ?></h2>

    <?php if ($success): ?>
        <div class="p-4 bg-green-100 text-green-800 rounded">Utilisateur mis à jour.</div>
    <?php elseif ($errors): ?>
        <div class="p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4 bg-white p-6 rounded shadow">
        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required
                value="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Prénom & Nom -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" name="prenom" id="prenom" required
                    value="<?= htmlspecialchars($u['prenom'], ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($u['nom'], ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Rôle -->
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Rôle</label>
            <select name="role" id="role" class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                <?php foreach (['membre', 'parent', 'admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>>
                        <?= ucfirst($r) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Mot de passe optionnel -->
        <div class="space-y-1">
            <p class="text-sm text-gray-600">
                Laissez vide pour conserver le mot de passe actuel.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="motdepasse" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                    <input type="password" name="motdepasse" id="motdepasse"
                        class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="confirm" class="block text-sm font-medium text-gray-700">Confirmer mot de passe</label>
                    <input type="password" name="confirm" id="confirm"
                        class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
            Enregistrer les modifications
        </button>
    </form>
</div>