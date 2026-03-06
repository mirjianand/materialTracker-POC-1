<div class="p-4">
  <h2>Edit User</h2>
  <?php if (empty($user)): ?>
    <div class="alert alert-danger">User not found.</div>
  <?php else: ?>
    <div class="user-boxed" style="border:1px solid #dee2e6;padding:12px;border-radius:6px;">
      <form method="post" action="<?php echo function_exists('base_path') ? base_path('users/update') : '/users/update'; ?>">
        <?php if (function_exists('csrf_field')) { echo csrf_field(); } else { ?><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><?php } ?>
        <input type="hidden" name="id" value="<?php echo intval($user['id']); ?>">
        <div class="mb-2"><label class="form-label">Employee ID</label><input name="emp_id" class="form-control" value="<?php echo htmlspecialchars($user['emp_id'] ?? ''); ?>"></div>
        <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"></div>
        <div class="mb-2"><label class="form-label">Email</label><input name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"></div>
        <div class="mb-2"><label class="form-label">Role</label><select name="role" class="form-select"><?php foreach (($roles ?? []) as $r): ?><option value="<?php echo htmlspecialchars($r); ?>" <?php if (($user['role'] ?? '')===$r) echo 'selected'; ?>><?php echo htmlspecialchars($r); ?></option><?php endforeach; ?></select></div>
        <div class="mb-2 form-check"><input type="checkbox" name="is_active" class="form-check-input" id="u_is_active" <?php if (!empty($user['is_active'])) echo 'checked'; ?>><label class="form-check-label" for="u_is_active">Active</label></div>
        <div><button class="btn btn-primary">Save</button> <a class="btn btn-secondary" href="<?php echo function_exists('base_path') ? base_path('users') : '/users'; ?>">Cancel</a></div>
      </form>
    </div>
  <?php endif; ?>
</div>
