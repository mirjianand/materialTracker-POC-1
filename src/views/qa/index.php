<div class="p-4">
  <h2>QA Acceptance</h2>
  <?php
    require_once __DIR__ . '/../../core/db.php';
    $db = Database::getInstance()->getConnection();
    $totalInQA = (int)@$db->query("SELECT COUNT(*) FROM inventory WHERE status = 'In-QA'")->fetchColumn();
    $totalOthers = (int)@$db->query("SELECT COUNT(*) FROM inventory WHERE status != 'In-QA'")->fetchColumn();
  ?>
  <div class="mb-3 d-flex gap-3 align-items-start">
    <div class="p-2 border rounded summary-card">
      <div class="text-muted small">Total In-QA</div>
      <div class="fw-bold"><?php echo $totalInQA; ?></div>
    </div>
    <div class="p-2 border rounded summary-card">
      <div class="text-muted small">Total Others</div>
      <div class="fw-bold"><?php echo $totalOthers; ?></div>
    </div>
  </div>
  <?php if (!empty($items)): ?>
    <style>
      /* match admin inventory compact bordered table */
      .qa-table { font-size: 0.78rem; border-collapse: collapse; }
      .qa-table th, .qa-table td { padding: 0.32rem 0.4rem; vertical-align: middle; border: 1px solid #e9ecef; }
      .qa-table thead th { font-size: 0.78rem; }
      .qa-table .btn-sm { padding: 0.12rem 0.28rem; font-size: 0.72rem; }
    </style>
    <table class="table table-sm table-bordered qa-table">
      <thead><tr><th>ID</th><th>Item Code</th><th>Item Name</th><th>PO#</th><th>Qty</th><th>Serial</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?php echo intval($it['id']); ?></td>
            <td><?php echo htmlspecialchars($it['item_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($it['item_name'] ?? $it['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($it['po_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo intval($it['quantity'] ?? 1); ?></td>
            <td><?php echo htmlspecialchars($it['serial_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($it['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
              <form method="post" style="display:inline" action="<?php echo function_exists('base_path') ? base_path('qa') : '/qa'; ?>">
                <?php echo CSRF::inputField(); ?>
                <input type="hidden" name="inventory_id" value="<?php echo intval($it['id']); ?>" />
                <button name="action" value="accept" class="btn btn-sm btn-success">Accept</button>
              </form>
              <form method="post" style="display:inline" action="<?php echo function_exists('base_path') ? base_path('qa') : '/qa'; ?>">
                <?php echo CSRF::inputField(); ?>
                <input type="hidden" name="inventory_id" value="<?php echo intval($it['id']); ?>" />
                <button name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No items currently in QA.</p>
  <?php endif; ?>
</div>
