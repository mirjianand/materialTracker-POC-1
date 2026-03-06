<div class="p-4">
  <h2>Inventory (Admin)</h2>
  <style>
    /* reduce table font and row padding by ~15% for compact display */
    .table {
      font-size: 0.78rem;
      border-collapse: collapse;
    }
    .table th, .table td {
      padding: 0.32rem 0.4rem;
      vertical-align: middle;
      border: 1px solid #e9ecef;
    }
    .table thead th { font-size: 0.78rem; }
    .table .btn-sm {
      padding: 0.12rem 0.28rem;
      font-size: 0.72rem;
    }
    /* compact form controls */
    .admin-controls .form-control, .admin-controls .form-select, .admin-controls .btn {
      font-size: 0.85rem;
      padding: 0.25rem 0.4rem;
    }
    .admin-controls .btn { line-height: 1.2; }
  </style>
  <?php
    // summary counts
    require_once __DIR__ . '/../../core/db.php';
    $db = Database::getInstance()->getConnection();
    $totalInQA = (int)@$db->query("SELECT COUNT(*) FROM inventory WHERE status = 'In-QA'")->fetchColumn();
    $totalAccepted = (int)@$db->query("SELECT COUNT(*) FROM inventory WHERE status = 'Accepted'")->fetchColumn();
    $totalWithOwner = (int)@$db->query("SELECT COUNT(*) FROM inventory WHERE status = 'With Owner'")->fetchColumn();
    $totalInTransit = (int)@$db->query("SELECT COUNT(*) FROM inventory WHERE status = 'In-transit'")->fetchColumn();
  ?>

  <?php
    // additional totals
    $totalToRework = (int)@$db->query("SELECT COUNT(*) FROM inventory WHERE status = 'To-Rework'")->fetchColumn();
    $totalSurrendered = (int)@$db->query("SELECT COUNT(*) FROM inventory WHERE status = 'Surrendered'")->fetchColumn();
  ?>
  <div class="mb-3 d-flex gap-3 align-items-start">
    <div class="p-2 border rounded summary-card summary-qa">
      <a href="<?php echo function_exists('base_path') ? base_path('admin/inventory?status=In-QA') : '/admin/inventory?status=In-QA'; ?>">
        <div class="text-muted small">Total QA</div>
        <div class="fw-bold"><?php echo $totalInQA; ?></div>
      </a>
    </div>
    <div class="p-2 border rounded summary-card summary-accepted">
      <a href="<?php echo function_exists('base_path') ? base_path('admin/inventory?status=Accepted') : '/admin/inventory?status=Accepted'; ?>">
        <div class="text-muted small">Total Accepted</div>
        <div class="fw-bold"><?php echo $totalAccepted; ?></div>
      </a>
    </div>
    <div class="p-2 border rounded summary-card summary-owner">
      <a href="<?php echo function_exists('base_path') ? base_path('admin/inventory?status=With%20Owner') : '/admin/inventory?status=With%20Owner'; ?>">
        <div class="text-muted small">Total Owner</div>
        <div class="fw-bold"><?php echo $totalWithOwner; ?></div>
      </a>
    </div>
    <div class="p-2 border rounded summary-card summary-transit">
      <a href="<?php echo function_exists('base_path') ? base_path('admin/inventory?status=In-transit') : '/admin/inventory?status=In-transit'; ?>">
        <div class="text-muted small">Total Emp-Transfer</div>
        <div class="fw-bold"><?php echo $totalInTransit; ?></div>
      </a>
    </div>
    <div class="p-2 border rounded summary-card summary-rework">
      <a href="<?php echo function_exists('base_path') ? base_path('admin/inventory?status=To-Rework') : '/admin/inventory?status=To-Rework'; ?>">
        <div class="text-muted small">Total Rework</div>
        <div class="fw-bold"><?php echo $totalToRework; ?></div>
      </a>
    </div>
    <div class="p-2 border rounded summary-card summary-surrender">
      <a href="<?php echo function_exists('base_path') ? base_path('admin/inventory?status=Surrendered') : '/admin/inventory?status=Surrendered'; ?>">
        <div class="text-muted small">Total Surrendered</div>
        <div class="fw-bold"><?php echo $totalSurrendered; ?></div>
      </a>
    </div>
  </div>

  <form method="get" class="mb-3" action="<?php echo function_exists('base_path') ? base_path('admin/inventory') : '/admin/inventory'; ?>">
    <div class="row g-2 admin-controls">
      <div class="col-md-3">
        <input type="search" name="q" value="<?php echo htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Search item code or description">
      </div>
      <div class="col-md-3">
        <select name="owner_id" class="form-select">
          <option value="">-- All owners --</option>
          <?php foreach ($users as $u): ?>
            <option value="<?php echo $u['id']; ?>" <?php if (!empty($owner) && $owner == $u['id']) echo 'selected'; ?>><?php echo htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select">
          <option value="">-- Any status --</option>
          <?php foreach (($statuses ?? []) as $s): ?>
            <option value="<?php echo htmlspecialchars($s); ?>" <?php if (!empty($status) && $status === $s) echo 'selected'; ?>><?php echo htmlspecialchars($s); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="page_size" class="form-select">
          <?php foreach ([10,25,50,100] as $ps): ?>
            <option value="<?php echo $ps; ?>" <?php if (!empty($pageSize) && $pageSize == $ps) echo 'selected'; ?>><?php echo $ps; ?> per page</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary">Filter</button>
      </div>
    </div>
  </form>


  <!-- Bulk actions compact (moved inline with summary) -->
  <form id="bulk-action-form" method="post" action="<?php echo function_exists('base_path') ? base_path('admin/inventory/bulk-action') : '/admin/inventory/bulk-action'; ?>" class="mb-3">
    <?php if (function_exists('csrf_field')) { echo csrf_field(); } else { ?>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <?php } ?>
    <input type="hidden" name="ids" id="bulk-ids" value="">
    <div class="d-flex gap-2 align-items-center">
      <div>
        <select id="bulk-action-select" name="action" class="form-select" style="min-width:140px;font-size:0.82rem;padding:0.18rem 0.32rem;">
          <option value="">Bulk action...</option>
          <option value="transfer">Transfer</option>
          <option value="rework">Send to Rework</option>
          <option value="surrender">Surrender</option>
        </select>
      </div>
      <div>
        <button type="button" id="bulk-apply" class="btn btn-primary" style="font-size:0.82rem;padding:0.22rem 0.5rem;">Apply</button>
      </div>
      <div class="ms-auto">
        <small class="text-muted">Select rows using checkboxes on the left.</small>
      </div>
    </div>
  </form>

  <?php if (!empty($items)): ?>
    <table class="table table-sm table-bordered">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all-page"></th>
          <th>ID</th>
          <th>Qty</th>
          <th>Item Code</th>
          <th>Description</th>
          <th>Category</th>
          <th>Type</th>
          <th>Material</th>
          <th>Owner</th>
          <th>Status</th>
          <th>PO</th>
          <th>Serial</th>
          <th>Received</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><input type="checkbox" class="row-select" data-id="<?php echo $it['id']; ?>" data-status="<?php echo htmlspecialchars((string)($it['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></td>
            <td><?php echo $it['id']; ?></td>
            <td><?php echo intval($it['quantity'] ?? 1); ?></td>
            <td><?php echo htmlspecialchars((string)($it['item_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['category_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['item_type_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['material_type_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['owner_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['po_number'] ?? $it['po_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['serial_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars((string)($it['received_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
              <?php if (($it['status'] ?? '') === 'Accepted'): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo function_exists('base_path') ? base_path('admin/inventory/transfer-form') : '/admin/inventory/transfer-form'; ?>?inventory_id=<?php echo $it['id']; ?>">Transfer</a>
              <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary" disabled title="Only QA-accepted items can be transferred">Transfer</button>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-warning" href="<?php echo function_exists('base_path') ? base_path('admin/inventory/rework-form') : '/admin/inventory/rework-form'; ?>?inventory_id=<?php echo $it['id']; ?>">Rework</a>
              <a class="btn btn-sm btn-outline-danger" href="<?php echo function_exists('base_path') ? base_path('admin/inventory/surrender-form') : '/admin/inventory/surrender-form'; ?>?inventory_id=<?php echo $it['id']; ?>">Surrender</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if (!empty($total)): ?>
      <div class="d-flex justify-content-between align-items-center">
        <div>Showing page <?php echo intval($page); ?> — total <?php echo intval($total); ?> rows</div>
        <div>
          <?php $prev = max(1, $page-1); $next = $page+1; ?>
          <a class="btn btn-sm btn-outline-secondary" href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$prev])); ?>">Prev</a>
          <a class="btn btn-sm btn-outline-secondary" href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$next])); ?>">Next</a>
        </div>

      <!-- Modals for bulk action inputs -->
      <div class="modal fade" id="bulkTransferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form id="bulk-transfer-form" method="post" action="<?php echo function_exists('base_path') ? base_path('admin/inventory/bulk-action') : '/admin/inventory/bulk-action'; ?>">
              <?php if (function_exists('csrf_field')) { echo csrf_field(); } else { ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <?php } ?>
              <input type="hidden" name="action" value="transfer">
              <input type="hidden" name="ids" id="transfer-ids" value="">
              <div class="modal-header"><h5 class="modal-title">Bulk Transfer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <div class="mb-2">
                      <label class="form-label">Transfer To (user)</label>
                      <div class="input-group">
                        <input type="search" id="bulk-to-user" class="form-control" placeholder="Type name or email to search">
                        <input type="hidden" name="to_user" id="bulk-to-user-id">
                        <button type="button" class="btn btn-outline-secondary" id="bulk-to-user-clear">Clear</button>
                      </div>
                      <div id="bulk-to-user-suggestions" class="list-group mt-1"></div>
                </div>
              </div>
                  <div class="mb-2">
                    <label class="form-label">Employee Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks"></textarea>
                  </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Confirm Transfer</button></div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="bulkReworkModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form id="bulk-rework-form" method="post" action="<?php echo function_exists('base_path') ? base_path('admin/inventory/bulk-action') : '/admin/inventory/bulk-action'; ?>">
              <?php if (function_exists('csrf_field')) { echo csrf_field(); } else { ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <?php } ?>
              <input type="hidden" name="action" value="rework">
              <input type="hidden" name="ids" id="rework-ids" value="">
              <div class="modal-header"><h5 class="modal-title">Send to Rework</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <div class="mb-2">
                  <label class="form-label">Vendor</label>
                    <div class="input-group">
                      <input type="search" id="bulk-vendor" name="vendor" class="form-control" placeholder="Type vendor name to search">
                      <input type="hidden" id="bulk-vendor-id" name="vendor_id">
                      <button type="button" class="btn btn-outline-secondary" id="bulk-vendor-clear">Clear</button>
                    </div>
                    <div id="bulk-vendor-suggestions" class="list-group mt-1"></div>
                </div>
              </div>
              <div class="mb-2">
                <label class="form-label">Employee Remarks</label>
                <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks"></textarea>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Confirm</button></div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="bulkSurrenderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <form id="bulk-surrender-form" method="post" action="<?php echo function_exists('base_path') ? base_path('admin/inventory/bulk-action') : '/admin/inventory/bulk-action'; ?>">
              <?php if (function_exists('csrf_field')) { echo csrf_field(); } else { ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <?php } ?>
              <input type="hidden" name="action" value="surrender">
              <input type="hidden" name="ids" id="surrender-ids" value="">
              <div class="modal-header"><h5 class="modal-title">Surrender Items</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <div class="mb-2">
                  <label class="form-label">Reason</label>
                    <input name="reason" class="form-control" placeholder="Reason for surrender">
                </div>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Confirm Surrender</button></div>
            </form>
          </div>
        </div>
      </div>

      <script>
      document.addEventListener('DOMContentLoaded', function(){
      const selectAll = document.getElementById('select-all-page');
      const rowChecks = Array.from(document.querySelectorAll('.row-select'));
      const bulkIds = document.getElementById('bulk-ids');
      const bulkSelect = document.getElementById('bulk-action-select');
      const applyBtn = document.getElementById('bulk-apply');
      const MAX_BULK = 200; // client-side safety limit

      function getSelectedIds(){ return rowChecks.filter(c=>c.checked).map(c=>c.dataset.id); }

      function getSelectedIdsByStatus(status){ return rowChecks.filter(c=>c.checked && c.dataset.status===status).map(c=>c.dataset.id); }

      if (selectAll) selectAll.addEventListener('change', function(){ rowChecks.forEach(c=>c.checked = selectAll.checked); });

      applyBtn.addEventListener('click', function(){
        const rawIds = getSelectedIds();
        if (!rawIds.length) { alert('No rows selected'); return; }
        if (ids.length > MAX_BULK) { if (!confirm('You have selected ' + ids.length + ' items. This exceeds the recommended maximum of ' + MAX_BULK + '. Continue?')) return; }
        const action = bulkSelect.value;
        if (!action) { alert('Select an action'); return; }
        // For transfer, only include QA-accepted items
        if (action === 'transfer') {
          const accepted = getSelectedIdsByStatus('Accepted');
          if (!accepted.length) { alert('No QA-accepted items selected for transfer.'); return; }
          if (accepted.length < rawIds.length) {
            if (!confirm('Some selected items are not QA-accepted and will be skipped. Continue with the accepted items only?')) return;
          }
          document.getElementById('transfer-ids').value = accepted.join(',');
          document.getElementById('rework-ids').value = accepted.join(',');
          document.getElementById('surrender-ids').value = accepted.join(',');
          bulkIds.value = accepted.join(',');
          new bootstrap.Modal(document.getElementById('bulkTransferModal')).show();
        } else {
          // set ids on forms and open appropriate modal for other actions
          document.getElementById('transfer-ids').value = rawIds.join(',');
          document.getElementById('rework-ids').value = rawIds.join(',');
          document.getElementById('surrender-ids').value = rawIds.join(',');
          bulkIds.value = rawIds.join(',');
          if (action === 'rework') new bootstrap.Modal(document.getElementById('bulkReworkModal')).show();
          else if (action === 'surrender') new bootstrap.Modal(document.getElementById('bulkSurrenderModal')).show();
        }
      });
    });
  
    // Autocomplete helpers for user/vendor search
    function debounce(fn, wait){ let t; return function(...a){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,a), wait); }; }

    function wireSearch(inputId, suggestionsId, apiUrl, hiddenInputId){
      const input = document.getElementById(inputId);
      const sugg = document.getElementById(suggestionsId);
      const hidden = document.getElementById(hiddenInputId);
      if (!input) return;
      const show = (items)=>{
        sugg.innerHTML = '';
        items.forEach(it=>{
          const el = document.createElement('button'); el.type='button'; el.className='list-group-item list-group-item-action';
          el.textContent = it.name || it.vendor_name || it.email || '' + (it.emp_id?(' ('+it.emp_id+')'):'');
          el.addEventListener('click', function(){
            hidden.value = it.id || it.vendor_name || '';
            input.value = el.textContent;
            sugg.innerHTML = '';
          });
          sugg.appendChild(el);
        });
      };
      const doSearch = debounce(function(){
        const q = input.value.trim(); if (q.length < 1) { sugg.innerHTML=''; hidden.value=''; return; }
        fetch(apiUrl + '?q=' + encodeURIComponent(q)).then(r=>r.json()).then(data=> show(data)).catch(()=>{});
      }, 250);
      input.addEventListener('input', doSearch);
      // clear button functionality if present
      const clearBtn = input.parentElement.querySelector('button'); if (clearBtn) clearBtn.addEventListener('click', function(){ input.value=''; hidden.value=''; sugg.innerHTML=''; });
    }

    wireSearch('bulk-to-user','bulk-to-user-suggestions','<?php echo function_exists('base_path') ? base_path('api/users') : '/api/users'; ?>','bulk-to-user-id');
    wireSearch('bulk-vendor','bulk-vendor-suggestions','<?php echo function_exists('base_path') ? base_path('api/vendors') : '/api/vendors'; ?>','bulk-vendor-id');
    wireSearch('rework-vendor','rework-vendor-suggestions','<?php echo function_exists('base_path') ? base_path('api/vendors') : '/api/vendors'; ?>','rework-vendor-id');
      </script>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <p>No inventory rows found.</p>
  <?php endif; ?>
</div>
