<div class="position-sticky pt-3 sidebar-stacked">
  <?php
  require_once __DIR__ . '/../../core/auth.php';
  $user = Auth::user();
  $role = $user['role'] ?? 'User';
  $current = str_replace('\\','/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  ?>

  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link<?php echo ($current === (function_exists('base_path') ? rtrim(base_path('/'), '/') : '')) ? ' active' : ''; ?>" href="<?php echo function_exists('base_path') ? base_path('/') : '/'; ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    </li>

    <?php if (in_array($role, ['LogisticsManager','CommodityManager','Admin'])): ?>
    <li class="nav-item nav-section">Admin Functions</li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#adminManage" role="button" aria-expanded="false" aria-controls="adminManage"><i class="bi bi-gear-fill me-2"></i>Manage <i class="bi bi-chevron-down ms-1"></i></a>
      <div class="collapse" id="adminManage">
        <ul class="nav flex-column ms-3">
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('items/create') : '/items/create'; ?>">Item Creation</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('po/entries') : '/po/entries'; ?>">PO Entries</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('users') : '/users'; ?>">User Management</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'; ?>">Inventory (Admin)</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('qa') : '/qa'; ?>">QA Acceptance</a></li>
        </ul>
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#adminActions" role="button" aria-expanded="false" aria-controls="adminActions"><i class="bi bi-list-task me-2"></i>Actions <i class="bi bi-chevron-down ms-1"></i></a>
      <div class="collapse" id="adminActions">
        <ul class="nav flex-column ms-3">
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('admin/process/lost') : '/admin/process/lost'; ?>">Process Item Lost Requests</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('admin/process/rework') : '/admin/process/rework'; ?>">Process Item Reworks</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('admin/process/surrender') : '/admin/process/surrender'; ?>">Process Surrendered Items</a></li>
        </ul>
      </div>
    </li>
    <?php endif; ?>

    <?php if ($role === 'User'): ?>
    <li class="nav-item nav-section">Operations</li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#opsActions" role="button" aria-expanded="false" aria-controls="opsActions"><i class="bi bi-people-fill me-2"></i>Actions <i class="bi bi-chevron-down ms-1"></i></a>
      <div class="collapse" id="opsActions">
        <ul class="nav flex-column ms-3">
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions'; ?>"><i class="bi bi-arrow-right-square me-2"></i>Item Transactions Issue/Receipt</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('ops/generate/lost') : '/ops/generate/lost'; ?>"><i class="bi bi-exclamation-triangle me-2"></i>Generate Item Lost Request</a></li>
        </ul>
      </div>
    </li>
    <?php endif; ?>

    <li class="nav-item nav-section">Reports</li>
    <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('reports') : '/reports'; ?>">Reports</a></li>

    <li class="nav-item nav-section">Feedback</li>
    <li class="nav-item"><a class="nav-link" href="<?php echo function_exists('base_path') ? base_path('feedback') : '/feedback'; ?>">Send Feedback</a></li>

    <li class="nav-item nav-section">Account</li>
    <li class="nav-item"><a class="nav-link text-danger" href="<?php echo function_exists('base_path') ? base_path('logout') : '/logout'; ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>

  </ul>
</div>
