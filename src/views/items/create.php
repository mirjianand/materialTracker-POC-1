<div class="p-4">
  <h2>Item Creation</h2>
  <?php if (!empty($categories) || !empty($item_types) || !empty($material_types)) : ?>
    <div class="mb-5">
      <h4 class="mb-3">Create New Item</h4>
      <form method="post" action="<?php echo function_exists('base_path') ? base_path('items/create') : '/items/create'; ?>">
        <?php echo CSRF::inputField(); ?>
        <div class="mb-3">
          <label class="form-label">Item Code</label>
          <input name="item_code" class="form-control" required maxlength="64" />
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
              <option value="">-- none --</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Item Type</label>
            <select name="item_type_id" class="form-select">
              <option value="">-- none --</option>
              <?php foreach ($item_types as $t): ?>
                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Material Type</label>
            <select name="material_type_id" class="form-select">
              <option value="">-- none --</option>
              <?php foreach ($material_types as $m): ?>
                <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Quantity Type</label>
          <select name="quantity_type" class="form-select">
            <option value="Number">Number</option>
            <option value="Batch">Batch</option>
          </select>
        </div>
        <button class="btn btn-primary" type="submit">Create Item</button>
      </form>
    </div>

    <hr />

    <div>
      <h4 class="mb-3">Existing Items</h4>
      <div class="mb-3">
        <form class="row g-2" method="get" action="<?php echo function_exists('base_path') ? base_path('items/create') : '/items/create'; ?>">
          <div class="col-md-4">
            <input class="form-control" name="item_q" placeholder="Search items" value="<?php echo htmlspecialchars($itemQ ?? ''); ?>" />
          </div>
          <div class="col-md-3">
            <select name="item_category" class="form-select">
              <option value="">All categories</option>
              <?php foreach (($categories ?? []) as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php if (($itemCategory ?? '') == $c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <select name="item_type" class="form-select">
              <option value="">All types</option>
              <?php foreach (($item_types ?? []) as $t): ?>
                <option value="<?php echo $t['id']; ?>" <?php if (($itemType ?? ($itemTypeFilter ?? '')) == $t['id']) echo 'selected'; ?>><?php echo htmlspecialchars($t['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <select name="item_material" class="form-select">
              <option value="">All materials</option>
              <?php foreach (($material_types ?? []) as $m): ?>
                <option value="<?php echo $m['id']; ?>" <?php if (($itemMaterial ?? ($itemMaterialFilter ?? '')) == $m['id']) echo 'selected'; ?>><?php echo htmlspecialchars($m['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <select name="item_sort" class="form-select">
              <option value="item_code" <?php if (($itemSort ?? '')=='item_code') echo 'selected'; ?>>Sort: Code</option>
              <option value="description" <?php if (($itemSort ?? '')=='description') echo 'selected'; ?>>Sort: Description</option>
              <option value="quantity_type" <?php if (($itemSort ?? '')=='quantity_type') echo 'selected'; ?>>Sort: Qty Type</option>
            </select>
          </div>
          <div class="col-md-1">
            <select name="item_order" class="form-select">
              <option value="desc" <?php if (($itemOrder ?? '')=='DESC') echo 'selected'; ?>>Desc</option>
              <option value="asc" <?php if (($itemOrder ?? '')=='ASC') echo 'selected'; ?>>Asc</option>
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary">Filter</button>
          </div>
        </form>
      </div>

      <?php if (!empty($items)): ?>
        <div class="mb-2">Showing <?php echo (($itemPage-1)*$itemPageSize)+1; ?> - <?php echo min($itemTotal, $itemPage*$itemPageSize); ?> of <?php echo $itemTotal; ?></div>
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Code</th>
              <th>Description</th>
              <th>Category</th>
              <th>Type</th>
              <th>Material</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
            <tr>
              <td><?php echo htmlspecialchars($it['item_code']); ?></td>
              <td><?php echo htmlspecialchars($it['description'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($it['category_name'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($it['item_type_name'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($it['material_type_name'] ?? ''); ?></td>
              <td><a class="btn btn-sm btn-outline-primary" href="<?php echo function_exists('base_path') ? base_path('items/edit?id='.urlencode($it['id'])) : '/items/edit?id='.urlencode($it['id']); ?>">Edit</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($itemTotal > $itemPageSize): ?>
          <?php $pages = (int)ceil($itemTotal / $itemPageSize); ?>
          <nav>
            <ul class="pagination">
              <?php for ($p=1;$p<=$pages;$p++): ?>
                <li class="page-item <?php if ($p==$itemPage) echo 'active'; ?>">
                  <a class="page-link" href="<?php echo (function_exists('base_path') ? base_path('items/create?item_page='.$p.'&item_page_size='.$itemPageSize.'&item_q='.urlencode($itemQ ?? '').'&item_category='.urlencode($itemCategory ?? '').'&item_sort='.urlencode($itemSort ?? '').'&item_order='.urlencode(strtolower($itemOrder ?? '')) ) : '/items/create?item_page='.$p.'&item_page_size='.$itemPageSize); ?>"><?php echo $p; ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
          <div class="row g-2 mt-2">
            <div class="col-auto">
              <form method="get" action="<?php echo function_exists('base_path') ? base_path('items/create') : '/items/create'; ?>">
                <label class="form-label">Items per page</label>
                <select name="item_page_size" class="form-select" onchange="this.form.submit()">
                  <?php foreach ([5,10,25,50,100] as $sz): ?>
                    <option value="<?php echo $sz; ?>" <?php if ($itemPageSize==$sz) echo 'selected'; ?>><?php echo $sz; ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="hidden" name="item_q" value="<?php echo htmlspecialchars($itemQ ?? ''); ?>" />
                <input type="hidden" name="item_category" value="<?php echo htmlspecialchars($itemCategory ?? ''); ?>" />
                <input type="hidden" name="item_sort" value="<?php echo htmlspecialchars($itemSort ?? 'item_code'); ?>" />
                <input type="hidden" name="item_order" value="<?php echo htmlspecialchars(strtolower($itemOrder ?? 'desc')); ?>" />
              </form>
            </div>
            <div class="col-auto">
              <form method="get" action="<?php echo function_exists('base_path') ? base_path('items/create') : '/items/create'; ?>" class="d-flex">
                <label class="form-label me-2">Jump to page</label>
                <input type="number" name="item_page" min="1" max="<?php echo $pages; ?>" class="form-control me-2" style="width:100px" value="<?php echo $itemPage; ?>" />
                <input type="hidden" name="item_page_size" value="<?php echo $itemPageSize; ?>" />
                <input type="hidden" name="item_q" value="<?php echo htmlspecialchars($itemQ ?? ''); ?>" />
                <input type="hidden" name="item_category" value="<?php echo htmlspecialchars($itemCategory ?? ''); ?>" />
                <input type="hidden" name="item_sort" value="<?php echo htmlspecialchars($itemSort ?? 'item_code'); ?>" />
                <input type="hidden" name="item_order" value="<?php echo htmlspecialchars(strtolower($itemOrder ?? 'desc')); ?>" />
                <button class="btn btn-outline-primary">Go</button>
              </form>
            </div>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <p>No items available.</p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <p>No category/type data available. Populate `item_categories`, `item_types`, and `material_types` first.</p>
  <?php endif; ?>
</div>
