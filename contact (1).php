<?php
// Simple, safe contact form handler for the portfolio.
// Upload together with index.html to any PHP host. Returns JSON.
header('Content-Type: application/json; charset=utf-8');

$to = 'semilooreadebayo2@gmail.com';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Honeypot: bots fill this hidden field
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true]);
    exit;
}

function clean($v) {
    return trim(str_replace(["\r", "\n"], ' ', strip_tags((string)$v)));
}

$name    = clean($_POST['name'] ?? '');
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$subject = clean($_POST['subject'] ?? 'Portfolio enquiry');
$message = trim(strip_tags($_POST['message'] ?? ''));

if ($name === '' || !$email || $message === '' || strlen($message) > 5000) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid input']);
    exit;
}

$body  = "New message from your portfolio\n\n";
$body .= "Name: $name\nEmail: $email\n\nMessage:\n$message\n";

$headers  = "From: Portfolio <no-reply@" . ($_SERVER['SERVER_NAME'] ?? 'localhost') . ">\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = @mail($to, "[Portfolio] $subject", $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Mail could not be sent']);
}
