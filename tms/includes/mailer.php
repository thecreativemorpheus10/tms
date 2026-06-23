<?php
require_once __DIR__ . '/../config/mail_config.php';

function send_mail($to, $subject, $message) {
    $to = filter_var($to, FILTER_SANITIZE_EMAIL);
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
    if (DEV_MODE) {
        $log = __DIR__ . '/../logs/email_log.txt';
        $entry = date('Y-m-d H:i:s') . " | To: $to | Subject: $subject | Message: " . strip_tags($message) . "\n---\n";
        file_put_contents($log, $entry, FILE_APPEND);
        return true;
    } else {
        return mail($to, $subject, $message, $headers);
    }
}

function sendTripAssignmentEmail($driver_email, $trip_details) {
    $subject = "Trip Assignment - " . $trip_details['trip_date'];
    $body = "<h3>Trip Details</h3>
             <p><strong>Route:</strong> " . htmlspecialchars($trip_details['route_name']) . "</p>
             <p><strong>Vehicle:</strong> " . htmlspecialchars($trip_details['registration_number']) . "</p>
             <p><strong>Date:</strong> " . htmlspecialchars($trip_details['trip_date']) . "</p>
             <p><strong>Departure:</strong> " . htmlspecialchars($trip_details['departure_time']) . "</p>
             <p><strong>Status:</strong> " . htmlspecialchars($trip_details['status']) . "</p>
             <p>Please log in to the TMS for more details.</p>";
    return send_mail($driver_email, $subject, $body);
}

function sendExpiryAlert($recipients, $subject, $body) {
    return send_mail($recipients, $subject, $body);
}
?>