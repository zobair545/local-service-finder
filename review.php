<?php
// review.php
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php'); exit;
}

$booking_id  = (int)($_GET['booking_id'] ?? 0);
$customer_id = $_SESSION['user_id'];

// Booking must be completed and belong to this customer
$stmt = $pdo->prepare("
    SELECT b.*, u.name AS provider_name, u.service_type
    FROM bookings b JOIN users u ON u.id = b.provider_id
    WHERE b.id = ? AND b.customer_id = ? AND b.status = 'completed'
");
$stmt->execute([$booking_id, $customer_id]);
$booking = $stmt->fetch();

if (!$booking) { header('Location: dashboard-customer.php'); exit; }

// Check if already reviewed
$stmt2 = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ?");
$stmt2->execute([$booking_id]);
$already_reviewed = $stmt2->fetch();

$error   = $_SESSION['rev_error'] ?? '';
$success = $_SESSION['rev_success'] ?? '';
unset($_SESSION['rev_error'], $_SESSION['rev_success']);
?>

<div class="page-wrap" style="max-width:500px;margin:0 auto">
  <div class="card">
    <div class="card-header">
      <h2>⭐ Leave a Review</h2>
    </div>
    <div class="card-body">

      <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

      <!-- Provider info -->
      <div style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:24px">
        <div style="font-weight:700;margin-bottom:4px"><?= htmlspecialchars($booking['provider_name']) ?></div>
        <div style="font-size:13px;color:var(--muted)">
          <?= htmlspecialchars($booking['service_type']) ?> •
          <?= date('M d, Y', strtotime($booking['booking_date'])) ?>
        </div>
      </div>

      <?php if ($already_reviewed): ?>
        <div class="alert alert-info">You have already submitted a review for this booking.</div>
        <a href="dashboard-customer.php" class="btn btn-outline btn-block">← Back to Dashboard</a>
      <?php else: ?>
      <form action="actions/review-action.php" method="POST">
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
        <input type="hidden" name="provider_id" value="<?= $booking['provider_id'] ?>">

        <div class="form-group">
          <label>Your Rating</label>
          <!-- CSS-only star rating (flex-row-reverse trick) -->
          <div class="star-rating">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
              <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
          </div>
        </div>

        <div class="form-group">
          <label>Your Review</label>
          <textarea name="review_text" placeholder="Share your experience with this service provider..." required></textarea>
        </div>

        <button type="submit" class="btn btn-accent btn-block btn-lg">Submit Review</button>
      </form>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
