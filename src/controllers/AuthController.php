<?php
// src/controllers/AuthController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/ldap.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends BaseController {
    public function loginForm($errors = []) {
        return $this->render('login', ['errors' => $errors]);
    }

    public function login() {
        SessionHelper::start();
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $token = $_POST['csrf_token'] ?? '';

        if (!CSRF::validate($token)) {
            return $this->loginForm(['Invalid CSRF token']);
        }

        // First, try local password-based authentication (users.password_hash)
        try {
            $userModel = new User();
            $stmt = $userModel->getConnection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['password_hash']) && password_verify($password, $row['password_hash'])) {
                $userModel->read_single($row['id']);
                SessionHelper::login($userModel->id);
                header('Location: ' . (function_exists('base_path') ? base_path('/') : '/'));
                exit;
            }
        } catch (Exception $e) {
            // ignore and continue to LDAP / dev fallback
        }

        $ldap = new LDAPAuth();
        $auth_ok = $ldap->authenticate($username, $password);

        if ($auth_ok) {
            // Map LDAP user to local user record
            $userModel = new User();
            // Try find by emp_id then by email
            $found = false;
            $stmt = $userModel->read();
            while ($row = $stmt->fetch()) {
                if (strcasecmp($row['emp_id'], $username) === 0 || strcasecmp($row['email'], $username) === 0) {
                    $userModel->read_single($row['id']);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // Create minimal local user record (default role: User)
                $userModel->emp_id = $username;
                $userModel->name = $username;
                $userModel->email = $username;
                $userModel->role = 'User';
                $userModel->is_active = 1;
                $userModel->create();
                // Re-fetch newest user id
                $stmt2 = $userModel->getConnection()->query('SELECT LAST_INSERT_ID() AS id');
                $rid = $stmt2->fetch();
                $userModel->read_single($rid['id']);
            }

            SessionHelper::login($userModel->id);
            header('Location: ' . (function_exists('base_path') ? base_path('/') : '/'));
            exit;
        }

        // Local password-based fallback: check users table for a password_hash
        try {
            $userModel = new User();
            $stmt = $userModel->getConnection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['password_hash'])) {
                if (password_verify($password, $row['password_hash'])) {
                    $userModel->read_single($row['id']);
                    SessionHelper::login($userModel->id);
                    header('Location: ' . (function_exists('base_path') ? base_path('/') : '/'));
                    exit;
                }
            }
        } catch (Exception $e) {
            // ignore and fall through to dev fallback
        }

        // Development fallback: allow a demo login without LDAP when running locally
        if (defined('APP_ENV') && APP_ENV === 'development' &&
            defined('DEV_DEMO_USER') && defined('DEV_DEMO_PASS') &&
            $username === DEV_DEMO_USER && $password === DEV_DEMO_PASS) {

            $userModel = new User();
            // Attempt to find by email
            $stmt = $userModel->conn->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $userModel->read_single($row['id']);
            } else {
                // Create a demo LogisticsManager user if not found
                $userModel->emp_id = 'demo';
                $userModel->name = 'Demo User';
                $userModel->email = $username;
                $userModel->role = 'LogisticsManager';
                $userModel->is_active = 1;
                $userModel->create();
                $stmt2 = $userModel->getConnection()->query('SELECT LAST_INSERT_ID() AS id');
                $rid = $stmt2->fetch();
                $userModel->read_single($rid['id']);
            }

            SessionHelper::login($userModel->id);
            header('Location: ' . (function_exists('base_path') ? base_path('/') : '/'));
            exit;
        }

        return $this->loginForm(['Invalid credentials']);
    }

    public function logout() {
        SessionHelper::start();
        SessionHelper::logout();
        header('Location: ' . (function_exists('base_path') ? base_path('login') : '/login'));
        exit;
    }
}

?>