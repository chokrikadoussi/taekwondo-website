<?php
session_start();
$pageTitle = 'Connexion';
$pageActuelle = 'login';
require __DIR__ . '/fonction/fonctions.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['motdepasse'] ?? '';

    if ($email === '' || !estValideMail($email)) {
        $errors[] = 'Adresse e-mail invalide.';
    }
    if ($mdp === '') {
        $errors[] = 'Veuillez saisir un mot de passe.';
    }

    if (empty($errors) && !authentification($email, $mdp)) {
        $errors[] = 'Identifiants incorrects.';
    }

    if (empty($errors)) {
        // Récupère les infos de l’utilisateur
        $user = connexionUtilisateur($email);
        if ($user) {
            // On stocke l’utilisateur en session
            $_SESSION['user'] = $user;
            header("Location: profile.php");
            exit;
        } else {
            // Cas improbable si auth a réussi
            $errors[] = 'Impossible de charger votre profil.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <style>
        /* style du point brillant avec box-shadow continu */
        .cursor-trail {
            position: absolute;
            pointer-events: none;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(75, 132, 245, 0.9);
            /* couleur de base */
            box-shadow:
                0 0 15px 15px rgba(75, 132, 245, 0.9);
            /* petite lueur interne */
            transform: translate(-50%, -50%) scale(1);
            animation: trail-fade 0.6s ease-out forwards;
        }

        @keyframes trail-fade {
            to {
                transform: translate(-50%, -50%) scale(3);
                opacity: 0;
            }
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-black">
    <canvas id="cursor-canvas"
        style="position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:-1;"></canvas>

    <?php include __DIR__ . '/includes/header.php'; ?>


    <main class="flex-grow flex items-center justify-center container mx-auto px-4 py-12">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-2xl">
            <h1 class="text-3xl font-extrabold mb-6 text-center text-slate-900">Se connecter</h1>

            <?php if (!empty($errors)): ?>
                <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-md border border-red-200">
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Adresse e-mail</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" required
                            value="<?= htmlspecialchars($email, ENT_QUOTES) ?>"
                            class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                            placeholder="vous@exemple.com">
                    </div>
                </div>

                <div>
                    <label for="motdepasse" class="block text-sm font-medium text-slate-700 mb-1">Mot de passe</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="motdepasse" id="motdepasse" required
                            class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md transition duration-200 shadow-sm cursor-pointer">
                    Se connecter
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Pas encore de compte ?
                <a href="register.php" class="font-medium text-blue-600 hover:underline">Créez-en un</a>.
            </p>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="js/main.js" defer></script>
</body>

</html>