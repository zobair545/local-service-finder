<?php
// register.php
require_once 'includes/header.php';
$error = $_SESSION['reg_error'] ?? '';
$success = $_SESSION['reg_success'] ?? '';
unset($_SESSION['reg_error'], $_SESSION['reg_success']);
?>

<div class="form-card" style="max-width:560px">
  <div class="form-title">Create Account</div>
  <div class="form-sub">Join LocalServe as a customer or service provider</div>

  <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

  <form action="actions/register-action.php" method="POST">

    <!-- Role Selection -->
    <div class="form-group">
      <label>I am a...</label>
      <div style="display:flex;gap:12px">
        <label style="flex:1;cursor:pointer;display:block">
          <input type="radio" name="role" value="customer" required style="margin-right:6px">
          👤 Customer
        </label>
        <label style="flex:1;cursor:pointer;display:block">
          <input type="radio" name="role" value="provider" style="margin-right:6px">
          🔧 Service Provider
        </label>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Your full name" required>
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="01XXXXXXXXX" required>
      </div>
    </div>

    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="you@example.com" required>
    </div>

    <div class="form-group">
      <label>Location / Area</label>
      <input type="text" name="location" placeholder="e.g. Dhaka, Chittagong" required>
    </div>

    <!-- Provider-only fields (shown via JS) -->
    <div id="provider-fields" style="display:none">
      <div class="form-row">
        <div class="form-group">
          <label>Service Type</label>
          <select name="service_type">
            <option value="">-- Select --</option>
            <option>Electrician</option>
            <option>Plumber</option>
            <option>AC Repair</option>
            <option>Carpenter</option>
            <option>Cleaner</option>
            <option>Mechanic</option>
            <option>Painter</option>
            <option>Other</option>
          </select>
        </div>
        <div class="form-group">
          <label>Years of Experience</label>
          <input type="number" name="experience" placeholder="e.g. 5" min="0" max="50">
        </div>
      </div>
      <div class="form-group">
        <label>Bio / Description</label>
        <textarea name="bio" placeholder="Describe your services, skills, working hours..."></textarea>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Min 6 characters" required minlength="6">
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Repeat password" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>

    <p class="text-center mt-2" style="font-size:14px;color:var(--muted)">
      Already have an account? <a href="login.php" style="color:var(--primary)">Login</a>
    </p>
  </form>
</div>

<script>
document.querySelectorAll('input[name="role"]').forEach(r => {
  r.addEventListener('change', () => {
    document.getElementById('provider-fields').style.display =
      r.value === 'provider' ? 'block' : 'none';
  });
});
</script>

<?php require_once 'includes/footer.php'; ?>
