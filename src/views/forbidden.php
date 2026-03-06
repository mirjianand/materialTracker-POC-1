<?php
// src/views/forbidden.php
?>
<div class="container mt-5">
    <div class="card">
        <div class="card-body text-center">
            <h2 class="text-danger"><?= htmlspecialchars($title) ?></h2>
            <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
                <p class="lead"><?= htmlspecialchars($f['message']) ?></p>
            <?php else: ?>
                <p class="lead">You do not have permission to view this page.</p>
            <?php endif; ?>
            <a href="<?= (function_exists('base_path') ? base_path('') : '/') ?>" class="btn btn-primary">Return Home</a>
        </div>
    </div>
</div>
