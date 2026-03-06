<div class="p-4">
  <h2>Transfer Inventory Item</h2>
  <?php if (empty($item)): ?>
    <p class="text-danger">Item not found.</p>
  <?php else: ?>
    <form method="post" action="<?php echo function_exists('base_path') ? base_path('admin/inventory/transfer') : '/admin/inventory/transfer'; ?>">
      <?php if (function_exists('csrf_field')) { echo csrf_field(); } else { ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <?php } ?>
      <input type="hidden" name="inventory_id" value="<?php echo intval($item['id']); ?>">

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Item Details</h5>
          <div class="row g-2">
            <div class="col-sm-4 text-muted">Inventory ID</div>
            <div class="col-sm-8 fw-bold"><?php echo intval($item['id']); ?></div>

            <div class="col-sm-4 text-muted">Item Code / Name</div>
            <div class="col-sm-8 fw-bold"><?php echo htmlspecialchars($item['item_code'] ?? ''); ?> / <?php echo htmlspecialchars($item['description'] ?? ''); ?></div>

            <div class="col-sm-4 text-muted">Quantity</div>
            <div class="col-sm-8"><?php echo intval($item['quantity'] ?? 1); ?></div>

            <div class="col-sm-4 text-muted">Current Owner</div>
            <div class="col-sm-8"><?php echo htmlspecialchars($item['owner_name'] ?? ''); ?></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Transfer Action</h5>
          <div class="mb-3">
            <label class="form-label">Transfer To (user)</label>
            <select name="to_user" class="form-select">
              <option value="">-- Select user --</option>
              <?php foreach (($users ?? []) as $u): ?>
                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name'] ?? $u['email'] ?? $u['id']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3 row">
            <div class="col-md-6">
              <label class="form-label">Date of Transfer</label>
              <input name="transfer_date" type="datetime-local" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Employee Remarks</label>
              <input name="remarks" class="form-control" placeholder="Optional remarks">
            </div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary">Transfer</button>
            <a class="btn btn-secondary" href="<?php echo function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'; ?>">Cancel</a>
          </div>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>
