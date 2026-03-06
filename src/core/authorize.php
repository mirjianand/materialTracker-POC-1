<?php
// src/core/authorize.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

class Authorize {
    // Check whether current user has the named permission
    public static function check($permissionName) {
        SessionHelper::start();
        $uid = SessionHelper::userId();
        if (!$uid) return false;

        // Fetch user's role from users table
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT role FROM users WHERE id = :id');
        $stmt->execute([':id' => $uid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        $role = $row['role'];

        // Check role_permissions table
        $stmt2 = $db->prepare('SELECT 1 FROM role_permissions WHERE role_name = :role AND permission_name = :perm LIMIT 1');
        $stmt2->execute([':role' => $role, ':perm' => $permissionName]);
        $found = $stmt2->fetchColumn();
        return (bool)$found;
    }

    // Convenience wrapper
    public static function authorize($permissionName) {
        if (!self::check($permissionName)) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }
    }

    // Require that current user has a specific role (simple role check)
    public static function requireRole(string $roleName) {
        // If running CLI, allow (so scripts won't be blocked)
        if (PHP_SAPI === 'cli') return true;

        SessionHelper::start();
        $uid = SessionHelper::userId();
        if (!$uid) {
            header('Location: ' . (function_exists('base_path') ? base_path('login') : '/login'));
            exit;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT role FROM users WHERE id = :id');
        $stmt->execute([':id' => $uid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $userRole = $row['role'] ?? null;
        // Admins have all rights: allow if user role matches required OR user is Admin
        if ($userRole !== $roleName && $userRole !== 'Admin') {
            // Friendly redirect for web users
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Access denied: Admins only'];
            $forbiddenUrl = (function_exists('base_path') ? base_path('forbidden') : '/forbidden');
            header('Location: ' . $forbiddenUrl);
            exit;
        }
    }
}

?>