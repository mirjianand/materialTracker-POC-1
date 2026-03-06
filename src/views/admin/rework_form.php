<div class="p-4">
  <h2>Send Inventory Item to Rework <?php if (!empty($item['status'])): ?><span class="badge bg-info ms-2"><?php echo htmlspecialchars($item['status']); ?></span><?php endif; ?></h2>
  <?php if (empty($item)): ?>
    <p class="text-danger">Item not found.</p>
  <?php else: ?>
    <form method="post" action="<?php echo function_exists('base_path') ? base_path('admin/inventory/rework') : '/admin/inventory/rework'; ?>">
      <?php if (function_exists('csrf_field')) { echo csrf_field(); } else { ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <?php } ?>
      <input type="hidden" name="inventory_id" value="<?php echo intval($item['id']); ?>">

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Item Details <?php if (!empty($item['status'])): ?><span class="badge bg-info ms-2"><?php echo htmlspecialchars($item['status']); ?></span><?php endif; ?></h5>
          <div class="row g-2">
            <div class="col-sm-4 text-muted">Inventory ID</div>
            <div class="col-sm-8 fw-bold"><?php echo intval($item['id']); ?></div>

            <div class="col-sm-4 text-muted">Item</div>
            <div class="col-sm-8"><?php echo htmlspecialchars($item['item_code'] ?? ''); ?> — <?php echo htmlspecialchars($item['description'] ?? ''); ?></div>

            <div class="col-sm-4 text-muted">Quantity</div>
            <div class="col-sm-8"><?php echo intval($item['quantity'] ?? 1); ?></div>

            <div class="col-sm-4 text-muted">Serial No</div>
            <div class="col-sm-8"><?php echo htmlspecialchars($item['serial_number'] ?? ''); ?></div>
          </div>
        </div>
      </div>

      <?php if (($item['status'] ?? '') === 'To-Rework'): ?>
        <div class="alert alert-warning">This item is already marked <strong>To-Rework</strong>. No further action is required.</div>
      <?php elseif (($item['status'] ?? '') === 'Surrendered'): ?>
        <div class="alert alert-danger">This item has been <strong>Surrendered</strong> and cannot be sent for rework.</div>
      <?php endif; ?>
      
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Rework Action</h5>
          <div class="mb-3">
            <label class="form-label">Vendor</label>
            <div class="input-group">
              <input type="search" id="rework-vendor" name="vendor" class="form-control" placeholder="Type vendor name to search">
              <input type="hidden" id="rework-vendor-id" name="vendor_id">
              <button type="button" class="btn btn-outline-secondary" id="rework-vendor-clear">Clear</button>
            </div>
            <div id="rework-vendor-suggestions" class="list-group mt-1"></div>
          </div>
          <div class="mb-3 row">
            <div class="col-md-6">
              <label class="form-label">Send Date</label>
              <input name="send_date" type="datetime-local" class="form-control" value="<?php echo date('Y-m-d\\TH:i'); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Remarks</label>
              <input name="remarks" class="form-control" placeholder="Optional remarks">
            </div>
          </div>
          <div class="d-flex gap-2">
            <?php if (($item['status'] ?? '') === 'To-Rework'): ?>
              <button class="btn btn-secondary" disabled><i class="bi bi-arrow-repeat"></i> Already To-Rework</button>
            <?php elseif (($item['status'] ?? '') === 'Surrendered'): ?>
              <button class="btn btn-secondary" disabled><i class="bi bi-x-circle"></i> Cannot Rework</button>
            <?php else: ?>
              <button class="btn btn-warning"><i class="bi bi-tools"></i> Send to Rework</button>
            <?php endif; ?>
            <a class="btn btn-secondary" href="<?php echo function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'; ?>">Cancel</a>
          </div>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>
