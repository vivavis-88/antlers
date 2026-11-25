<?php

declare(strict_types=1);

class Mailer
{
    private array $config;

    public function __construct()
    {
        $configFile = dirname(__DIR__, 2) . '/config/mail.php';
        $this->config = file_exists($configFile) ? (require $configFile) : [];
    }

    public function isConfigured(): bool
    {
        return trim((string) ($this->config['smtp_user'] ?? '')) !== ''
            && trim((string) ($this->config['smtp_pass'] ?? '')) !== '';
    }

    public function sendRiderApprovalEmail(string $toEmail, string $fullName, string $riderCode, string $adminNote = ''): array
    {
        $loginUrl = $this->config['app_url'] ?? 'http://localhost/Endterm/public/index.php?page=auth';
        $subject = 'MEMOLOG: Your Rider Application Has Been Approved!';

        $noteHtml = $adminNote !== ''
            ? '<p style="margin:14px 0;padding:12px 14px;background:#f1f5f9;border-left:3px solid #16a34a;border-radius:6px;color:#334155;"><strong>Admin Note:</strong> ' . htmlspecialchars($adminNote) . '</p>'
            : '';

        $htmlBody = '<!DOCTYPE html><html><body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">'
            . '<div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">'
            . '<div style="background:#0f172a;color:#ffffff;padding:22px 26px;">'
            . '<h1 style="margin:0;font-size:20px;letter-spacing:0.04em;">MEMOLOG</h1>'
            . '<p style="margin:4px 0 0;font-size:13px;color:#94a3b8;">Delivery Auditing &amp; Rider Earnings</p>'
            . '</div>'
            . '<div style="padding:26px;">'
            . '<h2 style="margin:0 0 12px;font-size:18px;color:#15803d;">Application Approved</h2>'
            . '<p style="margin:0 0 12px;line-height:1.6;">Hi <strong>' . htmlspecialchars($fullName) . '</strong>,</p>'
            . '<p style="margin:0 0 14px;line-height:1.6;">Great news! Your rider application for <strong>MEMOLOG</strong> has been reviewed and approved by the Administrator. Your rider account is now active.</p>'
            . '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;margin:16px 0;">'
            . '<p style="margin:0 0 6px;font-size:13px;color:#475569;">Assigned Rider Code: <strong style="color:#0f172a;">' . htmlspecialchars($riderCode) . '</strong></p>'
            . '<p style="margin:0;font-size:13px;color:#475569;">Login Email: <strong style="color:#0f172a;">' . htmlspecialchars($toEmail) . '</strong></p>'
            . '</div>'
            . $noteHtml
            . '<p style="margin:18px 0 22px;line-height:1.6;">Sign in using the email and password you registered with to view your assigned deliveries, track your daily quota, verify items, and monitor your earnings.</p>'
            . '<a href="' . htmlspecialchars($loginUrl) . '" style="display:inline-block;background:#0f172a;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold;font-size:14px;">Sign In to MEMOLOG</a>'
            . '</div>'
            . '<div style="padding:16px 26px;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;">This is an automated message from MEMOLOG Delivery Operations.</div>'
            . '</div></body></html>';

        return $this->send($toEmail, $fullName, $subject, $htmlBody);
    }

