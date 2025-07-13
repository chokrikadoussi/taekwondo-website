<?php
// Gestion des POST
$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

if ($action === 'mark_read' && $id > 0) {
    markMessageRead($id);
    setFlash('success', 'Message marqué comme lu.');
    redirectToProfile('messages');
}

if ($action === 'destroy' && $id > 0) {
    deleteMessage($id);
    setFlash('success', 'Message supprimé.');
    redirectToProfile('messages');
}

// Gestion de la vue détaillée
$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
if ($viewId > 0) {
    $msg = getMessageById($viewId);
    if (!$msg) {
        setFlash('error', 'Message introuvable.');
        redirectToProfile('messages');
    }
    // Si on clique pour lire, on le marque lu
    if (!$msg['is_read']) {
        markMessageRead($viewId);
        $msg['is_read'] = 1;
    }
    ?>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Message de <?= htmlspecialchars($msg['nom'], ENT_QUOTES) ?></h2>
        <p class="text-sm text-gray-600 mb-4">
            Envoyé le <?= htmlspecialchars($msg['date_sent'], ENT_QUOTES) ?> –
            <?= $msg['is_read'] ? 'Lu' : 'Nouveau' ?>
        </p>
        <h3 class="font-medium">Sujet : <?= htmlspecialchars($msg['sujet'] ?? '', ENT_QUOTES) ?></h3>
        <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-6 prose prose-sm max-w-full text-gray-800">
            <?= nl2br(htmlspecialchars($msg['contenu'], ENT_QUOTES)) ?>
        </div>
        <div class="mt-6 flex space-x-4">
            <form method="post">
                <input type="hidden" name="action" value="destroy">
                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
                    onclick="return confirm('Supprimer ce message ?')">
                    Supprimer
                </button>
            </form>
            <a href="profile.php?page=messages" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">
                ← Retour
            </a>
        </div>
    </div>
    <?php
    return;
}

// Chargement de tous les messages
$rows = getAllMessages();

// Configuration du tableau
$headers = ['ID', 'Nom', 'Email', 'Sujet', 'Reçu le', 'Statut'];
$fields = ['id', 'nom', 'email', 'sujet', 'date_sent', 'is_read'];
$formatters = [
    'is_read' => fn($v) =>
        $v
        ? '<span class="px-2 py-0.5 text-xs bg-gray-200 rounded">Lu</span>'
        : '<span class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded">Nouveau</span>',
];
// Actions : seule la suppression, la lecture se fait au clic sur la ligne
$actions = [
    [
        'icon' => 'trash-alt',
        'label' => 'Supprimer',
        'confirm' => 'Supprimer ce message ?',
        'params' => fn($r) => ['action' => 'destroy', 'id' => $r['id']],
    ],
];
?>

<?php displayFlash(); ?>

<h2 class="text-xl font-semibold mb-4">Gestion des messages</h2>

<div class="overflow-x-auto">
    <table id="msg-table" class="table-auto w-full divide-y divide-gray-200 bg-white shadow-sm rounded-lg">
        <thead class="bg-gray-50">
            <tr>
                <?php foreach ($headers as $h): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <?= htmlspecialchars($h, ENT_QUOTES) ?>
                    </th>
                <?php endforeach ?>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($rows as $r): ?>
                <tr data-id="<?= $r['id'] ?>" class="hover:bg-gray-50 cursor-pointer">
                    <?php foreach ($fields as $f):
                        $val = $r[$f];
                        if (isset($formatters[$f])) {
                            echo '<td class="px-6 py-4 whitespace-nowrap text-sm">'
                                . $formatters[$f]($val)
                                . '</td>';
                        } else {
                            echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">'
                                . htmlspecialchars($val ?? '', ENT_QUOTES)
                                . '</td>';
                        }
                    endforeach; ?>
                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                        <?php foreach ($actions as $act):
                            $params = $act['params']($r);
                            ?>
                            <form action="profile.php?page=messages" method="post" class="inline-block"
                                onsubmit="return confirm('<?= $act['confirm'] ?>');">
                                <?php foreach ($params as $n => $v): ?>
                                    <input type="hidden" name="<?= htmlspecialchars($n, ENT_QUOTES) ?>"
                                        value="<?= htmlspecialchars($v, ENT_QUOTES) ?>">
                                <?php endforeach ?>
                                <button type="submit" class="inline-flex items-center justify-center p-1.5 rounded-full text-gray-500 hover:text-gray-700
                         focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500"
                                    aria-label="<?= htmlspecialchars($act['label'], ENT_QUOTES) ?>">
                                    <i class="fas fa-<?= htmlspecialchars($act['icon'], ENT_QUOTES) ?> w-5 h-5"></i>
                                </button>
                            </form>
                        <?php endforeach ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<script src="/../../js/main.js"></script>