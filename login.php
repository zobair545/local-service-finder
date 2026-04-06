<?php
// login.php
require_once 'includes/header.php';
$error   = $_SESSION['login_error'] ?? '';
$success = $_SESSION['reg_success'] ?? '';
unset($_SESSION['login_error'], $_SESSION['reg_success']);
?>

<div class="form-card">
  <div class="form-title">Welcome Back</div>
  <div class="form-sub">Login to your LocalServe account</div>

  <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

  <form action="actions/login-action.php" method="POST">
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="you@example.com" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Your password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
    <p class="text-center mt-2" style="font-size:14px;color:var(--muted)">
      Don't have an account? <a href="register.php" style="color:var(--primary)">Register</a>
    </p>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