    public function sendRiderRejectionEmail(string $toEmail, string $fullName, string $reason): array
    {
        $subject = 'MEMOLOG: Update on Your Rider Application';

        $htmlBody = '<!DOCTYPE html><html><body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">'
            . '<div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">'
            . '<div style="background:#0f172a;color:#ffffff;padding:22px 26px;">'
            . '<h1 style="margin:0;font-size:20px;letter-spacing:0.04em;">MEMOLOG</h1>'
            . '<p style="margin:4px 0 0;font-size:13px;color:#94a3b8;">Delivery Auditing &amp; Rider Earnings</p>'
            . '</div>'
            . '<div style="padding:26px;">'
            . '<h2 style="margin:0 0 12px;font-size:18px;color:#b91c1c;">Application Not Approved</h2>'
            . '<p style="margin:0 0 12px;line-height:1.6;">Hi <strong>' . htmlspecialchars($fullName) . '</strong>,</p>'
            . '<p style="margin:0 0 14px;line-height:1.6;">Thank you for applying to be a rider with MEMOLOG. After reviewing your application, the Administrator was unable to approve it at this time.</p>'
            . '<p style="margin:14px 0;padding:12px 14px;background:#fef2f2;border-left:3px solid #dc2626;border-radius:6px;color:#7f1d1d;"><strong>Reason:</strong> ' . htmlspecialchars($reason) . '</p>'
            . '<p style="margin:16px 0 0;line-height:1.6;color:#475569;">If you believe this was in error, please contact the MEMOLOG Operations Administrator.</p>'
            . '</div>'
            . '<div style="padding:16px 26px;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;">This is an automated message from MEMOLOG Delivery Operations.</div>'
            . '</div></body></html>';

        return $this->send($toEmail, $fullName, $subject, $htmlBody);
    }

    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): array
    {
        if (!$this->isConfigured()) {
            $this->logLocalEmail($toEmail, $toName, $subject, $htmlBody, 'SIMULATED (Gmail SMTP not configured in config/mail.php)');
            return [
                'sent' => false,
                'simulated' => true,
                'message' => 'Gmail notification logged to storage/mail_log.txt (add your Gmail + App Password in config/mail.php to send live emails).',
            ];
        }

        $host = (string) ($this->config['smtp_host'] ?? 'smtp.gmail.com');
        $port = (int) ($this->config['smtp_port'] ?? 587);
        $encryption = strtolower((string) ($this->config['smtp_encryption'] ?? 'tls'));
        $username = trim((string) $this->config['smtp_user']);
        $password = str_replace(' ', '', trim((string) $this->config['smtp_pass']));
        $fromEmail = trim((string) ($this->config['from_email'] ?? '')) ?: $username;
        $fromName = trim((string) ($this->config['from_name'] ?? 'MEMOLOG'));

        try {
            $transport = ($encryption === 'ssl') ? "ssl://{$host}" : $host;
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
            ]);

            $socket = @stream_socket_client("{$transport}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
            if (!$socket) {
                throw new RuntimeException("SMTP connection failed: {$errstr} ({$errno})");
            }
            stream_set_timeout($socket, 15);

            $this->expectCode($socket, [220]);
            $this->sendCommand($socket, 'EHLO localhost', [250]);

            if ($encryption === 'tls') {
                $this->sendCommand($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Failed to start TLS encryption with Gmail SMTP.');
                }
                $this->sendCommand($socket, 'EHLO localhost', [250]);
            }

            $this->sendCommand($socket, 'AUTH LOGIN', [334]);
            $this->sendCommand($socket, base64_encode($username), [334]);
            $this->sendCommand($socket, base64_encode($password), [235]);

            $this->sendCommand($socket, "MAIL FROM:<{$fromEmail}>", [250]);
            $this->sendCommand($socket, "RCPT TO:<{$toEmail}>", [250, 251]);
            $this->sendCommand($socket, 'DATA', [354]);

            $safeFromName = str_replace(["\r", "\n"], '', $fromName);
            $safeToName = str_replace(["\r", "\n"], '', $toName);
            $safeSubject = '=?UTF-8?B?' . base64_encode(str_replace(["\r", "\n"], '', $subject)) . '?=';

            $headers = [
                "Date: " . date('r'),
                "From: {$safeFromName} <{$fromEmail}>",
                "To: {$safeToName} <{$toEmail}>",
                "Subject: {$safeSubject}",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "Content-Transfer-Encoding: base64",
            ];

            $payload = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.";
            fwrite($socket, $payload . "\r\n");
            $this->expectCode($socket, [250]);

            $this->sendCommand($socket, 'QUIT', [221]);
            fclose($socket);

            $this->logLocalEmail($toEmail, $toName, $subject, $htmlBody, 'SENT via Gmail SMTP');
            return [
                'sent' => true,
                'simulated' => false,
                'message' => "Gmail notification sent to {$toEmail}.",
            ];
        } catch (Throwable $e) {
            error_log('Mailer SMTP error: ' . $e->getMessage());
            $this->logLocalEmail($toEmail, $toName, $subject, $htmlBody, 'FAILED: ' . $e->getMessage());
            return [
                'sent' => false,
                'simulated' => false,
                'message' => 'Account action saved, but Gmail email failed to send (' . $e->getMessage() . '). Logged in storage/mail_log.txt.',
            ];
        }
    }

    private function sendCommand($socket, string $command, array $expectedCodes): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expectCode($socket, $expectedCodes);
    }

    private function expectCode($socket, array $expectedCodes): void
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('Unexpected SMTP response: ' . trim($response));
        }
    }

    private function logLocalEmail(string $toEmail, string $toName, string $subject, string $htmlBody, string $status): void
    {
        $dir = dirname(__DIR__, 2) . '/storage';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $entry = str_repeat('=', 70) . "\n"
            . "Timestamp: " . date('Y-m-d H:i:s') . "\n"
            . "Status:    {$status}\n"
            . "To:        {$toName} <{$toEmail}>\n"
            . "Subject:   {$subject}\n"
            . "Body:\n" . trim(strip_tags(str_replace(['<br>', '<br/>', '</p>', '</div>', '</h1>', '</h2>'], "\n", $htmlBody))) . "\n\n";
        @file_put_contents($dir . '/mail_log.txt', $entry, FILE_APPEND);
    }
}
