<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.html?error=didnotpost#order');
  exit;
}

function clean_input($value) {
  return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function generate_order_number($length = 5) {
  $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  $order_number = '';

  for ($i = 0; $i < $length; $i++) {
    $order_number .= $characters[random_int(0, strlen($characters) - 1)];
  }

  return $order_number;
}

$products = [
  'Classic Sourdough Loaf ($12)' => [
    'type' => 'loaf',
    'price' => 12.00,
  ],
  'Jalapeño Cheddar Sourdough Loaf ($15)' => [
    'type' => 'loaf',
    'price' => 15.00,
  ],
  'Whole Wheat Sourdough with Nuts and Raisins (optional) ($14)' => [
    'type' => 'loaf',
    'price' => 14.00,
  ],
  '6 Pack of Plain Sourdough Bagels ($16)' => [
    'type' => 'bagel',
    'price' => 16.00,
  ],
  '6 Pack of Jalapeño Cheddar Sourdough Bagels ($18)' => [
    'type' => 'bagel',
    'price' => 18.00,
  ],
  '6 Pack of Blueberry Sourdough Bagels ($18)' => [
    'type' => 'bagel',
    'price' => 18.00,
  ],
];

$to = 'javiervelazquez113@yahoo.com';

$full_name = clean_input($_POST['full_name'] ?? '');
$phone_number = clean_input($_POST['phone_number'] ?? '');
$email_address = clean_input($_POST['email_address'] ?? '');
$payment_method = clean_input($_POST['payment_method'] ?? '');
$special_requests = clean_input($_POST['special_requests'] ?? '');
$pickup_date = clean_input($_POST['pickup_date'] ?? '');
$pickup_time = clean_input($_POST['pickup_time'] ?? '');

$order_items = $_POST['order_items'] ?? [];

if (!is_array($order_items)) {
  $order_items = [];
}

$order_items_clean = array_map('trim', $order_items);

if (
  $full_name === '' ||
  $phone_number === '' ||
  $email_address === '' ||
  $payment_method === '' ||
  $pickup_date === '' ||
  $pickup_time === '' ||
  empty($order_items_clean)
) {
  header('Location: index.html?error=didnotpost#order');
  exit;
}

if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
  header('Location: index.html?error=invalidemail#order');
  exit;
}

$requested_loaves = 0;
$requested_bagels = 0;
$calculated_total = 0;

foreach ($order_items_clean as $item) {
  if (!isset($products[$item])) {
    die('Invalid product selected');
    exit;
  }

  if ($products[$item]['type'] === 'loaf') {
    $requested_loaves++;
  }

  if ($products[$item]['type'] === 'bagel') {
    $requested_bagels++;
  }

  $calculated_total += $products[$item]['price'];
}

$pickup_timestamp = strtotime($pickup_date);

if ($pickup_timestamp === false) {
  die('choose a date');
  exit;
}

$today = strtotime(date('Y-m-d'));
$max_pickup_date = strtotime('+60 days', $today);

if ($pickup_timestamp < $today || $pickup_timestamp > $max_pickup_date) {
  die('Date outside allowed range');
  exit;
}

$day_of_week = date('w', $pickup_timestamp);

if ($day_of_week !== '0' && $day_of_week !== '6') {
  die('Day not a weekend day');
  exit;
}

$stmt = $pdo->prepare("
  SELECT 
    COALESCE(SUM(CASE WHEN oi.item_type = 'loaf' THEN oi.quantity ELSE 0 END), 0) AS total_loaves,
    COALESCE(SUM(CASE WHEN oi.item_type = 'bagel' THEN oi.quantity ELSE 0 END), 0) AS total_bagels
  FROM order_items oi
  INNER JOIN orders o ON oi.order_id = o.id
  WHERE o.pickup_date = ?
");

$stmt->execute([$pickup_date]);
$current_totals = $stmt->fetch();

$current_loaves = (int) $current_totals['total_loaves'];
$current_bagels = (int) $current_totals['total_bagels'];

$max_loaves = 12;
$max_bagels = 20;

if (($current_loaves + $requested_loaves) > $max_loaves) {
  
  header('Location: index.html?error=loaves#order');
  exit;
}

if (($current_bagels + $requested_bagels) > $max_bagels) {
  
  header('Location: index.html?error=bagels#order');
  exit;
}

$order_number = generate_order_number(5);
$formatted_date = date("l, F j, Y", $pickup_timestamp);
$order_total = '$' . number_format($calculated_total, 2);

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("
    INSERT INTO orders (
      order_number,
      full_name,
      phone_number,
      email_address,
      payment_method,
      pickup_date,
      pickup_time,
      order_total,
      special_requests
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");

  $stmt->execute([
    $order_number,
    $full_name,
    $phone_number,
    $email_address,
    $payment_method,
    $pickup_date,
    $pickup_time,
    $calculated_total,
    $special_requests
  ]);

  $order_id = $pdo->lastInsertId();

  $stmt = $pdo->prepare("
    INSERT INTO order_items (
      order_id,
      item_name,
      item_type,
      quantity,
      price
    ) VALUES (?, ?, ?, ?, ?)
  ");

foreach ($order_items_clean as $item) {
  $stmt->execute([
    $order_id,
    $item,
    $products[$item]['type'],
    1,
    $products[$item]['price']
  ]);
}

$pdo->commit();

} catch (Exception $e) {

  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  header('Location: index.html?error=database#order');
  exit;
}

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

$admin_headers = [];
$admin_headers[] = 'From: Sunday Crumb Sourdough Co <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>';
$admin_headers[] = 'Reply-To: ' . $email_address;
$admin_headers[] = 'Content-Type: text/plain; charset=UTF-8';

$mail_sent = @mail($to, $subject, $body, implode("\r\n", $admin_headers));

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

$customer_headers = [];
$customer_headers[] = 'From: Sunday Crumb Sourdough Co <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>';
$customer_headers[] = 'Reply-To: ' . $to;
$customer_headers[] = 'Content-Type: text/plain; charset=UTF-8';

@mail($email_address, $customer_subject, $customer_body, implode("\r\n", $customer_headers));

if ($mail_sent) {
  header('Location: thank-you.html?order=' . urlencode($order_number));
} else {
  header('Location: thank-you.html?status=queued&order=' . urlencode($order_number));
}

exit;
?>