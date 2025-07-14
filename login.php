<?php
session_start();
$pageTitle = 'Connexion';
$pageActuelle = 'login';
require __DIR__ . '/fonction/fonctions.php';

$message_erreur = "";
$pageTitle = "Login";

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['motdepasse'] ?? '';

    // 1) Validation simple
    if ($email === '' || !estValideMail($email)) {
        $errors[] = 'Adresse e-mail invalide.';
    }
    if ($mdp === '') {
        $errors[] = 'Veuillez saisir un mot de passe.';
    }

    // 2) Authentification
    if (empty($errors) && !authentification($email, $mdp)) {
        $errors[] = 'Identifiants incorrects.';
    }

    // 3) Connexion réussie
    if (empty($errors)) {
        connexionUtilisateur($email);
        header("Location: profile.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>

</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-8 max-w-md">
        <?php if (!empty($message_erreur)) { ?>
            <div class="mb-6 p-4 bg-red-100 text-red-800 rounded">
                <?= htmlspecialchars($message_erreur, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>

        <form action="" method="post" class="space-y-4 bg-white p-6 rounded-lg shadow">
            <h1 class="text-2xl font-semibold mb-4 text-center">Connexion</h1>

            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-accent"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>

            <div>
                <label for="motdepasse" class="block text-sm font-medium mb-1">Mot de passe</label>
                <input type="password" name="motdepasse" id="motdepasse" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-accent">
            </div>

            <button type="submit" name="submit"
                class="w-full bg-blue-600 text-white py-2 rounded font-semibold hover:bg-blue-700 transition">
                Se connecter
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            Pas encore de compte ?
            <a href="register.php" class="text-accent hover:underline">Créez-en un ici</a>.
        </p>
    </main>


    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>

</html>