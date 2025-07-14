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

// 1) Charge tous les messages (filtrés si nécessaire)
$allRows = getAllMessages();

// 2) Paramètres de pagination
$perPage = 5;
$pageNum = max(1, (int) ($_GET['p'] ?? 1));
$total = count($allRows);
$totalPages = (int) ceil($total / $perPage);
$offset = ($pageNum - 1) * $perPage;
$start = $offset + 1;
$end = min($offset + $perPage, $total);

// 3) Découpe le tableau complet pour n’afficher que la page courante
$rows = array_slice($allRows, $offset, $perPage, true);

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
<?php } else { ?>

    <?php displayFlash(); ?>

    <h2 class="text-xl font-semibold mb-4">Gestion des messages</h2>

    <div class="overflow-x-auto">

        <form method="get" class="flex items-center space-x-4 mb-4">
            <input type="hidden" name="page" value="messages">
            <label class="inline-flex items-center">
                <input type="checkbox" name="unread" value="1" class="mr-2" <?= isset($_GET['unread']) ? 'checked' : '' ?>>
                Afficher seulement les non lus
            </label>
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                Filtrer
            </button>
        </form>

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
                        <?php foreach ($fields as $f): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 p-0">
                                <a href="profile.php?page=messages&view=<?= $r['id'] ?>" class="block w-full h-full px-6 py-4">
                                    <?= isset($formatters[$f])
                                        ? $formatters[$f]($r[$f])
                                        : htmlspecialchars($r[$f] ?? '', ENT_QUOTES) ?>
                                </a>
                            </td>
                        <?php endforeach; ?>
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

        <?php // 5) Affiche la barre de pagination
            if ($totalPages > 1): ?>

            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <!-- Mobile -->
                <div class="flex flex-1 justify-between sm:hidden">
                    <a href="?page=messages&p=<?= max(1, $pageNum - 1) ?>"
                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        <?= $pageNum === 1 ? 'aria-disabled="true"' : '' ?>>Previous</a>
                    <a href="?page=messages&p=<?= min($totalPages, $pageNum + 1) ?>"
                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        <?= $pageNum === $totalPages ? 'aria-disabled="true"' : '' ?>>Next</a>
                </div>

                <!-- Desktop -->
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium"><?= $start ?></span>
                            to
                            <span class="font-medium"><?= $end ?></span>
                            of
                            <span class="font-medium"><?= $total ?></span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                            <!-- Previous icon -->
                            <a href="?page=messages&p=<?= max(1, $pageNum - 1) ?>"
                                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                                aria-disabled="<?= $pageNum === 1 ? 'true' : 'false' ?>">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>

                            <!-- Pages -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php
                                // Affiche toutes si <7 pages, sinon affiche 1,2,...,last-1,last
                                if ($totalPages > 7 && $i > 2 && $i < $totalPages - 1) {
                                    if ($i === 3)
                                        echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset">…</span>';
                                    if ($i < 3 || $i > $totalPages - 2):
                                        ?>
                                        <a href="?page=messages&p=<?= $i ?>" aria-current="<?= $i === $pageNum ? 'page' : '' ?>"
                                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold 
                <?= $i === $pageNum
                            ? 'z-10 bg-blue-600 text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                            : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0' ?>"><?= $i ?></a>
                                        <?php
                                    endif;
                                    if ($i >= $totalPages - 1)
                                        echo ''; // continue loop to show last two
                                } else {
                                    ?>
                                    <a href="?page=messages&p=<?= $i ?>" aria-current="<?= $i === $pageNum ? 'page' : '' ?>"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold 
                <?= $i === $pageNum
                    ? 'z-10 bg-blue-600 text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                    : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0' ?>"><?= $i ?></a>
                                <?php }endfor; ?>

                            <!-- Next icon -->
                            <a href="?page=messages&p=<?= min($totalPages, $pageNum + 1) ?>"
                                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                                aria-disabled="<?= $pageNum === $totalPages ? 'true' : 'false' ?>">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

<?php } ?>

<script src="js/main.js"></script>