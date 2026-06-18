<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.html#order');
  exit;
}

function clean_input($value) {
  return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

$to = 'javiervelazquez113@yahoo.com';
$subject = 'New Order - Sunday Crumb Sourdough Co';

$full_name = clean_input($_POST['full_name'] ?? '');
$phone_number = clean_input($_POST['phone_number'] ?? '');
$email_address = clean_input($_POST['email_address'] ?? '');
$payment_method = clean_input($_POST['payment_method'] ?? '');
$special_requests = clean_input($_POST['special_requests'] ?? '');
$order_total = clean_input($_POST['order_total'] ?? '$0');
$order_items = $_POST['order_items'] ?? [];

if (!is_array($order_items)) {
  $order_items = [];
}

$order_items_clean = array_map('clean_input', $order_items);

if ($full_name === '' || $phone_number === '' || $email_address === '' || $payment_method === '' || empty($order_items_clean)) {
  header('Location: index.html#order');
  exit;
}

$body = "Sunday Crumb Sourdough Co - New Order\n\n";
$body .= "Full Name: {$full_name}\n";
$body .= "Phone Number: {$phone_number}\n";
$body .= "Email Address: {$email_address}\n\n";
$body .= "Ordered Items:\n- " . implode("\n- ", $order_items_clean) . "\n\n";
$body .= "Payment Method: {$payment_method}\n";
$body .= "Order Total: {$order_total}\n\n";
$body .= "Special Requests:\n" . ($special_requests !== '' ? $special_requests : 'None') . "\n";

$headers = [];
$headers[] = 'From: Sunday Crumb Sourdough Co <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>';
$headers[] = 'Reply-To: ' . $email_address;
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$mail_sent = @mail($to, $subject, $body, implode("\r\n", $headers));

if ($mail_sent) {
  header('Location: thank-you.html');
} else {
  header('Location: thank-you.html?status=queued');
}
exit;
?>
