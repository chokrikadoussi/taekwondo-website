<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier :    
 * 
 * TODO: remplir la description du fichier
 * 
 */
session_start();
$pageTitle = 'Connexion';
$pageActuelle = 'login';
require __DIR__ . '/fonction/fonctions.php';

// Initialisation des variables pour le formulaire et les erreurs
// Ces variables seront utilisées pour l'affichage initial du formulaire
// ou après une tentative de connexion (sur la même requête).
$errors = array();
$email = '';

// Gestion de la requête POST (soumission du formulaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['motdepasse'] ?? '';

    // Validation initiale des champs | inutile d'externaliser ce bloc dans une fonction
    if ($email === '' || !estValideMail($email)) {
        array_push($errors, 'Adresse e-mail invalide.');
    }
    if ($mdp === '') {
        array_push($errors, 'Veuillez saisir un mot de passe.');
    }

    // Si aucune erreur alors tenter l'authentification et la connexion
    if (empty($errors)) {
        try {
            // Tentative d'authentification
            if (!authentification($email, $mdp)) {
                array_push($errors, 'Identifiants incorrects.');
            } else {
                // Si l'authentification réussit, tenter de charger le profil utilisateur
                $user = connexionUtilisateur($email);
                if ($user) {
                    // Authentification et chargement du profil réussis : stocker en session et rediriger
                    $_SESSION['user'] = $user;
                    header("Location: profile.php");
                    exit;
                } else {
                    // Cas improbable : authentification OK mais profil introuvable
                    array_push($errors, 'Impossible de charger votre profil.');
                }
            }
        } catch (Exception $e) {
            // Gérer les erreurs de base de données ou autres exceptions imprévues
            logErreur("Page login.php ", $e->getMessage(), array('email' => $email, ));
            array_push($errors, 'Une erreur est survenue lors de la connexion. Veuillez réessayer plus tard.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-black">
    <canvas id="cursor-canvas"
        style="position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:-1;"></canvas>

    <?php include __DIR__ . '/includes/header.php'; ?>


    <main class="flex-grow flex items-center justify-center container mx-auto px-4 py-12">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-2xl">
            <h1 class="text-3xl font-extrabold mb-6 text-center text-slate-900">Se connecter</h1>

            <?php if (!empty($errors)) { ?>
                <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-md border border-red-200">
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        <?php foreach ($errors as $e) { ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

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
    <script src="js/particules.js" defer></script>
</body>

</html>