<?php
// actions/review-action.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php'); exit;
}

$booking_id  = (int)($_POST['booking_id'] ?? 0);
$provider_id = (int)($_POST['provider_id'] ?? 0);
$rating      = (int)($_POST['rating'] ?? 0);
$review_text = trim($_POST['review_text'] ?? '');
$customer_id = $_SESSION['user_id'];

if (!$booking_id || !$rating || !$review_text) {
    $_SESSION['rev_error'] = 'Please provide a rating and review.';
    header("Location: ../review.php?booking_id=$booking_id"); exit;
}

if ($rating < 1 || $rating > 5) {
    $_SESSION['rev_error'] = 'Rating must be between 1 and 5.';
    header("Location: ../review.php?booking_id=$booking_id"); exit;
}

// Prevent duplicate reviews
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ?");
$stmt->execute([$booking_id]);
if ($stmt->fetch()) {
    $_SESSION['rev_error'] = 'You have already reviewed this booking.';
    header("Location: ../review.php?booking_id=$booking_id"); exit;
}

$stmt = $pdo->prepare("
    INSERT INTO reviews (booking_id, customer_id, provider_id, rating, review_text)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$booking_id, $customer_id, $provider_id, $rating, $review_text]);

$_SESSION['rev_success'] = 'Review submitted! Thank you.';
header('Location: ../dashboard-customer.php');
exit;
?>
