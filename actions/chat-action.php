<?php
// actions/chat-action.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); exit;
}

$booking_id  = (int)($_POST['booking_id'] ?? 0);
$receiver_id = (int)($_POST['receiver_id'] ?? 0);
$message     = trim($_POST['message'] ?? '');
$sender_id   = $_SESSION['user_id'];

if (!$booking_id || !$receiver_id || !$message) {
    header("Location: ../chat.php?booking_id=$booking_id"); exit;
}

// Verify sender belongs to booking
$stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND (customer_id = ? OR provider_id = ?)");
$stmt->execute([$booking_id, $sender_id, $sender_id]);
if (!$stmt->fetch()) { header('Location: ../index.php'); exit; }

$stmt = $pdo->prepare("INSERT INTO messages (booking_id, sender_id, receiver_id, message) VALUES (?,?,?,?)");
$stmt->execute([$booking_id, $sender_id, $receiver_id, $message]);

header("Location: ../chat.php?booking_id=$booking_id");
exit;
?>
