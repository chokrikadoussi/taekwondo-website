<?php
// Variables attendues dans la page appelante :
//   $label    : libellé du module (ex. "utilisateurs")
//   $pageKey  : clé GET (ex. "users")
//   $showForm : bool

?>
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Gestion des <?= htmlspecialchars($label) ?></h2>

    <?php if (!$showForm): ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                <i class="fas fa-plus mr-2"></i>Ajouter
            </button>
        </form>
    <?php endif; ?>
</div>