<?php
// includes/profile/users.php

$co = connexionBaseDeDonnees();

// 1) Paramètres de pagination
$perPage = 3;
$pageNum = max(1, (int) ($_GET['p'] ?? 1));
$offset = ($pageNum - 1) * $perPage;

// 2) Nombre total d’utilisateurs
$totalStmt = $co->query("SELECT COUNT(*) FROM users");
$totalUsers = (int) $totalStmt->fetchColumn();
$totalPages = (int) ceil($totalUsers / $perPage);

// 3) Requête paginée
$sql = "
    SELECT 
      u.id,
      CONCAT(u.prenom, ' ', u.nom) AS nom_complet,
      u.email,
      u.role,
      DATE_FORMAT(u.created_at, '%d-%m-%Y') AS date_creation,
      DATE_FORMAT(u.updated_at, '%d-%m-%Y') AS date_modification
    FROM users u
    ORDER BY u.id
    LIMIT :limit OFFSET :offset
";
$stmt = $co->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$listeUsers = $stmt->fetchAll();

// 4) Définition des classes Tailwind par rôle
$listeClassRole = [
    'admin' => 'bg-red-100 text-red-800',
    'membre' => 'bg-green-100 text-green-800',
];
?>

<div class="w-full overflow-x-auto">
    <table class="table-auto w-full divide-y divide-gray-200 bg-white shadow-sm rounded-lg">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Id</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de
                    création</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de
                    modification</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($listeUsers as $u):
                $couleurRole = $listeClassRole[$u['role']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['id']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['nom_complet']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $couleurRole ?>">
                            <?= htmlspecialchars(ucfirst($u['role'])) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= htmlspecialchars($u['date_creation']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= htmlspecialchars($u['date_modification']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                        <!-- Éditer -->
                        <a href="profile.php?page=edit_user&id=<?= $u['id'] ?>"
                            class="inline-flex items-center justify-center p-1.5 rounded-full text-sm text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500"
                            aria-label="Éditer">
                            <i class="fas fa-pencil-alt w-5 h-5"></i>
                        </a>
                        <!-- Supprimer -->
                        <form action="profile.php?page=delete_user" method="post" class="inline-block"
                            onsubmit="return confirm('Supprimer cet utilisateur ?');">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button type="submit"
                                class="inline-flex items-center justify-center p-1.5 rounded-full text-sm text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500"
                                aria-label="Supprimer">
                                <i class="fas fa-trash-alt w-5 h-5"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 5) Contrôles de pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="flex items-center space-x-4 mt-6">
        <!-- Previous -->
        <form action="" method="get">
            <input type="hidden" name="page" value="users">
            <input type="hidden" name="p" value="<?= max(1, $pageNum - 1) ?>">
            <button type="submit" <?= $pageNum === 1 ? 'disabled' : '' ?> class="inline-flex items-center justify-center rounded-md transition-colors
               focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500
               px-3 py-1 bg-transparent text-gray-600 hover:text-gray-800
               <?= $pageNum === 1 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                Précédent
            </button>
        </form>

        <!-- Numéros de page -->
        <div class="flex items-center space-x-1">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <form action="" method="get">
                    <input type="hidden" name="page" value="users">
                    <input type="hidden" name="p" value="<?= $i ?>">
                    <button type="submit" class="inline-flex items-center justify-center rounded-md transition-colors
                   focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500
                   w-8 h-8 p-0
                   <?= $i === $pageNum
                       ? 'bg-blue-600 text-white'
                       : 'bg-transparent text-gray-600 hover:text-gray-800' ?>"
                        aria-current="<?= $i === $pageNum ? 'page' : '' ?>">
                        <?= $i ?>
                    </button>
                </form>
            <?php endfor; ?>
        </div>

        <!-- Next -->
        <form action="" method="get">
            <input type="hidden" name="page" value="users">
            <input type="hidden" name="p" value="<?= min($totalPages, $pageNum + 1) ?>">
            <button type="submit" <?= $pageNum === $totalPages ? 'disabled' : '' ?> class="inline-flex items-center justify-center rounded-md transition-colors
               focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500
               px-3 py-1 bg-transparent text-gray-600 hover:text-gray-800
               <?= $pageNum === $totalPages ? 'opacity-50 cursor-not-allowed' : '' ?>">
                Suivant
            </button>
        </form>
    </nav>
<?php endif; ?>