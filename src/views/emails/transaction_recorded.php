<div>
  <p>Hello,</p>
  <p>An item transaction has been recorded:</p>
  <ul>
    <li><strong>Inventory ID:</strong> <?php echo htmlspecialchars($inventory_id ?? ''); ?></li>
    <li><strong>Type:</strong> <?php echo htmlspecialchars($transaction_type ?? ''); ?></li>
    <li><strong>Quantity:</strong> <?php echo htmlspecialchars($quantity ?? ''); ?></li>
    <li><strong>Remarks:</strong> <?php echo nl2br(htmlspecialchars($remarks ?? '')); ?></li>
  </ul>
  <p>Regards,<br/>Material Tracker</p>
</div>
