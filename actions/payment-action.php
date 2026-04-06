<?php
// actions/payment-action.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php'); exit;
}

$booking_id  = (int)($_POST['booking_id'] ?? 0);
$provider_id = (int)($_POST['provider_id'] ?? 0);
$amount      = (float)($_POST['amount'] ?? 0);
$method      = $_POST['method'] ?? 'cash';
$customer_id = $_SESSION['user_id'];

if (!$booking_id || $amount <= 0) {
    $_SESSION['pay_error'] = 'Please enter a valid amount.';
    header("Location: ../payment.php?booking_id=$booking_id"); exit;
}

// Insert payment
$stmt = $pdo->prepare("
    INSERT INTO payments (booking_id, customer_id, provider_id, amount, method, status, paid_at)
    VALUES (?, ?, ?, ?, ?, 'paid', NOW())
");
$stmt->execute([$booking_id, $customer_id, $provider_id, $amount, $method]);

// Mark booking completed
$stmt2 = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
$stmt2->execute([$booking_id]);

$_SESSION['pay_success'] = 'Payment successful! You can now leave a review.';
header("Location: ../review.php?booking_id=$booking_id"); exit;
?>
