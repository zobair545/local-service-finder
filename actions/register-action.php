<?php
// actions/register-action.php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php'); exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$role     = $_POST['role'] ?? '';
$location = trim($_POST['location'] ?? '');

// Validation
if (!$name || !$email || !$phone || !$password || !$role) {
    $_SESSION['reg_error'] = 'All fields are required.';
    header('Location: ../register.php'); exit;
}

if ($password !== $confirm) {
    $_SESSION['reg_error'] = 'Passwords do not match.';
    header('Location: ../register.php'); exit;
}

if (strlen($password) < 6) {
    $_SESSION['reg_error'] = 'Password must be at least 6 characters.';
    header('Location: ../register.php'); exit;
}

// Check email uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $_SESSION['reg_error'] = 'This email is already registered.';
    header('Location: ../register.php'); exit;
}

$hashed  = password_hash($password, PASSWORD_DEFAULT);
$service = ($role === 'provider') ? trim($_POST['service_type'] ?? '') : null;
$exp     = ($role === 'provider') ? (int)($_POST['experience'] ?? 0) : 0;
$bio     = ($role === 'provider') ? trim($_POST['bio'] ?? '') : null;

$stmt = $pdo->prepare("
    INSERT INTO users (name, email, phone, password, role, location, service_type, experience, bio)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$name, $email, $phone, $hashed, $role, $location, $service, $exp, $bio]);

$_SESSION['reg_success'] = 'Account created successfully! Please login.';
header('Location: ../login.php'); exit;
?>
