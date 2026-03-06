<?php
// src/core/session.php
require_once __DIR__ . '/../../config/config.php';

class SessionHelper {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = SESSION_LIFETIME;
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => (APP_ENV === 'production'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            }
            // Regenerate session id periodically
            if (time() - $_SESSION['created'] > ($lifetime / 2)) {
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }

    public static function login($userId) {
        $_SESSION['user_id'] = (int)$userId;
        $_SESSION['logged_in_at'] = time();
    }

    public static function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }
}

?>