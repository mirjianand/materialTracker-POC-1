<?php
// src/core/ldap.php
require_once __DIR__ . '/../../config/config.php';

class LDAPAuth {
    private $connection;

    public function __construct() {
        $this->connection = null;
    }

    public function connect() {
        if (!function_exists('ldap_connect')) {
            // LDAP extension not installed
            return false;
        }

        $this->connection = @ldap_connect(LDAP_HOST);
        if ($this->connection) {
            @ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            @ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
            return true;
        }
        return false;
    }

    public function authenticate($username, $password) {
        if (!$this->connect()) {
            return false;
        }

        $user_dn = sprintf('uid=%s,%s', ldap_escape($username, '', LDAP_ESCAPE_DN), LDAP_USER_DN . ',' . LDAP_BASE_DN);

        // Attempt bind as the user
        $bound = @ldap_bind($this->connection, $user_dn, $password);
        if ($bound) {
            // Optionally fetch attributes, groups etc.
            return true;
        }
        return false;
    }

    public function close() {
        if ($this->connection) {
            @ldap_unbind($this->connection);
            $this->connection = null;
        }
    }
}

// Helper: ldap_escape (PHP < 8.1 compatibility)
if (!function_exists('ldap_escape')) {
    function ldap_escape($str, $ignore = '', $flags = 0) {
        return addcslashes($str, '\\,=+<>#;"');
    }
}

?>