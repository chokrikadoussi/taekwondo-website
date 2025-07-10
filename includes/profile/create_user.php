<?php
// includes/profile/create_user.php
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage
    $raw = [
        'email' => $_POST['email'] ?? '',
        'motdepasse' => $_POST['motdepasse'] ?? '',
        'confirm' => $_POST['confirm'] ?? '',
        'prenom' => $_POST['prenom'] ?? '',
        'nom' => $_POST['nom'] ?? '',
        'role' => $_POST['role'] ?? 'membre',
    ];
    $data = nettoyerDonnees($raw);

    // Validation
    if ($data['email'] === '' || !estValideMail($data['email'])) {
        $errors[] = 'Email invalide.';
    }
    if (utilisateurExiste($data['email'])) {
        $errors[] = 'Cet e-mail est déjà utilisé.';
    }
    if ($data['motdepasse'] === '' || !estValideMotdepasse($data['motdepasse'])) {
        $errors[] = 'Le mot de passe ne respecte pas les contraintes.';
    }
    if ($data['motdepasse'] !== $data['confirm']) {
        $errors[] = 'La confirmation ne correspond pas.';
    }
    if ($data['prenom'] === '') {
        $errors[] = 'Le prénom est requis.';
    }
    if ($data['nom'] === '') {
        $errors[] = 'Le nom est requis.';
    }

    // Enregistrement
    if (empty($errors)) {
        $data['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
        if (enregistrerUtilisateur($data)) {
            $success = true;
            header("Location: profile.php?page=users");
            exit;
        } else {
            $errors[] = 'Erreur lors de la création en base.';
        }
    }
}
?>

<div class="space-y-4">
    <h2 class="text-xl font-semibold mb-4">Créer un nouvel utilisateur</h2>

    <?php if ($success): ?>
        <div class="p-4 bg-green-100 text-green-800 rounded">Utilisateur créé avec succès.</div>
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
            <input type="email" name="email" id="email" required"
                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Prénom & Nom -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" name="prenom" id="prenom" required"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="nom" id="nom" required"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Mot de passe & confirmation -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="motdepasse" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input type="password" name="motdepasse" id="motdepasse" required
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="confirm" class="block text-sm font-medium text-gray-700">Confirmer mot de passe</label>
                <input type="password" name="confirm" id="confirm" required
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Rôle -->
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Rôle</label>
            <select name="role" id="role" class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                <option value="membre">Membre</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <!-- Submit -->
        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
            Créer l’utilisateur
        </button>
    </form>
</div>