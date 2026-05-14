<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ❌ SMTP Configuration REMOVED
// $smtpHost = 'mail.mcdmauritius.com';
// $smtpPort = 465;
// $username = 'support@mcdmauritius.com';
// $password = 'U,n=h,k9w#N9';

$to = 'yourpersonalemail@gmail.com'; // change this
$subject = 'SMTP Independence Test';
$message = "This email is sent WITHOUT setting SMTP credentials.";
$headers = "From: support@mcdmauritius.com\r\n";
$headers .= "Reply-To: support@mcdmauritius.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// ❌ These lines removed to test independence
// ini_set("SMTP", $smtpHost);
// ini_set("smtp_port", $smtpPort);
// ini_set("sendmail_from", $username);

echo "<h3>Testing mail() without SMTP settings...</h3>";

if (mail($to, $subject, $message, $headers)) {
    echo "<h2 style='color:green;'>Email Sent Successfully ✅</h2>";
} else {
    echo "<h2 style='color:red;'>Email Sending Failed ❌</h2>";
}
?>