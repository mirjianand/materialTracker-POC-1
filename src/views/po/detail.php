<div class="p-4">
  <h2>PO Detail</h2>
  <div class="mb-3">
    <a class="btn btn-sm btn-secondary" href="<?php echo function_exists('base_path') ? base_path('po/entries') : '/po/entries'; ?>">Back to POs</a>
  </div>

  <h4>PO Information</h4>
  <table class="table table-sm">
    <tbody>
      <?php foreach ($po as $k => $v): ?>
        <tr><th style="width:200px"><?php echo htmlspecialchars((string)$k, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></th><td><?php echo htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h4 class="mt-4">Items</h4>
  <?php if (!empty($items)): ?>
    <div class="d-flex justify-content-between mb-2">
      <div>Showing <?php echo htmlspecialchars((($itemPage-1)*$itemPageSize)+1); ?> - <?php echo htmlspecialchars(min($itemTotal, $itemPage*$itemPageSize)); ?> of <?php echo htmlspecialchars($itemTotal); ?> items</div>
      <div>
        <button class="btn btn-sm btn-outline-secondary" id="btnExportItems">Export (placeholder)</button>
      </div>
    </div>
    <table class="table table-sm">
      <thead>
        <tr>
          <th>#</th>
          <th>Item Code</th>
          <th>Item Name</th>
          <th>Category</th>
          <th>Type</th>
          <th>Material</th>
          <th>Qty</th>
          <th>Expiry</th>
          <th>Serial</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php $n=(($itemPage-1)*$itemPageSize)+1; foreach ($items as $it): ?>
        <tr>
          <td><?php echo $n++; ?></td>
          <td><?php echo htmlspecialchars($it['item_code'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['item_name'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['item_category'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['item_type'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['material_type'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['quantity'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['expiry_date'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['serial_number'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['item_status'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if ($itemTotal > $itemPageSize): ?>
      <?php $itemPages = (int)ceil($itemTotal / $itemPageSize); ?>
      <nav>
        <ul class="pagination">
          <?php for ($pg=1;$pg<=$itemPages;$pg++): ?>
            <li class="page-item <?php if ($pg==$itemPage) echo 'active'; ?>"><a class="page-link" href="<?php echo (function_exists('base_path') ? base_path('po/view?id='.urlencode($po['id']).'&item_page='.$pg) : '/po/view?id='.urlencode($po['id']).'&item_page='.$pg); ?>"><?php echo $pg; ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php else: ?>
    <p>No items found for this PO.</p>
  <?php endif; ?>
</div>

<script>
// Placeholder CSV upload handler
var btn = document.getElementById('btnCsvUpload');
if (btn) btn.addEventListener('click', function(){ alert('CSV/Excel upload will be implemented later.'); });
var btnExp = document.getElementById('btnExportItems');
if (btnExp) btnExp.addEventListener('click', function(){ alert('Export will be implemented later (CSV).'); });
</script>
