<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($title ?? 'Material Tracker'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --app-header-bg: #0d6efd; /* bootstrap primary */
            --app-header-color: #ffffff;
        }
        body { padding-top: 72px; }
        .app-header { background: var(--app-header-bg); color: var(--app-header-color); height:72px; }
        .app-header .app-title { margin:0; font-size:1.4rem; font-weight:600; color:var(--app-header-color); }
        .header-left img { max-height:48px; }
        .header-right { min-width:220px; }
        .header-user { font-weight:600; color:var(--app-header-color); }
        .header-email, .header-date { color: rgba(255,255,255,0.85); }
        .sidebar { min-height: calc(100vh - 72px); border-right: 1px solid rgba(0,0,0,0.08); padding-top:1rem; background: var(--app-header-bg); color: var(--app-header-color); }
        .sidebar-stacked .nav-section { font-size:0.85rem; font-weight:700; color: rgba(255,255,255,0.9); padding:0.5rem 1rem; }
        .sidebar .nav-link { color: rgba(255,255,255,0.95); }
        .sidebar .nav-link.active { background: rgba(255,255,255,0.12); }
        .sidebar .nav-link .small { color: rgba(255,255,255,0.85); }
        .sidebar .collapse .nav-link { color: rgba(255,255,255,0.95); }
        .nav-link .bi { vertical-align: -.125em; }
        /* caret/chevron color for collapse toggles */
        .sidebar .nav-link .bi-chevron-down { color: rgba(255,255,255,0.9); }
        .sidebar .nav-link:hover, .sidebar .nav-link:focus { background: rgba(255,255,255,0.06); }
        /* Summary card styles used across admin pages */
        .summary-card { min-width:90px; text-align:center; padding:0.5rem 0.75rem; }
        .summary-card .small { font-size:0.75rem; }
        .summary-card a { text-decoration:none; color:inherit; display:block; }
        .summary-qa { background:#e7f5ff; }
        .summary-accepted { background:#e6ffed; }
        .summary-owner { background:#e9f0ff; }
        .summary-transit { background:#fff7e6; }
        .summary-rework { background:#fff1f0; }
        .summary-surrender { background:#ffeef0; }
        /* Compact behaviour for bulk controls and admin-controls */
        .admin-controls { flex-wrap:wrap; }
        @media (max-width: 576px) {
            .admin-controls .col-md-3, .admin-controls .col-md-2 { flex: 0 0 100%; max-width:100%; }
            .summary-card { min-width:70px; padding:0.35rem 0.5rem; }
            .admin-controls .form-control, .admin-controls .form-select, .admin-controls .btn { font-size:0.8rem; }
            #bulk-action-form { width:100%; margin-top:0.5rem; }
        }
    </style>
    <?php require_once __DIR__ . '/../core/session.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <?php
                // flash message (one-time)
                if (SessionHelper::isAuthenticated() && !empty($_SESSION['flash'])) {
                    $f = $_SESSION['flash'];
                    unset($_SESSION['flash']);
                    echo '<div class="container mt-3"><div class="alert alert-' . htmlspecialchars($f['type'] ?? 'info') . '">' . htmlspecialchars($f['message']) . '</div></div>';
                }
                echo $content;
                ?>
            </main>
        </div>
    </div>
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
