<?php
// scripts/run_test_email.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/core/session.php';

SessionHelper::start();

require_once __DIR__ . '/../src/controllers/TestController.php';

$controller = new TestController();
$output = $controller->sendEmail();

// Print the rendered HTML for quick feedback
echo $output . "\n";
