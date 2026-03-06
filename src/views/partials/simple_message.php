<div class="p-4">
  <h2><?php echo htmlspecialchars($title ?? 'Message'); ?></h2>
  <p><?php echo htmlspecialchars($message ?? ''); ?></p>
  <p><a href="<?php echo function_exists('base_path') ? base_path() : '/'; ?>">Back</a></p>
</div>
