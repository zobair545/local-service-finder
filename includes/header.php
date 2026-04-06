<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LocalServe — Find Services Near You</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/project/css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="/project/" class="brand">⚡ LocalServe</a>
  <div class="nav-links">
    <a href="/project/search.php" <?= $current_page=='search.php'?'class="active"':'' ?>>Find Services</a>
    <?php if ($logged_in): ?>
      <?php if ($role === 'customer'): ?>
        <a href="/project/dashboard-customer.php">My Bookings</a>
      <?php else: ?>
        <a href="/project/dashboard-provider.php">Dashboard</a>
      <?php endif; ?>
      <a href="/project/logout.php" class="btn-nav">Logout</a>
    <?php else: ?>
      <a href="/project/login.php">Login</a>
      <a href="/project/register.php" class="btn-nav">Register</a>
    <?php endif; ?>
  </div>
</nav>
