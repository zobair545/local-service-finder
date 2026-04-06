<?php
// actions/book-action.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php'); exit;
}

$provider_id   = (int)($_POST['provider_id'] ?? 0);
$booking_date  = $_POST['booking_date'] ?? '';
$booking_time  = $_POST['booking_time'] ?? '';
$notes         = trim($_POST['notes'] ?? '');
$customer_id   = $_SESSION['user_id'];

if (!$provider_id || !$booking_date || !$booking_time) {
    $_SESSION['book_error'] = 'Please fill all required fields.';
    header("Location: ../book.php?provider_id=$provider_id"); exit;
}

// Get provider service type
$stmt = $pdo->prepare("SELECT service_type FROM users WHERE id = ?");
$stmt->execute([$provider_id]);
$prov = $stmt->fetch();

$stmt = $pdo->prepare("
    INSERT INTO bookings (customer_id, provider_id, service_type, booking_date, booking_time, notes)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$customer_id, $provider_id, $prov['service_type'], $booking_date, $booking_time, $notes]);

$_SESSION['book_success'] = 'Booking request sent successfully!';
header('Location: ../dashboard-customer.php'); exit;
?>
