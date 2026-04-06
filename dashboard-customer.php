<?php
// dashboard-customer.php
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php'); exit;
}

$user_id = $_SESSION['user_id'];

// Stats
$stats = $pdo->prepare("SELECT
    COUNT(*) AS total,
    SUM(status='pending') AS pending,
    SUM(status='accepted') AS accepted,
    SUM(status='completed') AS completed
    FROM bookings WHERE customer_id = ?");
$stats->execute([$user_id]);
$s = $stats->fetch();

// All bookings
$stmt = $pdo->prepare("
    SELECT b.*, u.name AS provider_name, u.service_type, u.phone AS provider_phone,
           pay.status AS pay_status,
           rev.id AS reviewed
    FROM bookings b
    JOIN users u ON u.id = b.provider_id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    LEFT JOIN reviews rev ON rev.booking_id = b.id
    WHERE b.customer_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Unread message count
$unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unread->execute([$user_id]);
$unread_count = $unread->fetchColumn();

$success = $_SESSION['book_success'] ?? $_SESSION['pay_success'] ?? $_SESSION['rev_success'] ?? '';
unset($_SESSION['book_success'], $_SESSION['pay_success'], $_SESSION['rev_success']);
?>

<div class="dash-header">
  <div style="max-width:1100px;margin:0 auto;padding:0 24px">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h1>
    <p>Customer Dashboard — manage your bookings and services</p>
  </div>
</div>

<div class="page-wrap">
  <?php if ($success): ?>
    <div class="alert alert-success mb-3"><?= $success ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="grid-3 mb-3" style="gap:16px">
    <div class="stat-box">
      <div class="stat-num"><?= $s['total'] ?></div>
      <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:var(--accent)"><?= $s['pending'] ?></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:var(--secondary)"><?= $s['completed'] ?></div>
      <div class="stat-label">Completed</div>
    </div>
  </div>

  <?php if ($unread_count > 0): ?>
  <div class="alert alert-info mb-3">
    💬 You have <strong><?= $unread_count ?> unread message<?= $unread_count>1?'s':'' ?></strong>. Check your bookings below to reply.
  </div>
  <?php endif; ?>

  <!-- Quick search link -->
  <div class="flex justify-between items-center mb-2">
    <div class="section-title" style="margin-bottom:0">My Bookings</div>
    <a href="search.php" class="btn btn-primary btn-sm">+ Book New Service</a>
  </div>

  <!-- Bookings table -->
  <div class="card">
    <?php if (empty($bookings)): ?>
      <div class="card-body text-center" style="padding:48px">
        <div style="font-size:40px;margin-bottom:16px">📋</div>
        <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px">No bookings yet</div>
        <p class="text-muted mb-3">Find a service provider and make your first booking</p>
        <a href="search.php" class="btn btn-primary">Find Services</a>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Provider</th>
              <th>Service</th>
              <th>Date & Time</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
              <td style="color:var(--muted)">#<?= $b['id'] ?></td>
              <td>
                <a href="provider-profile.php?id=<?= $b['provider_id'] ?>" style="color:var(--dark);font-weight:500;text-decoration:none">
                  <?= htmlspecialchars($b['provider_name']) ?>
                </a>
                <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($b['provider_phone']) ?></div>
              </td>
              <td><?= htmlspecialchars($b['service_type'] ?? '—') ?></td>
              <td>
                <div style="font-size:13px"><?= date('M d, Y', strtotime($b['booking_date'])) ?></div>
                <div style="font-size:12px;color:var(--muted)"><?= date('h:i A', strtotime($b['booking_time'])) ?></div>
              </td>
              <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                  <!-- Chat -->
                  <a href="chat.php?booking_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">💬 Chat</a>

                  <!-- Pay (if accepted & not paid) -->
                  <?php if ($b['status'] === 'accepted' && !$b['pay_status']): ?>
                    <a href="payment.php?booking_id=<?= $b['id'] ?>" class="btn btn-success btn-sm">💳 Pay</a>
                  <?php endif; ?>

                  <!-- Review (if completed & not reviewed) -->
                  <?php if ($b['status'] === 'completed' && !$b['reviewed']): ?>
                    <a href="review.php?booking_id=<?= $b['id'] ?>" class="btn btn-accent btn-sm">⭐ Review</a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
