<?php
session_start();
$pageTitle = 'Inscription';
$pageActuelle = 'register';
require __DIR__ . '/fonction/fonctions.php';

$pageTitle = "Register";
$errors = [];

// 1) Récupération brute
$raw = [
    'email' => $_POST['email'] ?? '',
    'motdepasse' => $_POST['motdepasse'] ?? '',
    'mdp_confirm' => $_POST['mdp_confirm'] ?? '',
    'prenom' => $_POST['prenom'] ?? '',
    'nom' => $_POST['nom'] ?? '',
    'role' => $_POST['role'] ?? 'membre',
];

// 2) Nettoyage des données
$data = nettoyerDonnees($raw);

// 3) Validation
if ($data['email'] === '' || !estValideMail($data['email'])) {
    $errors[] = 'Adresse e-mail invalide.';
}
if ($data['motdepasse'] === '' || !estValideMotdepasse($data['motdepasse'])) {
    $errors[] = 'Le mot de passe ne respecte pas la politique.';
}
if ($data['motdepasse'] !== $raw['mdp_confirm']) {
    $errors[] = 'La confirmation du mot de passe ne correspond pas.';
}
if ($data['prenom'] === '') {
    $errors[] = 'Le prénom est requis.';
}
if ($data['nom'] === '') {
    $errors[] = 'Le nom est requis.';
}
if (utilisateurExiste($data['email'])) {
    $errors[] = 'Cet e-mail est déjà utilisé.';
}

// 4) Enregistrement si OK
if (empty($errors)) {
    $data['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
    if (enregistrerUtilisateur($data)) {
        header('Location: login.php');
        exit;
    }
    $errors[] = 'Erreur lors de l\'enregistrement.';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>

</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <br>
    <main class="flex-grow container mx-auto px-4 py-8 max-w-md">
        <h1 class="text-2xl font-semibold mb-4 text-center">Inscription</h1>
        <form action="" method="post" class="space-y-6 bg-white p-6 rounded-lg shadow">
            <!-- Nom -->
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <input type="text" name="nom" id="nom" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Prénom -->
            <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                <input type="text" name="prenom" id="prenom" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Mot de passe -->
            <div>
                <label for="motdepasse" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <input type="password" name="motdepasse" id="motdepasse" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Confirmation mot de passe -->
            <div>
                <label for="mdp_confirm" class="block text-sm font-medium text-gray-700 mb-1">
                    Resaisissez votre mot de passe
                </label>
                <input type="password" name="mdp_confirm" id="mdp_confirm" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Rôle -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Vous êtes</label>
                <select name="role" id="role"
                    class="w-full px-3 py-2 border border-gray-300 bg-white rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="membre">Adhérent</option>
                    <option value="parent">Parent d'adhérent</option>
                </select>
            </div>

            <!-- Bouton -->
            <button type="submit" name="submit"
                class="w-full bg-blue-600 text-white py-2 rounded font-semibold hover:bg-blue-700 transition">
                Créer mon compte
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Déjà un compte ?
            <a href="login.php" class="text-blue-600 hover:underline">Connectez-vous ici</a>.
        </p>

        <?php include __DIR__ . '/includes/footer.php'; ?>
    </main>

</body>

</html>