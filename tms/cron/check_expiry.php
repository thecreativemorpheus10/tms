<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/mailer.php';
$services = getUpcomingServices();
if (!empty($services)) {
    $subject = 'Daily Vehicle Service Reminders';
    $body = 'Vehicles due within 7 days:<br><ul>';
    foreach ($services as $v) $body .= '<li>'.$v['registration_number'].' - due on '.$v['next_service_date'].'</li>';
    $body .= '</ul>';
    $recipients = array_map('trim', explode(',', ALERT_RECIPIENTS));
    foreach ($recipients as $rec) sendMail($rec, $subject, $body);
}
?>