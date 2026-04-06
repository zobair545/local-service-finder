<?php
// payment.php
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php'); exit;
}

$booking_id = (int)($_GET['booking_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT b.*, u.name AS provider_name, u.service_type
    FROM bookings b JOIN users u ON u.id = b.provider_id
    WHERE b.id = ? AND b.customer_id = ? AND b.status = 'accepted'
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) { header('Location: dashboard-customer.php'); exit; }

// Check if already paid
$stmt2 = $pdo->prepare("SELECT id FROM payments WHERE booking_id = ? AND status = 'paid'");
$stmt2->execute([$booking_id]);
$already_paid = $stmt2->fetch();

$error   = $_SESSION['pay_error'] ?? '';
$success = $_SESSION['pay_success'] ?? '';
unset($_SESSION['pay_error'], $_SESSION['pay_success']);
?>

<div class="page-wrap" style="max-width:500px;margin:0 auto">
  <div class="card">
    <div class="card-header">
      <h2>💳 Payment</h2>
    </div>
    <div class="card-body">

      <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
      <?php if ($already_paid): ?><div class="alert alert-success">✅ Payment already completed for this booking.</div><?php endif; ?>

      <!-- Booking Summary -->
      <div style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:24px">
        <div style="font-weight:700;margin-bottom:8px">Booking Summary</div>
        <div style="font-size:14px;color:var(--muted);display:flex;flex-direction:column;gap:6px">
          <span>🔧 Service: <?= htmlspecialchars($booking['service_type']) ?></span>
          <span>👤 Provider: <?= htmlspecialchars($booking['provider_name']) ?></span>
          <span>📅 Date: <?= date('M d, Y', strtotime($booking['booking_date'])) ?></span>
          <span>🕐 Time: <?= date('h:i A', strtotime($booking['booking_time'])) ?></span>
        </div>
      </div>

      <?php if (!$already_paid): ?>
      <form action="actions/payment-action.php" method="POST">
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
        <input type="hidden" name="provider_id" value="<?= $booking['provider_id'] ?>">

        <div class="form-group">
          <label>Amount (BDT)</label>
          <input type="number" name="amount" placeholder="e.g. 500" required min="1">
        </div>

        <div class="form-group">
          <label>Payment Method</label>
          <select name="method" required>
            <option value="cash">💵 Cash on Delivery</option>
            <option value="bkash">📱 bKash</option>
            <option value="nagad">📱 Nagad</option>
            <option value="card">💳 Card</option>
          </select>
        </div>

        <button type="submit" class="btn btn-success btn-block btn-lg">Confirm Payment</button>
      </form>
      <?php endif; ?>

      <a href="dashboard-customer.php" class="btn btn-outline btn-block mt-2">← Back to Dashboard</a>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
