<?php
session_start();
$pageTitle = 'Inscription';
$pageActuelle = 'register';
require __DIR__ . '/fonction/fonctions.php';

$pageTitle = "Register";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



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
    if (empty($data['email']) || !estValideMail($data['email'])) {
        $errors[] = 'Adresse e-mail invalide.';
    }
    if (empty($data['motdepasse']) || !estValideMotdepasse($data['motdepasse'])) {
        $errors[] = 'Le mot de passe ne respecte pas la politique.';
    }
    if ($data['motdepasse'] !== $raw['mdp_confirm']) {
        $errors[] = 'La confirmation du mot de passe ne correspond pas.';
    }
    if (empty($data['prenom'])) {
        $errors[] = 'Le prénom est requis.';
    }
    if (empty($data['nom'])) {
        $errors[] = 'Le nom est requis.';
    }
    if (isUtilisateur($data['email'])) {
        $errors[] = 'Cet e-mail est déjà utilisé.';
    }

    // 4) Enregistrement si OK
    if (empty($errors)) {
        $data['mdp_securise'] = password_hash($data['motdepasse'], PASSWORD_DEFAULT);
        if (enregistrerUtilisateur($data)) {
            connexionUtilisateur($data['email']);
            header("Location: profile.php");
            exit;
        }
        $errors[] = 'Erreur lors de l\'enregistrement.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-slate-50">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 flex items-center">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

            <div class="space-y-6">
                <h1 class="text-4xl font-extrabold text-slate-900">
                    Rejoignez notre communauté
                </h1>
                <p class="text-slate-600 leading-relaxed">
                    En créant un compte, vous accédez à un espace personnalisé pour gérer votre pratique, suivre vos
                    progrès et rester connecté avec la vie du club.
                </p>
                <ul class="space-y-5 pt-4">
                    <li class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3 flex items-center justify-center h-12 w-12">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                        <div>
                            <span class="block font-semibold text-slate-800">Accès au planning</span>
                            <span class="text-slate-600">Consultez les horaires et inscrivez-vous aux cours en quelques
                                clics.</span>
                        </div>
                    </li>
                    <li class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3 flex items-center justify-center h-12 w-12">
                            <i class="fas fa-user-cog fa-lg"></i>
                        </div>
                        <div>
                            <span class="block font-semibold text-slate-800">Gestion de profil</span>
                            <span class="text-slate-600">Mettez à jour vos informations personnelles et suivez votre
                                progression.</span>
                        </div>
                    </li>
                    <li class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3 flex items-center justify-center h-12 w-12">
                            <i class="fas fa-newspaper fa-lg"></i>
                        </div>
                        <div>
                            <span class="block font-semibold text-slate-800">Actualités du club</span>
                            <span class="text-slate-600">Ne manquez aucune annonce, événement ou compétition.</span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-center mb-6 text-slate-900">Formulaire d'inscription</h2>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert">
                        <ul class="list-disc list-inside text-sm">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="post" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="nom" class="block text-sm font-medium text-slate-700">Nom <span
                                    class="text-red-600">*</span></label>
                            <input type="text" name="nom" id="nom" required
                                value="<?= htmlspecialchars($raw['nom'] ?? '') ?>"
                                class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="prenom" class="block text-sm font-medium text-slate-700">Prénom <span
                                    class="text-red-600">*</span></label>
                            <input type="text" name="prenom" id="prenom" required
                                value="<?= htmlspecialchars($raw['prenom'] ?? '') ?>"
                                class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email <span
                                class="text-red-600">*</span></label>
                        <input type="email" name="email" id="email" required
                            value="<?= htmlspecialchars($raw['email'] ?? '') ?>"
                            class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="motdepasse" class="block text-sm font-medium text-slate-700">Mot de passe <span
                                class="text-red-600">*</span></label>
                        <input type="password" name="motdepasse" id="motdepasse" required
                            class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="mdp_confirm" class="block text-sm font-medium text-slate-700">Confirmez le mot de
                            passe <span class="text-red-600">*</span></label>
                        <input type="password" name="mdp_confirm" id="mdp_confirm" required
                            class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" name="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-md font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-sm text-base">
                        Créer mon compte
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-600">
                    Déjà un compte ?
                    <a href="login.php" class="font-medium text-blue-600 hover:underline">Connectez-vous ici</a>.
                </p>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>