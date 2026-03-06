<?php
// src/core/auth.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../models/User.php';

class Auth {
    public static function user() {
        SessionHelper::start();
        $uid = SessionHelper::userId();
        if (!$uid) return null;
        $u = new User();
        if ($u->read_single($uid)) {
            // Return user properties as associative array for simple use
            return [
                'id' => $u->id,
                'emp_id' => $u->emp_id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'is_active' => $u->is_active,
            ];
        }
        return null;
    }

    public static function requireLogin() {
        if (!SessionHelper::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
}

?>