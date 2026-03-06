<div class="p-4">
  <h2>User Management</h2>

  <style>
    .user-boxed { font-size: 0.85rem; }
    .user-boxed .form-control, .user-boxed .form-select { font-size: 0.85rem; padding: .25rem .5rem; }
    table.table th, table.table td { font-size: 0.85rem; padding: .35rem .4rem; }
  </style>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash']['type']); ?>"><?php echo htmlspecialchars($_SESSION['flash']['message']); ?></div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-4">
      <div style="border:1px solid #dee2e6;padding:12px;border-radius:6px;">
      <h4>Create User</h4>
      <form method="post" action="<?php echo function_exists('base_path') ? base_path('users') : '/users'; ?>">
        <?php echo CSRF::inputField(); ?>
        <div class="mb-2">
          <label class="form-label">Employee ID</label>
          <input name="emp_id" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Name</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Role</label>
          <select name="role" class="form-select">
            <?php foreach ($roles as $r): ?>
              <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Password (optional)</label>
          <input name="password" type="password" class="form-control">
        </div>
        <div class="mb-2 form-check">
          <input type="checkbox" name="is_active" class="form-check-input" id="ua_is_active" checked>
          <label class="form-check-label" for="ua_is_active">Active</label>
        </div>
        <button class="btn btn-primary">Create</button>
      </form>
      </div>
    </div>

    <div class="col-md-8">
      <div style="border:1px solid #dee2e6;padding:12px;border-radius:6px;">
      <h4>Users</h4>
      <form method="get" class="mb-3" action="<?php echo function_exists('base_path') ? base_path('users') : '/users'; ?>">
        <div class="row g-2">
          <div class="col-md-5"><input name="q" value="<?php echo htmlspecialchars($q ?? ''); ?>" class="form-control" placeholder="Search name, email, emp id"></div>
          <div class="col-md-3">
            <select name="role" class="form-select">
              <option value="">-- Any role --</option>
              <?php foreach ($roles as $r): ?>
                <option value="<?php echo htmlspecialchars($r); ?>" <?php if (!empty($roleFilter) && $roleFilter === $r) echo 'selected'; ?>><?php echo htmlspecialchars($r); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <select name="page_size" class="form-select">
              <?php foreach ([10,25,50] as $ps): ?>
                <option value="<?php echo $ps; ?>" <?php if (!empty($pageSize) && $pageSize == $ps) echo 'selected'; ?>><?php echo $ps; ?> per page</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2"><button class="btn btn-secondary">Search</button></div>
        </div>
      </form>

      <?php if (!empty($users)): ?>
        <table class="table table-sm">
          <thead><tr><th>ID</th><th>Emp ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th></tr></thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><?php echo intval($u['id']); ?></td>
                <td><?php echo htmlspecialchars($u['emp_id'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($u['name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($u['role'] ?? ''); ?></td>
                <td><?php echo $u['is_active'] ? 'Yes' : 'No'; ?></td>
                <td><a class="btn btn-sm btn-outline-secondary" href="<?php echo function_exists('base_path') ? base_path('users/edit?id='.$u['id']) : '/users/edit?id='.$u['id']; ?>">Edit</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if (!empty($total)): ?>
          <div class="d-flex justify-content-between align-items-center">
            <div>Page <?php echo intval($page); ?> — total <?php echo intval($total); ?> users</div>
            <div>
              <?php $prev = max(1, $page-1); $next = $page+1; ?>
              <a class="btn btn-sm btn-outline-secondary" href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$prev])); ?>">Prev</a>
              <a class="btn btn-sm btn-outline-secondary" href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$next])); ?>">Next</a>
            </div>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <p>No users found.</p>
      <?php endif; ?>
      </div>
    </div>
  </div>
</div>
