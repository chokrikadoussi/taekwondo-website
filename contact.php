<?php
session_start();
$pageTitle = 'Contact';
$pageActuelle = 'contact';

require __DIR__ . '/fonction/fonctions.php';
$pdo = connexionBaseDeDonnees();

// Initialisation
$errors = [];
$success = false;

// 1) Traitement du POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage minimal
    $raw = [
        'nom' => $_POST['nom'] ?? '',
        'email' => $_POST['email'] ?? '',
        'message' => $_POST['message'] ?? '',
    ];
    $data = nettoyerDonnees($raw);

    // Validation
    if ($data['nom'] === '') {
        $errors[] = 'Le nom est requis.';
    }
    if ($data['email'] === '' || !estValideMail($data['email'])) {
        $errors[] = 'E-mail invalide.';
    }
    if ($data['message'] === '') {
        $errors[] = 'Le message ne peut pas être vide.';
    }

    // Insertion si OK
    if (empty($errors)) {
        $stmt = $pdo->prepare("
          INSERT INTO messages (nom, email, contenu)
          VALUES (:nom, :email, :contenu)
        ");
        if (
            $stmt->execute([
                'nom' => $data['nom'],
                'email' => $data['email'],
                'contenu' => $data['message'],
            ])
        ) {
            $success = true;
        } else {
            $errors[] = 'Une erreur est survenue, veuillez réessayer.';
        }
    }
}
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

        <?php if ($success): ?>
            <div class="p-4 bg-green-100 text-green-800 rounded mb-6">
                Merci ! Votre message a bien été envoyé.
            </div>
        <?php elseif (!empty($errors)): ?>
            <div class="p-4 bg-red-100 text-red-800 rounded mb-6">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4 bg-white p-6 rounded shadow">
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" name="nom" id="nom" required
                    value="<?= htmlspecialchars($data['nom'] ?? '', ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                <input type="email" name="email" id="email" required
                    value="<?= htmlspecialchars($data['email'] ?? '', ENT_QUOTES) ?>"
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" id="message" rows="5" required
                    class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($data['message'] ?? '', ENT_QUOTES) ?></textarea>
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