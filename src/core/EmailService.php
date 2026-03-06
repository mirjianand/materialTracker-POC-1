<?php
// src/core/EmailService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class EmailService {
    protected $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        // Configure SMTP using config constants
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->Port = SMTP_PORT;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USER;
        $this->mailer->Password = SMTP_PASS;
        if (defined('SMTP_SECURE') && SMTP_SECURE !== '') {
            $this->mailer->SMTPSecure = SMTP_SECURE;
        }
        $this->mailer->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $this->mailer->isHTML(true);
    }

    /**
     * Send an email. Returns ['success' => bool, 'error' => string|null]
     */
    public function send(string|array $to, string $subject, string $body, array $attachments = [], array $cc = []): array
    {
        $error = null;
        $success = false;
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearAttachments();

            $tos = is_array($to) ? $to : [$to];
            foreach ($tos as $addr) {
                $this->mailer->addAddress($addr);
            }

            foreach ($cc as $caddr) {
                $this->mailer->addCC($caddr);
            }

            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            foreach ($attachments as $att) {
                if (is_array($att) && isset($att['path'])) {
                    $this->mailer->addAttachment($att['path'], $att['name'] ?? '');
                } elseif (is_string($att)) {
                    $this->mailer->addAttachment($att);
                }
            }

            $this->mailer->send();
            $success = true;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $success = false;
        }

        // Attempt to log the result to email_logs table
        try {
            require_once __DIR__ . '/db.php';
            $db = Database::getInstance()->getConnection();
            $now = date('Y-m-d H:i:s');

            // Log a row for each recipient and CC
            $allRecipients = is_array($to) ? $to : [$to];
            $allRecipients = array_merge($allRecipients, $cc);

            $stmt = $db->prepare('INSERT INTO email_logs (recipient_email, subject, body, sent_at, status, error_message) VALUES (:r,:s,:b,:t,:st,:err)');
            foreach ($allRecipients as $r) {
                $stmt->execute([
                    ':r' => $r,
                    ':s' => $subject,
                    ':b' => $body,
                    ':t' => $now,
                    ':st' => ($success ? 'sent' : 'failed'),
                    ':err' => $error
                ]);
            }
        } catch (Exception $logEx) {
            error_log('Failed to log email: ' . $logEx->getMessage());
        }

        return ['success' => $success, 'error' => $error];
    }

    // Render a template from src/views/emails/<name>.php with $data
    public function renderTemplate(string $templateName, array $data = []): string
    {
        $tpl = __DIR__ . '/../views/emails/' . $templateName . '.php';
        if (!file_exists($tpl)) {
            // fallback to simple body
            return $data['body'] ?? '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $tpl;
        return ob_get_clean();
    }

    // Send a templated email to multiple recipients
    public function sendTemplate(array $recipients, string $subject, string $templateName, array $data = [], array $attachments = []): array
    {
        $body = $this->renderTemplate($templateName, $data);
        // send as a single message to all recipients (no CC by default)
        return ['result' => $this->send($recipients, $subject, $body, $attachments, [])];
    }

    // Send to a role using ROLE_RECIPIENTS mapping (from config)
    public function sendToRole(string $role, string $subject, string $templateName, array $data = [], array $attachments = []): array
    {
        // Resolve recipients: first from DB table notification_recipients, fallback to config mapping
        $recips = [];
        try {
            require_once __DIR__ . '/db.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT email FROM notification_recipients WHERE role_name = :r');
            $stmt->execute([':r' => $role]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                if (!empty($row['email'])) $recips[] = $row['email'];
            }
        } catch (Exception $e) {
            // ignore DB errors and fallback to config
        }

        if (empty($recips)) {
            global $ROLE_RECIPIENTS;
            $recips = $ROLE_RECIPIENTS[$role] ?? [];
        }

        if (empty($recips)) return [];

        // Always CC logistics and all Admin users
        $cc = ['logistics@c2techsolutions.com'];
        try {
            require_once __DIR__ . '/db.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT email FROM users WHERE role = 'Admin' AND email IS NOT NULL AND email != ''");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                if (!empty($row['email'])) $cc[] = $row['email'];
            }
        } catch (Exception $e) {
            // ignore
        }

        $body = $this->renderTemplate($templateName, $data);
        return ['result' => $this->send($recips, $subject, $body, $attachments, $cc)];
    }
}
