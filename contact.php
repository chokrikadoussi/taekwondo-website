<?php
// public/contact.php
session_start();
$pageTitle = 'Contact';
$pageActuelle = 'contact';
require __DIR__ . '/fonction/fonctions.php';

// 1) Initialisation
$errors = [];
$data = ['nom' => '', 'email' => '', 'sujet' => '', 'message' => ''];

// 2) POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2.1) Nettoyage
    $data = nettoyerDonnees([
        'nom' => $_POST['nom'] ?? '',
        'email' => $_POST['email'] ?? '',
        'sujet' => $_POST['sujet'] ?? '',
        'message' => $_POST['message'] ?? '',
    ]);

    // 2.2) Validation
    if ($data['nom'] === '') {
        $errors[] = 'Le nom est requis.';
    }
    if ($data['email'] === '' || !estValideMail($data['email'])) {
        $errors[] = 'Adresse e-mail invalide.';
    }
    if (trim($data['message']) === '') {
        $errors[] = 'Le message ne peut pas être vide.';
    }

    // 2.3) Insertion et redirection en cas de succès
    if (empty($errors)) {
        if (enregistrerMessage($data)) {
            setFlash('success', 'Merci ! Votre message a bien été envoyé.');
            header('Location: contact.php');
            exit;
        } else {
            $errors[] = 'Une erreur est survenue. Veuillez réessayer plus tard.';
        }
    }

    // On stocke les erreurs pour affichage après POST
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $data;
    header('Location: contact.php');
    exit;
}

// 3) Lecture des flashes / anciens inputs
$errors = $_SESSION['form_errors'] ?? [];
$data = $_SESSION['form_data'] ?? $data;
unset($_SESSION['form_errors'], $_SESSION['form_data']);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 max-w-lg">
        <h1 class="text-3xl font-semibold mb-6 text-center">Contactez-nous</h1>

        <?php displayFlash(); ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-800 rounded">
                <ul class="list-disc pl-5 space-y-1">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4 bg-white p-6 rounded shadow">
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom <span
                        class="text-red-800">*</span></label>
                <input type="text" name="nom" id="nom" required
                    value="<?= htmlspecialchars($data['nom'], ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-mail <span
                        class="text-red-800">*</span></label>
                <input type="email" name="email" id="email" required
                    value="<?= htmlspecialchars($data['email'], ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="sujet" class="block text-sm font-medium text-gray-700">Sujet</label>
                <input type="text" name="sujet" id="sujet" value="<?= htmlspecialchars($data['sujet'], ENT_QUOTES) ?>"
                    placeholder="Objet de votre message"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">Message <span
                        class="text-red-800">*</span></label>
                <textarea name="message" id="message" rows="5" required
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($data['message'], ENT_QUOTES) ?></textarea>
            </div>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
                Envoyer
            </button>
        </form>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>