<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2>Login</h2>
            <?php if (!empty($errors)) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $e) : ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars(function_exists('base_path') ? base_path('auth/login') : '/auth/login'); ?>">
                <?php echo CSRF::inputField(); ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" required />
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required />
                </div>
                <button class="btn btn-primary" type="submit">Login</button>
            </form>
        </div>
    </div>
</div>
