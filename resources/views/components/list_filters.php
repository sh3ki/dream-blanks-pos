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
