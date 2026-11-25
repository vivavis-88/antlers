<?php

declare(strict_types=1);

/**
 * Gmail SMTP Configuration for MEMOLOG.
 *
 * To send real Gmail notifications when approving/rejecting rider applications:
 * 1. Copy this file to config/mail.php (or edit config/mail.php directly).
 * 2. Enable 2-Step Verification on your Google Account.
 * 3. Create a 16-character App Password at https://myaccount.google.com/apppasswords
 * 4. Enter your Gmail address in 'smtp_user' and the 16-character App Password in 'smtp_pass'.
 *
 * When smtp_user or smtp_pass is left empty, MEMOLOG safely logs outgoing emails
 * to storage/mail_log.txt without failing.
 */
return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_encryption' => 'tls', // 'tls' (port 587) or 'ssl' (port 465)
    'smtp_user' => '',          // e.g. yourname@gmail.com
    'smtp_pass' => '',          // 16-character Gmail App Password
    'from_email' => '',         // defaults to smtp_user if empty
    'from_name' => 'MEMOLOG Delivery Operations',
    'app_url' => 'http://localhost/Endterm/public/index.php?page=auth',
];
