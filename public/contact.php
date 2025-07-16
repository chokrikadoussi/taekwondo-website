<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Page Contact permettant aux utilisateurs d'envoyer un message aux admins stocké en bdd
 * 
 * 
 */
session_start();
$pageTitle = 'Contact';
$pageActuelle = 'contact';
require __DIR__ . '/fonction/fonctions.php';

// Initialisation pour s'assurer qu'ils existent pour fournir des valeurs par défaut pour l'affichage initial du formulaire.
$errors = array();
$data = array('nom' => '', 'email' => '', 'sujet' => '', 'message' => '', );

// Gestion des actions POST lorsqu'un formulaire en envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage
    $data = nettoyerDonnees(array(
        'nom' => $_POST['nom'] ?? '',
        'email' => $_POST['email'] ?? '',
        'sujet' => $_POST['sujet'] ?? '',
        'message' => $_POST['message'] ?? '',
    ));

    // Validation via la fonction dédiée validerDonneesMessage()
    $errors = validerDonneesMessage($data);

    // 2.3) Insertion et redirection en cas de succès
    if (empty($errors)) {
        try {
            $enregistrer = enregistrerMessage($data);
            if ($enregistrer) {
                setFlash('success', 'Merci ! Votre message a bien été envoyé.');
                header('Location: contact.php');
                exit;
            } else {
                // Si enregistrerMessage retourne false (mais n'a pas levé d'exception)
                setFlash('error', "Échec de l'envoi du message. Veuillez réessayer.");
            }
        } catch (Exception $e) {
            // Capture les exceptions levées par enregistrerMessage (ex: erreur PDO)
            setFlash('error', "Une erreur est survenue lors de l'envoi. Veuillez réessayer plus tard.");
            // Enregistrement de l'erreur pour le débogage (déjà géré par logErreur dans fonctions.php)
        }
    }

    // Stockage des erreurs et données pour le PRG (Post/Redirect/Get)
    // Ceci s'exécute soit si la validation échoue, soit si l'enregistrement échoue après validation.
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $data;
    header('Location: contact.php');
    exit;
}

// Lecture des flashes / anciens inputs après redirection
// Ces variables sont utilisées pour pré-remplir le formulaire et afficher les erreurs de validation.
$errors = $_SESSION['form_errors'] ?? [];
$data = $_SESSION['form_data'] ?? $data; // Utilise les données de la session ou les valeurs par défaut
unset($_SESSION['form_errors'], $_SESSION['form_data']); // Nettoie la session après lecture

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-slate-50">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12">
        <h1 class="text-4xl font-extrabold text-slate-900 text-center mb-12">Contactez-nous</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="order-2 lg:order-1 space-y-8">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800">Vous avez une question ?</h2>
                    <p class="text-slate-600 mt-4 leading-relaxed">
                        Que ce soit pour en savoir plus sur nos cours, planifier une séance d’essai ou toute autre
                        demande, notre équipe est à votre écoute !
                    </p>
                </div>
                <ul class="space-y-6">
                    <li class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3 h-12 w-12 flex items-center justify-center">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <span class="block font-semibold text-slate-900">Téléphone</span>
                            <a href="tel:0123456789" class="text-slate-700 hover:text-blue-600 transition">01 23 45 67
                                89</a>
                        </div>
                    </li>
                    <li class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3 h-12 w-12 flex items-center justify-center">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <span class="block font-semibold text-slate-900">Email</span>
                            <a href="mailto:contact@taekwondo-pise.worldlite.fr"
                                class="text-slate-700 hover:text-blue-600 transition">contact@taekwondo-pise.worldlite.fr</a>
                        </div>
                    </li>
                    <li class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3 h-12 w-12 flex items-center justify-center">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <span class="block font-semibold text-slate-900">Adresse</span>
                            <span class="text-slate-700">10 Avenue Pierre Mendès-France, <br>69800 Saint-Priest</span>
                        </div>
                    </li>
                </ul>

                <div class="overflow-hidden rounded-xl shadow-lg">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2786.131364537319!2d4.947223376882887!3d45.71003451731952!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47f4c7b8c0d9597b%3A0x9188defc36975525!2s10%20Av.%20Pierre%20Mend%C3%A8s%20France%2C%2069800%20Saint-Priest!5e0!3m2!1sfr!2sfr!4v1721077582236!5m2!1sfr!2sfr"
                        class="w-full h-64 border-0" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            <div class="order-1 lg:order-2 bg-white p-8 rounded-lg shadow-lg flex flex-col">
                <?php displayFlash(); ?>
                <?php if (!empty($errors)) { ?>
                    <div class="mb-6 p-4 bg-red-100 text-red-800 rounded">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach ($errors as $e) { ?>
                                <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <form method="post" class="space-y-6 flex flex-col h-full">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="nom" class="block text-sm font-medium text-slate-700">Nom <span
                                    class="text-red-600">*</span></label>
                            <input type="text" name="nom" id="nom" required
                                value="<?= htmlspecialchars($data['nom'], ENT_QUOTES) ?>"
                                class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700">Email <span
                                    class="text-red-600">*</span></label>
                            <input type="email" name="email" id="email" required
                                value="<?= htmlspecialchars($data['email'], ENT_QUOTES) ?>"
                                class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="sujet" class="block text-sm font-medium text-slate-700">Sujet</label>
                        <input type="text" name="sujet" id="sujet"
                            value="<?= htmlspecialchars($data['sujet'], ENT_QUOTES) ?>"
                            placeholder="Objet de votre message"
                            class="mt-1 w-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex-grow flex flex-col">
                        <label for="message" class="block text-sm font-medium text-slate-700">
                            Message <span class="text-red-600">*</span>
                        </label>
                        <textarea name="message" id="message" required
                            class="mt-1 w-full h-full border border-slate-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($data['message'], ENT_QUOTES) ?></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md transition duration-200 shadow-sm">
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>