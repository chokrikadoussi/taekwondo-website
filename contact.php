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

    // Stockage des erreurs et données pour le PRG
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

<body class="min-h-screen flex flex-col bg-gray-50">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-center mb-8">Contactez-nous</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <!-- Visuel / info -->
            <div class="order-2 lg:order-1 grid grid-cols-1 lg:grid-cols-2 gap-6 h-full">
                <!-- Contact info -->
                <div class="space-y-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Vous avez une question&nbsp;?</h2>
                    <p class="text-gray-700">
                        Que ce soit pour en savoir plus sur nos cours, planifier une séance d’essai
                        ou toute autre demande, notre équipe est à votre écoute&nbsp;!
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start space-x-3">
                            <div class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <span class="block font-medium">Téléphone</span>
                                <a href="tel:0123456789" class="text-gray-700 hover:text-blue-600 transition">01 23 45
                                    67 89</a>
                            </div>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <span class="block font-medium">Email</span>
                                <a href="mailto:contact@club-taekwondo.fr"
                                    class="text-gray-700 hover:text-blue-600 transition">
                                    contact@taekwondo-pise.worldlite.fr
                                </a>
                            </div>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <span class="block font-medium">Adresse</span>
                                <span class="text-gray-700">10 Avenue Pierre Mendès-France, <br>69800 Saint-Priest</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Responsive Map -->
                <div class="overflow-hidden rounded-xl shadow-lg col-span-full lg:col-span-1">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2786.761718756447!2d4.9568376999999995!3d45.6957537!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47f4c471e150f7bb%3A0xf564ba03eba8074b!2sTaekwondo%20Club%20Saint-Priest!5e0!3m2!1sfr!2sfr!4v1752603460807!5m2!1sfr!2sfr"
                        class="w-full h-full aspect-video" allowfullscreen="true" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="order-1 lg:order-2 bg-white p-8 rounded-lg shadow-lg">
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

                <form method="post" class="space-y-6">
                    <!-- Nom & Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-700">
                                Nom <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="nom" id="nom" required
                                value="<?= htmlspecialchars($data['nom'], ENT_QUOTES) ?>"
                                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email <span class="text-red-600">*</span>
                            </label>
                            <input type="email" name="email" id="email" required
                                value="<?= htmlspecialchars($data['email'], ENT_QUOTES) ?>"
                                class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Sujet -->
                    <div>
                        <label for="sujet" class="block text-sm font-medium text-gray-700">Sujet</label>
                        <input type="text" name="sujet" id="sujet"
                            value="<?= htmlspecialchars($data['sujet'], ENT_QUOTES) ?>"
                            placeholder="Objet de votre message"
                            class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">
                            Message <span class="text-red-600">*</span>
                        </label>
                        <textarea name="message" id="message" rows="5" required
                            class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($data['message'], ENT_QUOTES) ?></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </main>


    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>