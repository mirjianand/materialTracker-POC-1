<div>
  <p>Hello,</p>
  <p>A new Purchase Order has been created:</p>
  <ul>
    <li><strong>PO Number:</strong> <?php echo htmlspecialchars($po_number ?? ''); ?></li>
    <li><strong>PR Number:</strong> <?php echo htmlspecialchars($pr_number ?? ''); ?></li>
    <li><strong>Created By:</strong> <?php echo htmlspecialchars($created_by ?? ''); ?></li>
  </ul>
  <p>Regards,<br/>Material Tracker</p>
</div>
