<div class="p-4">
  <h2>Surrender Inventory Item <?php if (!empty($item['status'])): ?><span class="badge bg-info ms-2"><?php echo htmlspecialchars($item['status']); ?></span><?php endif; ?></h2>
  <?php if (empty($item)): ?>
    <p class="text-danger">Item not found.</p>
  <?php else: ?>
    <form method="post" action="<?php echo function_exists('base_path') ? base_path('admin/inventory/surrender') : '/admin/inventory/surrender'; ?>">
      <?php if (function_exists('csrf_field')) { echo csrf_field(); } else { ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <?php } ?>
      <input type="hidden" name="inventory_id" value="<?php echo intval($item['id']); ?>">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Item Details <?php if (!empty($item['status'])): ?><span class="badge bg-info ms-2"><?php echo htmlspecialchars($item['status']); ?></span><?php endif; ?></h5>
            <div class="row g-2">
              <div class="col-sm-4 text-muted">Item</div>
              <div class="col-sm-8"><?php echo htmlspecialchars($item['item_code'] ?? ''); ?> — <?php echo htmlspecialchars($item['description'] ?? ''); ?></div>

              <div class="col-sm-4 text-muted">Quantity</div>
              <div class="col-sm-8"><?php echo intval($item['quantity'] ?? 1); ?></div>
            </div>
          </div>
        </div>

      <?php if (($item['status'] ?? '') === 'Surrendered'): ?>
        <div class="alert alert-warning">This item is already <strong>Surrendered</strong>.</div>
      <?php endif; ?>

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Surrender Action</h5>
            <div class="mb-3">
              <label class="form-label">Reason</label>
              <input name="reason" class="form-control" placeholder="Reason for surrender">
            </div>
            <div class="d-flex gap-2">
              <?php if (($item['status'] ?? '') === 'Surrendered'): ?>
                <button class="btn btn-secondary" disabled><i class="bi bi-check-circle"></i> Already Surrendered</button>
              <?php else: ?>
                <button class="btn btn-danger"><i class="bi bi-trash"></i> Surrender</button>
              <?php endif; ?>
              <a class="btn btn-secondary" href="<?php echo function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'; ?>">Cancel</a>
            </div>
          </div>
        </div>
      </form>
  <?php endif; ?>
</div>
