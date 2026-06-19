
<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.html#order');
  exit;
}

function clean_input($value) {
  return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function generate_order_number($length = 7) {
  $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  $order_number = '';

  for ($i = 0; $i < $length; $i++) {
    $order_number .= $characters[random_int(0, strlen($characters) - 1)];
  }

  return $order_number;
}

$to = 'javiervelazquez113@yahoo.com';

$full_name = clean_input($_POST['full_name'] ?? '');
$phone_number = clean_input($_POST['phone_number'] ?? '');
$email_address = clean_input($_POST['email_address'] ?? '');
$payment_method = clean_input($_POST['payment_method'] ?? '');
$special_requests = clean_input($_POST['special_requests'] ?? '');
$order_total = clean_input($_POST['order_total'] ?? '$0');

$order_items = $_POST['order_items'] ?? [];

$pickup_date = clean_input($_POST['pickup_date'] ?? '');
$pickup_time = clean_input($_POST['pickup_time'] ?? '');

if (!is_array($order_items)) {
  $order_items = [];
}

$order_items_clean = array_map('clean_input', $order_items);

if (
  $full_name === '' ||
  $phone_number === '' ||
  $email_address === '' ||
  $payment_method === '' ||
  $pickup_date === '' ||
  $pickup_time === '' ||
  empty($order_items_clean)
) {
  header('Location: index.html#order');
  exit;
}

if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
  header('Location: index.html#order');
  exit;
}

$pickup_timestamp = strtotime($pickup_date);

if ($pickup_timestamp === false) {
  header('Location: index.html#order');
  exit;
}

$today = strtotime(date('Y-m-d'));
$max_pickup_date = strtotime('+60 days', $today);

if ($pickup_timestamp < $today || $pickup_timestamp > $max_pickup_date) {
  header('Location: index.html#order');
  exit;
}

$day_of_week = date('w', $pickup_timestamp);

if ($day_of_week !== '0' && $day_of_week !== '6') {
  header('Location: index.html#order');
  exit;
}

$order_number = generate_order_number(5);
$formatted_date = date("l, F j, Y", $pickup_timestamp);

$subject = "New Order #{$order_number} - Sunday Crumb Sourdough Co";

$body = "Sunday Crumb Sourdough Co - New Order\n";
$body .= "Order Number: {$order_number}\n\n";

$body .= "Customer Information\n";
$body .= "--------------------\n";
$body .= "Full Name: {$full_name}\n";
$body .= "Phone Number: {$phone_number}\n";
$body .= "Email Address: {$email_address}\n\n";

$body .= "Order Details\n";
$body .= "-------------\n";
$body .= "Ordered Items:\n- " . implode("\n- ", $order_items_clean) . "\n\n";
$body .= "Order Total: {$order_total}\n";
$body .= "Payment Method: {$payment_method}\n\n";

$body .= "Pickup Details\n";
$body .= "--------------\n";
$body .= "Pickup Date: {$formatted_date}\n";
$body .= "Pickup Time: {$pickup_time}\n\n";

$body .= "Special Requests\n";
$body .= "----------------\n";
$body .= ($special_requests !== '' ? $special_requests : 'None') . "\n";

$headers = [];
$headers[] = 'From: Sunday Crumb Sourdough Co <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>';
$headers[] = 'Reply-To: ' . $email_address;
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$mail_sent = @mail($to, $subject, $body, implode("\r\n", $headers));

// CUSTOMER CONFIRMATION EMAIL

$customer_subject = "Order Confirmation #{$order_number} - Sunday Crumb Sourdough Co";

$customer_body = "
Hi {$full_name},

Thank you for your order from Sunday Crumb Sourdough Co! 🫶🏽

Order Number: {$order_number}

----------------------------------
Order Summary
----------------------------------

Items:
- " . implode("\n- ", $order_items_clean) . "

Total: {$order_total}

----------------------------------
Pickup Details
----------------------------------

Date: {$formatted_date}
Time: {$pickup_time}

----------------------------------

We will have your order ready for pickup at the selected time.
Tip: Save this email or screenshot it for easy pickup reference.
If you have any questions or need to make changes, feel free to reply to this email.

Follow us on Instagram:
https://www.instagram.com/sundaycrumbsourdoughco

Thank you for supporting small-batch baking!

— Sunday Crumb Sourdough Co
";

// Send email to customer
@mail($email_address, $customer_subject, $customer_body, implode("\r\n", $headers));


if ($mail_sent) {
  header('Location: thank-you.html?order=' . urlencode($order_number));
} else {
  header('Location: thank-you.html?status=queued&order=' . urlencode($order_number));
}

exit;
?>
