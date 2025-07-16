<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 *
 * Présentation du fichier : Page de gestion des messages
 *
 * Informations techniques concernant la configuration du composant table.php :
 * - Headers     : Détermine le nom des en-têtes affiché dans la table
 * - Fields      : Détermine les champs techniques récupérer en base de données via fonctions get*
 * - Formatters  : Détermine les fonctions de formattage des données récupérées (Couleur spécifique, code html a retourné)
 * - Actions     : Détermine les actions à effectuer sur les données récupérées via des fonctions callback (Modifier, Supprimer)
 *
 * TODO:
 *
 */

// Gestion des POST
$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$id_tableau = "msg-table"; // Utilisé dans le HTML pour identifier la table des messages

// Traitement "Marquer comme lu"
if ($action === 'mark_read' && $id > 0) {
    try {
        setMessageLu($id);
        setFlash('success', 'Message marqué comme lu.');
    } catch (Exception $e) {
        logErreur("Partial includes/profile/messages.php ", $e->getMessage(), array('action' => $action, 'id' => $id, ));
        setFlash('error', 'Erreur lors du marquage du message comme lu.');
    }
}

// Traitement "Supprimer"
if ($action === 'destroy' && $id > 0) {
    try {
        supprimerMessage($id);
        setFlash('success', 'Message supprimé.');
        // $msgSupp = true; // Peut être activé si nécessaire, sinon retirer
    } catch (Exception $e) {
        logErreur("Partial includes/profile/messages.php ", $e->getMessage(), array('action' => $action, 'id' => $id, ));
        setFlash('error', 'Erreur lors de la suppression du message.');
    }
}

// Pagination et récupération de la liste des messages
$all = array(); // Initialiser à vide pour le cas d'erreur
try {
    $all = getListeMessages();
} catch (Exception $e) {
    logErreur("Partial includes/profile/messages.php ", $e->getMessage());
    setFlash('error', 'Impossible de charger la liste des messages. Veuillez réessayer plus tard.');
}

$baseUrl = "profile.php?page=" . $pageActuelle;
// chargement du tableau avec pagination
$pag = paginateArray($all, 'p', 5);
// on remplace les rows par le slice
$rows = $pag['slice'];
// et on récupère les infos de pagination
extract($pag); // pageNum, perPage, total, totalPages, offset, slice
$start = $pag['offset'] + 1;
$end = min($pag['offset'] + $perPage, $total);

// Configuration du tableau (headers, fields, formatters, actions)
$headers = array('ID', 'Nom', 'Email', 'Sujet', 'Reçu le', 'Statut', );
$fields = array('id', 'nom', 'email', 'sujet', 'date_sent', 'is_read', );
$formatters = array(
    'is_read' => fn($v) => $v
        ? '<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">Lu</span>'
        : '<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Nouveau</span>',
    'sujet' => fn($s) => empty($s) ? '<em class="text-slate-400">Aucun objet</em>' : htmlspecialchars($s, ENT_QUOTES),
);

// Actions : seule la suppression, la lecture se fait au clic sur la ligne
$actions = array(
    [
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'confirm' => 'Supprimer ce message ?',
        'params' => fn($r) => array('action' => 'destroy', 'id' => $r['id'], ),
    ],
);

// Gestion de la vue détaillée d'un message
$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$msg = null; // On initialise $msg à null
if ($viewId > 0) {
    try {
        $msg = getMessageParId($viewId);
        if ($msg) { // Si le message est trouvé
            if (!$msg['is_read']) {
                // Marquer le message comme lu si ce n'est pas déjà fait
                setMessageLu($viewId);
                $msg['is_read'] = 1; // Mettre à jour localement pour l'affichage immédiat
            }
        } else {
            // Message non trouvé pour l'ID spécifié
            setFlash('error', 'Message introuvable.');
        }
    } catch (Exception $e) {
        logErreur("Partial includes/profile/messages.php ", $e->getMessage(), array('viewId' => $viewId, ));
        setFlash('error', 'Erreur lors du chargement ou du marquage du message.');
        $msg = null; // S'assurer que $msg est null en cas d'erreur
    }
}
?>

<?php if ($viewId > 0) { ?>
    <?php if ($msg) { ?>
        <div class="space-y-6">
            <header>
                <a href="profile.php?page=messages"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-blue-600 mb-4">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la liste des messages
                </a>
                <h2 class="text-2xl font-bold text-slate-800">Message de <?= htmlspecialchars($msg['nom'], ENT_QUOTES) ?></h2>
                <p class="text-sm text-slate-500 mt-1">
                    <a href="mailto:<?= htmlspecialchars($msg['email'], ENT_QUOTES) ?>"
                        class="hover:underline"><?= htmlspecialchars($msg['email'], ENT_QUOTES) ?></a>
                    - Envoyé le <?= htmlspecialchars($msg['date_sent'], ENT_QUOTES) ?>
                </p>
            </header>

            <div class="bg-white rounded-lg shadow-md p-6 border border-slate-200">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Sujet</dt>
                        <dd class="mt-1 text-lg font-semibold text-slate-900">
                            <?= empty($msg['sujet']) ? '<em class="text-slate-400">Aucun objet</em>' : htmlspecialchars($msg['sujet'], ENT_QUOTES) ?>
                        </dd>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <dt class="text-sm font-medium text-slate-700">Message</dt>
                        <dd class="mt-2 text-base text-slate-800 leading-relaxed prose max-w-none">
                            <?= nl2br(htmlspecialchars($msg['contenu'], ENT_QUOTES)) ?>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="flex items-center gap-4">
                <form method="post" onsubmit="return confirm('Supprimer ce message ?');">
                    <input type="hidden" name="action" value="destroy">
                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash-alt"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    <?php } else { ?>
        <?php setFlash('error', 'Message introuvable.');
        displayFlash(); ?>
        <a href="profile.php?page=messages"
            class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-blue-600">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste des messages
        </a>
    <?php } ?>

<?php } else { ?>
    <?php displayFlash(); ?>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Gestion des messages</h2>
        <form method="get" class="flex items-center justify-end" onchange="this.submit()">
            <input type="hidden" name="page" value="messages">
            <label for="unread-toggle" class="flex items-center gap-3 cursor-pointer">
                <span class="text-sm font-medium text-slate-700">Afficher les non-lus uniquement</span>
                <div class="relative">
                    <input type="checkbox" id="unread-toggle" name="unread" value="1" <?= isset($_GET['unread']) ? 'checked' : '' ?> class="sr-only peer" />
                    <div class="w-11 h-6 bg-slate-200 peer-checked:bg-blue-600 rounded-full transition-colors duration-300">
                    </div>
                    <div
                        class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-full">
                    </div>
                </div>
            </label>
        </form>
    </div>


    <div class="bg-white rounded-lg shadow-md border border-slate-200 overflow-hidden">
        <?php
        // On prépare la variable $rowStyler pour le composant table.php
        $rowStyler = function ($r) {
            return !$r['is_read'] ? 'font-semibold bg-slate-50' : '';
        };
        include __DIR__ . '/../components/table.php';
        ?>
        <?php include __DIR__ . '/../components/pagination.php'; ?>
    </div>
<?php } ?>