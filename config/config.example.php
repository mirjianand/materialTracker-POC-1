<?php
// config/config.example.php
// Copy this file to config.php and update secrets. DO NOT commit real credentials.

// Database (fill with your DB credentials)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'mat_tracker');
define('DB_USER', 'db_user_here');
define('DB_PASS', 'change_this_password');
define('DB_CHARSET', 'utf8mb4');

// LDAP (optional)
define('LDAP_HOST', 'ldap://ldap.example.org');
define('LDAP_BASE_DN', 'dc=example,dc=org');
define('LDAP_USER_DN', 'ou=users,dc=example,dc=org');

// Uploads
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024);
define('UPLOAD_ALLOWED_TYPES', json_encode(['application/pdf', 'image/jpeg']));
define('UPLOAD_DIR', __DIR__ . '/../uploads');

// Session and environment
define('SESSION_LIFETIME', 30 * 60);
define('APP_ENV', 'production');

// Mail - replace with SMTP credentials or leave empty if not used
define('SMTP_HOST', 'smtp.example.org');
define('SMTP_PORT', 587);
define('SMTP_USER', 'smtp_user');
define('SMTP_PASS', 'smtp_password');
define('SMTP_SECURE', 'tls');
define('MAIL_FROM', 'noreply@example.org');
define('MAIL_FROM_NAME', 'Material Tracker');

// When running in web context, determine base path so redirects and links work
if (PHP_SAPI !== 'cli') {
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    define('BASE_PATH', $scriptDir === '' ? '' : $scriptDir);
} else {
    define('BASE_PATH', '');
}

// Development convenience: copy to config.php and set APP_ENV to 'development' for local debugging

?>
