<div class="card">
    <h3 style="margin-top:0;">Notification Center</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>Message</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
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
                            <form method="post" action="<?= e(url('/notifications/' . (int) $notification['id'] . '/read')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-secondary" type="submit">Mark Read</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
