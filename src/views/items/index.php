<div class="p-4">
  <h2>Items</h2>
  <form class="row g-2 mb-3" method="get" action="<?php echo function_exists('base_path') ? base_path('items') : '/items'; ?>">
    <div class="col-md-4">
      <input type="text" name="q" value="<?php echo htmlspecialchars($q ?? ''); ?>" class="form-control" placeholder="Search code or description" />
    </div>
    <div class="col-md-3">
      <select name="category" class="form-select">
        <option value="">All categories</option>
        <?php foreach (($categories ?? []) as $c): ?>
          <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="item_type" class="form-select">
        <option value="">All types</option>
        <?php foreach (($item_types ?? []) as $t): ?>
          <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="material_type" class="form-select">
        <option value="">All materials</option>
        <?php foreach (($material_types ?? []) as $m): ?>
          <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Search</button>
      <a class="btn btn-secondary" href="<?php echo function_exists('base_path') ? base_path('items') : '/items'; ?>">Reset</a>
    </div>
    <div class="col-auto ms-auto text-end">
      <a class="btn btn-sm btn-success" href="<?php echo function_exists('base_path') ? base_path('items/create') : '/items/create'; ?>">Create Item</a>
    </div>
  </form>

  <?php if (!empty($items)): ?>
    <div class="mb-2">Showing <?php echo (($page-1)*$pageSize)+1; ?> - <?php echo min($total, $page*$pageSize); ?> of <?php echo $total; ?></div>
    <table class="table table-sm">
      <thead>
        <tr>
          <th>Item Code</th>
          <th>Description</th>
          <th>Category</th>
          <th>Type</th>
          <th>Material</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><?php echo htmlspecialchars($it['item_code']); ?></td>
          <td><?php echo htmlspecialchars($it['description'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['category_name'] ?? ($it['category_id'] ?? '')); ?></td>
          <td><?php echo htmlspecialchars($it['item_type_name'] ?? ($it['item_type_id'] ?? '')); ?></td>
          <td><?php echo htmlspecialchars($it['material_type_name'] ?? ($it['material_type_id'] ?? '')); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if ($total > $pageSize): ?>
      <?php $pages = (int)ceil($total / $pageSize); ?>
      <nav>
        <ul class="pagination">
          <?php for ($p=1;$p<=$pages;$p++): ?>
            <li class="page-item <?php if ($p==$page) echo 'active'; ?>"><a class="page-link" href="<?php echo (function_exists('base_path') ? base_path('items?page='.$p.'&q='.urlencode($q)) : '/items?page='.$p.'&q='.urlencode($q)); ?>"><?php echo $p; ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php else: ?>
    <p>No items found.</p>
  <?php endif; ?>
</div>
