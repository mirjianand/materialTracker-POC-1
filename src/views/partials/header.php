<header class="app-header fixed-top">
  <div class="container-fluid d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center header-left">
      <a href="<?php echo function_exists('base_path') ? base_path('/') : '/'; ?>">
        <img src="<?php echo function_exists('base_path') ? base_path('css/images/logo.png') : '/css/images/logo.png'; ?>" alt="Logo" style="height:44px; width:auto;" onerror="this.style.display='none'" />
      </a>
    </div>

    <div class="header-center text-center">
      <h1 class="app-title">Material Tracker</h1>
    </div>

    <div class="header-right text-end">
      <?php
      require_once __DIR__ . '/../../core/auth.php';
      $user = null;
      if (class_exists('SessionHelper') && SessionHelper::isAuthenticated()) {
          $user = Auth::user();
      }

      if ($user) {
          $display = htmlspecialchars($user['name'] ?: $user['emp_id']);
          $email = htmlspecialchars($user['email'] ?? '');
          $role = htmlspecialchars($user['role']);
          $date = date('d M Y');
          echo '<div class="header-user">' . $display . '</div>';
          echo '<div class="header-email text-muted small">' . $email . '</div>';
          echo '<div class="header-date text-muted small">' . $date . '</div>';
      } else {
          echo '<a class="btn btn-sm btn-light" href="' . (function_exists('base_path') ? base_path('login') : '/login') . '">Login</a>';
      }
      ?>
    </div>
  </div>
</header>
