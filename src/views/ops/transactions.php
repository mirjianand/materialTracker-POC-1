<div class="p-4">
  <h2>Item Transactions - Issue / Receipt</h2>
  <?php if (!empty($owned)): ?>
    <h5>Owned Item List</h5>
    <form method="post" action="<?= function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions' ?>">
      <?= CSRF::inputField() ?>
      <input type="hidden" name="action" value="create_transfer" />
      <div class="table-responsive">
        <table class="table table-sm">
          <thead><tr><th></th><th>Item ID</th><th>Item Name</th><th>Qty</th><th>Serial No</th><th>Trans Date</th><th>Accept Date</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($owned as $o): ?>
              <tr>
                <td><input type="checkbox" name="inventory_ids[]" value="<?= htmlspecialchars($o['id']) ?>"></td>
                <td><?= htmlspecialchars($o['id']) ?></td>
                <td><?= htmlspecialchars($o['item_code'] ?? $o['description'] ?? '') ?></td>
                <td><?= htmlspecialchars($o['quantity'] ?? 1) ?></td>
                <td><?= htmlspecialchars($o['serial_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($o['trans_date'] ?? '') ?></td>
                <td><?= htmlspecialchars($o['accept_date'] ?? '') ?></td>
                <td><?= htmlspecialchars($o['status'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="row">
        <div class="col-md-6">
          <label class="form-label">Transfer To (Employee)</label>
          <select name="to_user_id" class="form-select">
            <option value="">-- Select recipient --</option>
            <?php foreach ($users as $u): ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control"></textarea>
          <div class="mt-2 text-end"><button class="btn btn-primary">Create Transfer</button></div>
        </div>
      </div>
    </form>
  <?php else: ?>
    <p>You do not own any items.</p>
  <?php endif; ?>

  <hr />

  <div class="row">
    <div class="col-md-6">
      <h5>Items In-coming</h5>
      <?php if (empty($incoming)): ?>
        <p>No incoming transfers.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>Item ID</th><th>Item Name</th><th>Qty</th><th>Serial No</th><th>Trans Date</th><th>Accept Date</th><th>From</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($incoming as $it): ?>
                <tr>
                  <td><?= htmlspecialchars($it['inventory_id'] ?? '') ?></td>
                  <td><?= htmlspecialchars($it['item_code'] ?? $it['description'] ?? '') ?></td>
                  <td><?= htmlspecialchars($it['ti_quantity'] ?? 1) ?></td>
                  <td><?= htmlspecialchars($it['serial_number'] ?? '') ?></td>
                  <td><?= htmlspecialchars($it['trans_date'] ?? '') ?></td>
                  <td><?= '' /* accept date empty until accepted */ ?></td>
                  <td><?= htmlspecialchars($it['from_name'] ?? '') ?></td>
                  <td>
                    <form method="post" class="d-inline" action="<?= function_exists('base_path') ? base_path('ops/transactions') : '/ops/transactions' ?>">
                      <?= CSRF::inputField() ?>
                      <input type="hidden" name="action" value="accept" />
                      <input type="hidden" name="transfer_id" value="<?= (int)$it['transfer_id'] ?>" />
                      <button class="btn btn-sm btn-success">Accept</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-md-6">
      <h5>Outgoing Transfers (Pending)</h5>
      <?php if (empty($outgoing)): ?>
        <p>No outgoing pending transfers.</p>
      <?php else: foreach ($outgoing as $ot): ?>
        <div class="card mb-2">
          <div class="card-body">
            <strong>To:</strong> <?= htmlspecialchars($ot['to_name'] ?? 'Unknown') ?> <br />
            <strong>Transfer #</strong> <?= htmlspecialchars($ot['transfer_number']) ?> <br />
            <small>Created: <?= htmlspecialchars($ot['created_at']) ?></small>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <hr />

  <h5>Recent Transactions</h5>
  <?php if (!empty($transactions)): ?>
    <table class="table table-sm">
      <thead><tr><th>ID</th><th>Inventory</th><th>Type</th><th>Qty</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($transactions as $t): ?>
          <tr>
            <td><?= $t['id'] ?></td>
            <td><?= $t['inventory_id'] ?></td>
            <td><?= htmlspecialchars($t['transaction_type']) ?></td>
            <td><?= $t['quantity'] ?></td>
            <td><?= $t['transaction_date'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No recent transactions.</p>
  <?php endif; ?>
</div>
