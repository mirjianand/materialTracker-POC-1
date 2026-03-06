<div class="p-4">
  <h2>Edit Item</h2>
  <form method="post" action="<?php echo function_exists('base_path') ? base_path('items/edit?id='.urlencode($item['id'])) : '/items/edit?id='.urlencode($item['id']); ?>">
    <?php echo CSRF::inputField(); ?>
    <div class="mb-3">
      <label class="form-label">Item Code</label>
      <input name="item_code" class="form-control" required maxlength="64" value="<?php echo htmlspecialchars($item['item_code'] ?? ''); ?>" />
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
          <option value="">-- none --</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php if (($item['category_id'] ?? '') == $c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Item Type</label>
        <select name="item_type_id" class="form-select">
          <option value="">-- none --</option>
          <?php foreach ($item_types as $t): ?>
            <option value="<?php echo $t['id']; ?>" <?php if (($item['item_type_id'] ?? '') == $t['id']) echo 'selected'; ?>><?php echo htmlspecialchars($t['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Material Type</label>
        <select name="material_type_id" class="form-select">
          <option value="">-- none --</option>
          <?php foreach ($material_types as $m): ?>
            <option value="<?php echo $m['id']; ?>" <?php if (($item['material_type_id'] ?? '') == $m['id']) echo 'selected'; ?>><?php echo htmlspecialchars($m['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Quantity Type</label>
      <select name="quantity_type" class="form-select">
        <option value="Number" <?php if (($item['quantity_type'] ?? '')=='Number') echo 'selected'; ?>>Number</option>
        <option value="Batch" <?php if (($item['quantity_type'] ?? '')=='Batch') echo 'selected'; ?>>Batch</option>
      </select>
    </div>
    <div>
      <button class="btn btn-primary" type="submit">Save Changes</button>
      <a class="btn btn-secondary" href="<?php echo function_exists('base_path') ? base_path('items') : '/items'; ?>">Back to list</a>
    </div>
  </form>
</div>
