<?php
// book.php
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php'); exit;
}

$provider_id = (int)($_GET['provider_id'] ?? 0);
if (!$provider_id) { header('Location: search.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'provider'");
$stmt->execute([$provider_id]);
$provider = $stmt->fetch();
if (!$provider) { header('Location: search.php'); exit; }

$error   = $_SESSION['book_error'] ?? '';
$success = $_SESSION['book_success'] ?? '';
unset($_SESSION['book_error'], $_SESSION['book_success']);
?>

<div class="page-wrap" style="max-width:600px;margin:0 auto">
  <a href="provider-profile.php?id=<?= $provider_id ?>" style="color:var(--muted);font-size:14px;text-decoration:none">
    ← Back to Profile
  </a>

  <div class="card mt-3">
    <div class="card-header">
      <h2>Book a Service</h2>
    </div>
    <div class="card-body">

      <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

      <!-- Provider summary -->
      <div style="display:flex;gap:14px;align-items:center;background:var(--bg);border-radius:10px;padding:16px;margin-bottom:24px">
        <div class="provider-avatar" style="width:50px;height:50px;font-size:18px">
          <?= strtoupper(substr($provider['name'],0,1)) ?>
        </div>
        <div>
          <div style="font-weight:700"><?= htmlspecialchars($provider['name']) ?></div>
          <div style="color:var(--primary);font-size:13px"><?= htmlspecialchars($provider['service_type']) ?></div>
          <div style="color:var(--muted);font-size:12px">📍 <?= htmlspecialchars($provider['location']) ?></div>
        </div>
      </div>

      <form action="actions/book-action.php" method="POST">
        <input type="hidden" name="provider_id" value="<?= $provider_id ?>">

        <div class="form-row">
          <div class="form-group">
            <label>Service Date</label>
            <input type="date" name="booking_date" required min="<?= date('Y-m-d') ?>">
          </div>
          <div class="form-group">
            <label>Preferred Time</label>
            <input type="time" name="booking_time" required>
          </div>
        </div>

        <div class="form-group">
          <label>Notes / Description (optional)</label>
          <textarea name="notes" placeholder="Describe your issue or what you need done..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          Send Booking Request
        </button>
      </form>

    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
