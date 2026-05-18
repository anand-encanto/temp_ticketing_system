<?php

/**
 * Direct SMTP Test Script
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "=== SMTP TEST STARTED ===\n\n";

/**
 * SMTP SETTINGS
 */
$host       = 'mail.mcdmauritius.com';
$port       = 587;
$username   = 'support@mcdmauritius.com';
$password   = 'Support*2026';
$encryption = 'tls';

/**
 * TEST EMAIL
 */
$toEmail = 'yourgmail@gmail.com';

echo "SMTP Host: $host\n";
echo "SMTP Port: $port\n";
echo "SMTP User: $username\n";
echo "Encryption: $encryption\n\n";

/**
 * LOAD LARAVEL AUTOLOAD
 */
require __DIR__ . '/../api/vendor/autoload.php';

/**
 * CREATE TRANSPORT
 */
try {

    $transport = new Swift_SmtpTransport($host, $port, $encryption);

    $transport->setUsername($username);
    $transport->setPassword($password);

    echo "SMTP Transport Created Successfully\n";

    /**
     * CREATE MAILER
     */
    $mailer = new Swift_Mailer($transport);

    /**
     * CREATE MESSAGE
     */
    $message = (new Swift_Message('SMTP TEST MAIL'))
        ->setFrom([$username => "McDonald's Support"])
        ->setTo([$toEmail])
        ->setBody("SMTP TEST SUCCESSFUL");

    /**
     * SEND MAIL
     */
    $result = $mailer->send($message);

    echo "\nMAIL SENT SUCCESSFULLY\n";
    echo "Result: $result\n";

} catch (\Exception $e) {

    echo "\nSMTP ERROR:\n\n";
    echo $e->getMessage() . "\n\n";

    echo "FILE:\n";
    echo $e->getFile() . "\n\n";

    echo "LINE:\n";
    echo $e->getLine() . "\n";

}

echo "\n=== SMTP TEST FINISHED ===";