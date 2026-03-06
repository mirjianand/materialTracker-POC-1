<div class="p-4">
  <h2>PO Entries</h2>

  <style>
    .po-boxed { border: 1px solid #dee2e6; padding: 12px; border-radius:6px; margin-bottom:1rem; }
    .po-boxed h4 { margin-top:0; }
    .po-boxed .form-control, .po-boxed .form-select { font-size: 0.85rem; padding: .25rem .5rem; }
    .po-boxed table.table th, .po-boxed table.table td { padding: .35rem .4rem; font-size: 0.85rem; }
    .po-boxed .btn { padding: .25rem .5rem; font-size: 0.85rem; }
  </style>

  <div class="po-boxed">
    <h4>Create PO</h4>
    <form method="post" action="<?php echo function_exists('base_path') ? base_path('po/entries') : '/po/entries'; ?>">
    <?php echo CSRF::inputField(); ?>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">PO#</label>
        <input name="po_number" class="form-control" required />
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">PR#</label>
        <input name="pr_number" class="form-control" />
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">PO Type</label>
        <select name="po_type" class="form-select">
          <option value="local">Local</option>
          <option value="import">Import</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Vendor Name</label>
        <input name="vendor_name" class="form-control" />
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Vendor Invoice #</label>
        <input name="vendor_invoice" class="form-control" />
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">GRN #</label>
        <input name="grn_number" class="form-control" />
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-3">
        <label class="form-label">GRN Date</label>
        <input type="date" name="grn_date" class="form-control" />
      </div>
      <div class="col-md-9 text-end">
        <button type="button" id="btnAddRow" class="btn btn-secondary mt-4">Add Item Row</button>
        <button type="button" id="btnCsvUpload" class="btn btn-outline-secondary mt-4 ms-2">Upload CSV/Excel (placeholder)</button>
        <button class="btn btn-primary mt-4" type="submit">Create PO</button>
      </div>
    </div>

    <h5>Items</h5>
    <table class="table table-sm" id="poItemsTable">
      <thead>
        <tr>
          <th>Item Code</th>
          <th>Item Name</th>
          <th>Category</th>
          <th>Type</th>
          <th>Material</th>
          <th>Qty</th>
          <th>Expiry Date</th>
          <th>Serial No</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php for ($i=0;$i<3;$i++): ?>
        <tr>
          <td><input name="item_code[]" class="form-control" /></td>
          <td><input name="item_name[]" class="form-control" /></td>
          <td><input name="item_category[]" class="form-control" /></td>
          <td><input name="item_type[]" class="form-control" /></td>
          <td><input name="material_type[]" class="form-control" /></td>
          <td><input name="item_qty[]" type="number" min="0" class="form-control" /></td>
          <td><input name="item_expiry_date[]" type="date" class="form-control" /></td>
          <td><input name="item_srl_no[]" class="form-control" /></td>
          <td>
            <select name="item_status[]" class="form-select">
              <option value="In-QA">In-QA</option>
              <option value="Accepted">Accepted</option>
              <option value="Rejected">Rejected</option>
            </select>
          </td>
          <td><button type="button" class="btn btn-sm btn-danger btnRemove">Remove</button></td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
    </form>
  </div>

  <div class="po-boxed">
    <h4 class="mt-3">Recent POs</h4>
  <?php if (!empty($purchase_orders)): ?>
    <div class="mb-2">Showing page <?php echo $page; ?> of <?php echo ceil(($total ?? 0)/($pageSize ?? 25)); ?> — Total POs: <?php echo $total ?? 0; ?></div>
    <table class="table table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th><a href="?sort=po_number&order=<?php echo $order === 'ASC' ? 'desc' : 'asc'; ?>">PO#</a></th>
          <th><a href="?sort=pr_number&order=<?php echo $order === 'ASC' ? 'desc' : 'asc'; ?>">PR#</a></th>
          <th>PO Type</th>
          <th>Vendor</th>
          <th>Vendor Invoice</th>
          <th>GRN#</th>
          <th>GRN Date</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
            <?php foreach ($purchase_orders as $p): ?>
          <tr>
            <td><?php echo $p['id']; ?></td>
            <td>
              <a href="<?php echo (function_exists('base_path') ? base_path('po/view?id='.$p['id']) : '/po/view?id='.$p['id']); ?>"><?php echo htmlspecialchars($p['PO#'] ?? $p['po_number']); ?></a>
            </td>
            <td><?php echo htmlspecialchars($p['PR#'] ?? $p['pr_number']); ?></td>
            <td><?php echo htmlspecialchars($p['po_type'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($p['vendor_name'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($p['vendor_invoice'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($p['grn_number'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($p['grn_date'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($p['created_at'] ?? ''); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <nav>
      <ul class="pagination">
        <?php for ($pg=1;$pg<=max(1,ceil(($total ?? 0)/($pageSize ?? 25))); $pg++): ?>
          <li class="page-item <?php if ($pg==$page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $pg; ?>&sort=<?php echo htmlspecialchars($sort); ?>&order=<?php echo htmlspecialchars(strtolower($order)); ?>"><?php echo $pg; ?></a></li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php else: ?>
    <p>No POs found.</p>
  <?php endif; ?>
  </div>
</div>

<script>
document.getElementById('btnAddRow').addEventListener('click', function(){
  var tbody = document.querySelector('#poItemsTable tbody');
  var tr = document.createElement('tr');
  tr.innerHTML = '<td><input name="item_code[]" class="form-control" /></td>'+
    '<td><input name="item_name[]" class="form-control" /></td>'+
    '<td><input name="item_category[]" class="form-control" /></td>'+
    '<td><input name="item_type[]" class="form-control" /></td>'+
    '<td><input name="material_type[]" class="form-control" /></td>'+
    '<td><input name="item_qty[]" type="number" min="0" class="form-control" /></td>'+
    '<td><input name="item_expiry_date[]" type="date" class="form-control" /></td>'+
    '<td><input name="item_srl_no[]" class="form-control" /></td>'+
    '<td><select name="item_status[]" class="form-select"><option>In-QA</option><option>Accepted</option><option>Rejected</option></select></td>'+
    '<td><button type="button" class="btn btn-sm btn-danger btnRemove">Remove</button></td>';
  tbody.appendChild(tr);
});
document.addEventListener('click', function(e){ if (e.target && e.target.classList.contains('btnRemove')) e.target.closest('tr').remove(); });
var csvBtn = document.getElementById('btnCsvUpload');
if (csvBtn) csvBtn.addEventListener('click', function(){ alert('CSV/Excel upload will be implemented later. For large POs please prepare a CSV and contact admin for import.'); });
</script>
