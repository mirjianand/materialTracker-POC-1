<div class="p-4">
  <h2>Feedback</h2>
  <form method="post" action="<?php echo function_exists('base_path') ? base_path('feedback/send') : '/feedback/send'; ?>">
    <?php echo CSRF::inputField(); ?>
    <div class="mb-3">
      <label class="form-label">Subject</label>
      <input name="subject" class="form-control" />
    </div>
    <div class="mb-3">
      <label class="form-label">Message</label>
      <textarea name="message" class="form-control" rows="6"></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Send Feedback</button>
  </form>
</div>
