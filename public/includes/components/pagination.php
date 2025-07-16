<?php
/**
 * @author Chokri Kadoussi
 * @author Anssoumane Sissokho
 * @date 2025-07-16
 * @version 1.0.0
 * 
 * Présentation du fichier : Template de pagination pour les partials inclus dans profile
 * 
 */
?>

<?php if ($totalPages > 1) { ?>
    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <!-- Mobile -->
        <div class="flex flex-1 justify-between sm:hidden">
            <a href="<?= $baseUrl ?>&p=<?= max(1, $pageNum - 1) ?>"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                <?= $pageNum === 1 ? 'aria-disabled="true"' : '' ?>>
                Précédent
            </a>
            <a href="<?= $baseUrl ?>&p=<?= min($totalPages, $pageNum + 1) ?>"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                <?= $pageNum === $totalPages ? 'aria-disabled="true"' : '' ?>>
                Suivant
            </a>
        </div>
        <!-- Desktop -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-sm text-gray-700">
                Affichage de <span class="font-medium"><?= $start ?></span> à
                <span class="font-medium"><?= $end ?></span> sur
                <span class="font-medium"><?= $total ?></span> résultats
            </p>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <!-- Previous icon -->
                <a href="<?= $baseUrl ?>&p=<?= max(1, $pageNum - 1) ?>"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                    aria-disabled="<?= $pageNum === 1 ? 'true' : 'false' ?>">
                    <span class="sr-only">Précédent</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <!-- Pages -->
                <?php for ($i = 1; $i <= $totalPages; $i++) {
                    if ($totalPages > 7 && $i > 2 && $i < $totalPages - 1) {
                        if ($i === 3) { ?>
                            <span
                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset">…</span>
                        <?php } ?>
                        <?php if ($i < 3 || $i > $totalPages - 2) { ?>
                            <a
                                href="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i === $pageNum
                                    ? 'z-10 bg-blue-600 text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                                    : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0' ?>">
                                <?= $i ?>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        <a href="<?= $baseUrl ?>&p=<?= $i ?>" class="inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i === $pageNum
                                ? 'z-10 bg-blue-600 text-white'
                                : 'text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php } ?>
                <?php } ?>
                <!-- Next icon -->
                <a href="<?= $baseUrl ?>&p=<?= min($totalPages, $pageNum + 1) ?>"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                    aria-disabled="<?= $pageNum === $totalPages ? 'true' : 'false' ?>">
                    <span class="sr-only">Suivant</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </nav>
        </div>
    </div>
<?php } ?>