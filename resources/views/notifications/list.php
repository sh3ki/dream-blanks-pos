<?php
$filterConfig = [
    'targetTableId' => 'notifications-table',
    'searchPlaceholder' => 'Search notifications by type, title, or message...',
    'searchColumns' => [0, 1, 2],
    'filterLabel' => 'Status',
    'filterColumn' => 3,
    'filterOptions' => [
        ['value' => 'Unread', 'label' => 'Unread'],
        ['value' => 'Read', 'label' => 'Read'],
    ],
    'dateColumn' => 4,
    'emptyMessage' => 'No notifications match your filters.',
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Notifications</h2>
        <p class="page-subtitle">Stay on top of alerts, approvals, and system updates.</p>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<div class="card">
    <h3 class="card-title">Notification Center</h3>
    <div class="table-wrap">
        <table class="table" id="notifications-table" data-table>
            <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>Message</th>
                <th>Status</th>
                <th>Created At</th>
                <th data-no-sort>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($notifications as $notification): ?>
                <tr>
                    <td><?= e($notification['notification_type']) ?></td>
                    <td><?= e($notification['title']) ?></td>
                    <td><?= e($notification['message']) ?></td>
                    <td>
                        <span class="status <?= (int) $notification['is_read'] === 1 ? 'status-ok' : 'status-info' ?>">
                            <?= (int) $notification['is_read'] === 1 ? 'Read' : 'Unread' ?>
                        </span>
                    </td>
                    <td><?= e($notification['created_at']) ?></td>
                    <td>
                        <?php if ((int) $notification['is_read'] === 0): ?>
                            <div class="action-menu" data-menu>
                                <button class="btn btn-ghost btn-sm" type="button" data-menu-toggle>Actions</button>
                                <div class="menu" data-menu-list>
                                    <form method="post" action="<?= e(url('/notifications/' . (int) $notification['id'] . '/read')) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit">Mark Read</button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-pagination" data-table-pagination data-target-table="notifications-table">
        <div data-table-page-info></div>
        <div class="pagination">
            <button class="btn btn-ghost btn-sm" type="button" data-table-page-first>First</button>
            <button class="btn btn-ghost btn-sm" type="button" data-table-page-prev>Prev</button>
            <div class="page-jump">
                <input class="input input-sm" type="number" min="1" data-table-page-input>
                <span class="page-total" data-table-page-total></span>
            </div>
            <button class="btn btn-ghost btn-sm" type="button" data-table-page-next>Next</button>
            <button class="btn btn-ghost btn-sm" type="button" data-table-page-last>Last</button>
        </div>
    </div>
</div>
