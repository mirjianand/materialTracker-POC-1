<div>
  <p>Hello,</p>
  <p>A new item has been created:</p>
  <ul>
    <li><strong>Item Code:</strong> <?php echo htmlspecialchars($item_code ?? ''); ?></li>
    <li><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($description ?? '')); ?></li>
    <li><strong>Category:</strong> <?php echo htmlspecialchars($category_name ?? ''); ?></li>
    <li><strong>Material Type:</strong> <?php echo htmlspecialchars($material_type_name ?? ''); ?></li>
  </ul>
  <p>Regards,<br/>Material Tracker</p>
</div>
