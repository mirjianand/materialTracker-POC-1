<?php
// src/controllers/TestController.php
require_once __DIR__ . '/BaseController.php';

class TestController extends BaseController {
    public function sendEmail() {
        // simple test endpoint to send a notification to TEST_NOTIFICATION_EMAIL
        require_once __DIR__ . '/../core/EmailService.php';
        $emailer = new EmailService();
        $subject = 'Test Notification from Material Tracker';
        $body = 'This is a test notification sent at ' . date('Y-m-d H:i:s');
        $result = $emailer->send(TEST_NOTIFICATION_EMAIL, $subject, $body);
        $status = $result['success'] ? 'sent' : 'failed: ' . $result['error'];
        return $this->render('partials/simple_message', ['title' => 'Test Email', 'message' => 'Test email ' . $status]);
    }
}
