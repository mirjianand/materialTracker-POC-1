<?php
// src/views/admin/notification_recipients.php
?>
<div class="container mt-4">
    <h1><?= htmlspecialchars($title) ?></h1>

    <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['message']) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="post" action="<?= (function_exists('base_path') ? base_path('admin/notification_recipients/add') : '/admin/notification_recipients/add') ?>">
                <?= CSRF::inputField() ?>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <input type="text" name="role_name" class="form-control" placeholder="Role name (e.g. CommodityManager)" required />
                    </div>
                    <div class="col-md-5 mb-2">
                        <input type="email" name="email" class="form-control" placeholder="email@example.org" required />
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-primary">Add Recipient</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-sm">
        <thead>
            <tr><th>ID</th><th>Role</th><th>Email</th><th>Added</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php if (empty($recipients)): ?>
            <tr><td colspan="5">No recipients configured.</td></tr>
        <?php else: foreach ($recipients as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['id']) ?></td>
                <td><?= htmlspecialchars($r['role_name']) ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><?= htmlspecialchars($r['created_at']) ?></td>
                <td>
                    <form method="post" action="<?= (function_exists('base_path') ? base_path('admin/notification_recipients/delete') : '/admin/notification_recipients/delete') ?>" style="display:inline-block">
                        <?= CSRF::inputField() ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($r['id']) ?>" />
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this recipient?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
