<?php
// tests/bootstrap.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
// Start output buffering early to avoid headers already sent
if (!ob_get_level()) ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Provide minimal SERVER variables expected by views/controllers during tests
if (empty($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '/';
if (empty($_SERVER['SCRIPT_NAME'])) $_SERVER['SCRIPT_NAME'] = '/index.php';
if (empty($_SERVER['REQUEST_METHOD'])) $_SERVER['REQUEST_METHOD'] = 'GET';
