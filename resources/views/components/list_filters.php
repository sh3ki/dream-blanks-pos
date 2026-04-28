<?php
$cfg = $filterConfig ?? [];
$targetTableId = (string) ($cfg['targetTableId'] ?? '');
$searchPlaceholder = (string) ($cfg['searchPlaceholder'] ?? 'Search...');
$searchColumns = $cfg['searchColumns'] ?? [];
$filterLabel = (string) ($cfg['filterLabel'] ?? 'Filter');
$filterColumn = (string) ($cfg['filterColumn'] ?? '');
$filterOptions = is_array($cfg['filterOptions'] ?? null) ? $cfg['filterOptions'] : [];
$dateColumn = (string) ($cfg['dateColumn'] ?? '');
$emptyMessage = (string) ($cfg['emptyMessage'] ?? 'No matching records found.');
$primaryAction = $cfg['primaryAction'] ?? null;
$secondaryActions = is_array($cfg['secondaryActions'] ?? null) ? $cfg['secondaryActions'] : [];
$enableClientPaging = (bool) ($cfg['enableClientPaging'] ?? true);
$showTableTools = (bool) ($cfg['showTableTools'] ?? true);

if (!is_array($searchColumns)) {
    $searchColumns = [];
}

$searchColumnsValue = implode(',', array_map(static fn ($col) => (string) $col, $searchColumns));
?>

<div
    class="card list-filter"
    data-list-filter
    data-target-table="<?= e($targetTableId) ?>"
    data-search-columns="<?= e($searchColumnsValue) ?>"
    data-filter-column="<?= e($filterColumn) ?>"
    data-date-column="<?= e($dateColumn) ?>"
    data-empty-message="<?= e($emptyMessage) ?>"
>
    <div class="table-toolbar">
        <div class="table-toolbar__filters">
            <div class="list-filter-grid">
                <div class="field list-filter-field-search">
                    <label>Search</label>
                    <input class="input" type="text" data-filter-search placeholder="<?= e($searchPlaceholder) ?>">
                </div>

                <div class="field list-filter-field-select">
                    <label><?= e($filterLabel) ?></label>
                    <select class="select" data-filter-select>
                        <option value="">All</option>
                        <?php foreach ($filterOptions as $option): ?>
                            <?php
                            if (is_array($option)) {
                                $value = (string) ($option['value'] ?? '');
                                $label = (string) ($option['label'] ?? $value);
                            } else {
                                $value = (string) $option;
                                $label = $value;
                            }
                            ?>
                            <?php if ($value !== ''): ?>
                                <option value="<?= e($value) ?>"><?= e($label) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field list-filter-field-date">
                    <label>From Date</label>
                    <input class="input" type="date" data-filter-from>
                </div>

                <div class="field list-filter-field-date">
                    <label>To Date</label>
                    <input class="input" type="date" data-filter-to>
                </div>

                <div class="field list-filter-field-action">
                    <label>&nbsp;</label>
                    <button class="btn btn-secondary w-full" type="button" data-filter-reset>Reset</button>
                </div>
            </div>
        </div>

        <div class="table-toolbar__actions">
            <?php if (is_array($primaryAction)): ?>
                <?php if (!empty($primaryAction['href'])): ?>
                    <a class="btn btn-primary" href="<?= e((string) $primaryAction['href']) ?>">
                        <?= e((string) ($primaryAction['label'] ?? 'Add')) ?>
                    </a>
                <?php elseif (!empty($primaryAction['modal'])): ?>
                    <button class="btn btn-primary" type="button" data-modal-open="<?= e((string) $primaryAction['modal']) ?>">
                        <?= e((string) ($primaryAction['label'] ?? 'Add')) ?>
                    </button>
                <?php endif; ?>
            <?php endif; ?>

            <?php foreach ($secondaryActions as $action): ?>
                <?php
                $label = (string) ($action['label'] ?? 'Action');
                $href = (string) ($action['href'] ?? '');
                $modal = (string) ($action['modal'] ?? '');
                $class = (string) ($action['class'] ?? 'btn btn-ghost');
                ?>
                <?php if ($href !== ''): ?>
                    <a class="<?= e($class) ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
                <?php elseif ($modal !== ''): ?>
                    <button class="<?= e($class) ?>" type="button" data-modal-open="<?= e($modal) ?>">
                        <?= e($label) ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($showTableTools): ?>
        <div class="table-toolbar">
            <div class="table-tools" data-table-controls data-target-table="<?= e($targetTableId) ?>">
                <?php if ($enableClientPaging): ?>
                    <label for="<?= e($targetTableId) ?>-page-size">Rows</label>
                    <select class="select" id="<?= e($targetTableId) ?>-page-size" data-table-page-size>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                <?php endif; ?>
                <button class="btn btn-ghost btn-sm" type="button" data-table-density>Density</button>
                <div class="action-menu" data-menu>
                    <button class="btn btn-ghost btn-sm" type="button" data-menu-toggle>Columns</button>
                    <div class="menu" data-menu-list data-table-columns-menu></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
