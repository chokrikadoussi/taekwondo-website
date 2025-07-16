<?php
// includes/components/table.php

// Variables attendues :
// $headers : tableau indexé de libellés (ex. ['Id','Nom','Email','Rôle',...])
// $rows    : tableau de tableaux associatifs (résultat fetchAll)
// $fields  : tableau indexé de clés de chaque colonne (ex. ['id','nom_complet','email','role',...])
// $formatters (optionnel) :
// $actions (optionnel) : 

?>

<div class="w-full overflow-x-auto">
    <table id="<?= isset($id_tableau) ? $id_tableau : '' ?>" class="table-auto w-full divide-y divide-slate-200 bg-white">
        <thead class="bg-slate-50">
            <tr>
                <?php foreach ($headers as $h): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                        <?= htmlspecialchars($h) ?>
                    </th>
                <?php endforeach; ?>
                <?php if (!empty($actions)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-200">
            <?php foreach ($rows as $row): ?>
                <tr data-id="<?= $row['id'] ?? 0 ?>"
                    class="transition-colors hover:bg-slate-50 cursor-pointer <?= isset($rowStyler) ? $rowStyler($row) : '' ?>">
                    <?php foreach ($fields as $field):
                        $val = $row[$field] ?? '';
                        if (isset($formatters[$field])) {
                            echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">' . $formatters[$field]($val, $row) . '</td>';
                        } else {
                            echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">' . htmlspecialchars($val) . '</td>';
                        }
                    endforeach; ?>

                    <?php if (!empty($actions)): ?>
                        <td class="px-6 py-4 whitespace-nowrap space-x-2">
                            <?php foreach ($actions as $act):
                                $icon = $act['icon'];
                                ?>
                                <form method="post" class="inline-block" <?= isset($act['confirm'])
                                    ? 'onsubmit="return confirm(\'' . htmlspecialchars($act['confirm'], ENT_QUOTES) . '\');"'
                                    : '' ?>">
                                    <?php if (!empty($act['params'])) {
                                        foreach ($act['params']($row) as $name => $value) {
                                            echo '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '">';
                                        }
                                    } ?>
                                    <button type="submit"
                                        class="inline-flex items-center justify-center p-1.5 rounded-full text-sm text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500"
                                        aria-label="<?= htmlspecialchars($act['label']) ?>">
                                        <i class="fas fa-<?= htmlspecialchars($icon) ?> w-5 h-5"></i>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>