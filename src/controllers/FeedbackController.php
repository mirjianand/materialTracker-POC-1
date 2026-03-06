<?php
// src/controllers/FeedbackController.php
require_once __DIR__ . '/BaseController.php';

class FeedbackController extends BaseController {
    private $emailer;

    public function __construct($emailer = null)
    {
        $this->emailer = $emailer;
    }

    public function index() {
        return $this->render('feedback', ['title' => 'Feedback']);
    }

    public function send() {
        SessionHelper::start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (function_exists('base_path') ? base_path('feedback') : '/feedback'));
            if (PHP_SAPI !== 'cli') exit;
            return;
        }

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($subject === '' || $message === '') {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Subject and message are required'];
            header('Location: ' . (function_exists('base_path') ? base_path('feedback') : '/feedback'));
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $now = date('Y-m-d H:i:s');

        // Use injected emailer when provided (tests can inject a mock)
        if ($this->emailer !== null) {
            $emailer = $this->emailer;
        } else {
            require_once __DIR__ . '/../core/EmailService.php';
            $emailer = new EmailService();
        }
        $result = $emailer->send('admin@example.org', $subject, nl2br(htmlspecialchars($message)));

        try {
            $stmt = $db->prepare('INSERT INTO email_logs (recipient_email, subject, body, sent_at, status, error_message) VALUES (:r,:s,:b,:t,:st,:err)');
            $stmt->execute([':r'=>'admin@example.org', ':s'=>$subject, ':b'=>$message, ':t'=>$now, ':st'=>($result['success'] ? 'sent' : 'failed'), ':err'=>$result['error']]);
            $_SESSION['flash'] = ['type'=>($result['success'] ? 'success' : 'danger'),'message'=>($result['success'] ? 'Feedback sent. Thank you.' : 'Failed to send feedback. Please try again later.')];
        } catch (PDOException $ex) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Failed to record feedback. Please contact admin.'];
        }
        header('Location: ' . (function_exists('base_path') ? base_path('feedback') : '/feedback'));
        if (PHP_SAPI !== 'cli') exit;
        return;
    }
}

?>