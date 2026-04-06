<?php
// dashboard-provider.php
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header('Location: login.php'); exit;
}

$user_id = $_SESSION['user_id'];

// Handle booking status update (accept/reject/complete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['booking_id'])) {
    $action     = $_POST['action'];
    $booking_id = (int)$_POST['booking_id'];
    $allowed    = ['accepted', 'rejected', 'completed'];

    if (in_array($action, $allowed)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status=? WHERE id=? AND provider_id=?");
        $stmt->execute([$action, $booking_id, $user_id]);
    }
    header('Location: dashboard-provider.php'); exit;
}

// Stats
$stats = $pdo->prepare("SELECT
    COUNT(*) AS total,
    SUM(status='pending') AS pending,
    SUM(status='accepted') AS accepted,
    SUM(status='completed') AS completed
    FROM bookings WHERE provider_id = ?");
$stats->execute([$user_id]);
$s = $stats->fetch();

// Earnings
$earn = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE provider_id = ? AND status = 'paid'");
$earn->execute([$user_id]);
$earnings = $earn->fetchColumn();

// Average rating
$rat = $pdo->prepare("SELECT ROUND(AVG(rating),1) AS avg_r, COUNT(*) AS cnt FROM reviews WHERE provider_id = ?");
$rat->execute([$user_id]);
$rating_data = $rat->fetch();

// All bookings
$stmt = $pdo->prepare("
    SELECT b.*, u.name AS customer_name, u.phone AS customer_phone, u.location AS customer_loc
    FROM bookings b JOIN users u ON u.id = b.customer_id
    WHERE b.provider_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Unread messages
$unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unread->execute([$user_id]);
$unread_count = $unread->fetchColumn();
?>

<div class="dash-header">
  <div style="max-width:1100px;margin:0 auto;padding:0 24px">
    <h1>Provider Dashboard 🔧</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> — manage your service requests</p>
  </div>
</div>

<div class="page-wrap">

  <!-- Stats -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">
    <div class="stat-box">
      <div class="stat-num"><?= $s['total'] ?></div>
      <div class="stat-label">Total Requests</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:var(--accent)"><?= $s['pending'] ?></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:var(--secondary)"><?= $s['completed'] ?></div>
      <div class="stat-label">Completed</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:var(--primary)">৳<?= number_format($earnings) ?></div>
      <div class="stat-label">Total Earned</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:var(--accent)">
        <?= $rating_data['avg_r'] ? $rating_data['avg_r'] . '★' : '—' ?>
      </div>
      <div class="stat-label">Avg Rating (<?= $rating_data['cnt'] ?> reviews)</div>
    </div>
  </div>

  <?php if ($unread_count > 0): ?>
  <div class="alert alert-info mb-3">
    💬 You have <strong><?= $unread_count ?> unread message<?= $unread_count>1?'s':'' ?></strong>.
  </div>
  <?php endif; ?>

  <!-- Pending requests highlight -->
  <?php $pending = array_filter($bookings, fn($b) => $b['status'] === 'pending'); ?>
  <?php if (!empty($pending)): ?>
  <div class="section-title mb-2">🔔 Pending Requests (<?= count($pending) ?>)</div>
  <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:32px">
    <?php foreach ($pending as $b): ?>
    <div class="card">
      <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <div>
          <div style="font-weight:700;margin-bottom:4px"><?= htmlspecialchars($b['customer_name']) ?></div>
          <div style="font-size:13px;color:var(--muted)">
            📅 <?= date('M d, Y', strtotime($b['booking_date'])) ?> at <?= date('h:i A', strtotime($b['booking_time'])) ?>
          </div>
          <?php if ($b['notes']): ?>
          <div style="font-size:13px;color:var(--muted);margin-top:4px">📝 <?= htmlspecialchars(substr($b['notes'],0,80)) ?></div>
          <?php endif; ?>
          <div style="font-size:12px;margin-top:4px">📞 <?= htmlspecialchars($b['customer_phone']) ?></div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <form method="POST" style="display:inline">
            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
            <input type="hidden" name="action" value="accepted">
            <button class="btn btn-success btn-sm">✅ Accept</button>
          </form>
          <form method="POST" style="display:inline">
            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
            <input type="hidden" name="action" value="rejected">
            <button class="btn btn-danger btn-sm">❌ Reject</button>
          </form>
          <a href="chat.php?booking_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">💬 Chat</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- All bookings -->
  <div class="flex justify-between items-center mb-2">
    <div class="section-title" style="margin-bottom:0">All Bookings</div>
  </div>
  <div class="card">
    <?php if (empty($bookings)): ?>
      <div class="card-body text-center" style="padding:48px">
        <div style="font-size:40px;margin-bottom:12px">📭</div>
        <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700">No bookings yet</div>
        <p class="text-muted mt-1">Customers will find you through search</p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Customer</th>
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
                <div style="font-weight:500"><?= htmlspecialchars($b['customer_name']) ?></div>
                <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($b['customer_phone']) ?></div>
              </td>
              <td>
                <div style="font-size:13px"><?= date('M d, Y', strtotime($b['booking_date'])) ?></div>
                <div style="font-size:12px;color:var(--muted)"><?= date('h:i A', strtotime($b['booking_time'])) ?></div>
              </td>
              <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                  <a href="chat.php?booking_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">💬 Chat</a>
                  <?php if ($b['status'] === 'accepted'): ?>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                      <input type="hidden" name="action" value="completed">
                      <button class="btn btn-secondary btn-sm">✅ Mark Done</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($b['status'] === 'pending'): ?>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                      <input type="hidden" name="action" value="accepted">
                      <button class="btn btn-success btn-sm">Accept</button>
                    </form>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                      <input type="hidden" name="action" value="rejected">
                      <button class="btn btn-danger btn-sm">Reject</button>
                    </form>
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
