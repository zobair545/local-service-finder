<?php
// chat.php
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$booking_id = (int)($_GET['booking_id'] ?? 0);
$user_id    = $_SESSION['user_id'];

// Verify user belongs to this booking
$stmt = $pdo->prepare("SELECT b.*, 
    c.name AS customer_name, p.name AS provider_name
    FROM bookings b 
    JOIN users c ON c.id = b.customer_id
    JOIN users p ON p.id = b.provider_id
    WHERE b.id = ? AND (b.customer_id = ? OR b.provider_id = ?)");
$stmt->execute([$booking_id, $user_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) { header('Location: index.php'); exit; }

// Determine chat partner
$is_customer = ($booking['customer_id'] == $user_id);
$other_id    = $is_customer ? $booking['provider_id'] : $booking['customer_id'];
$other_name  = $is_customer ? $booking['provider_name'] : $booking['customer_name'];

// Load messages
$stmt2 = $pdo->prepare("SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.booking_id = ? ORDER BY m.sent_at ASC");
$stmt2->execute([$booking_id]);
$messages = $stmt2->fetchAll();

// Mark messages as read
$pdo->prepare("UPDATE messages SET is_read=1 WHERE booking_id=? AND receiver_id=?")->execute([$booking_id, $user_id]);
?>

<div class="page-wrap" style="max-width:680px;margin:0 auto">
  <!-- Header -->
  <div class="card mb-3">
    <div class="card-body" style="display:flex;align-items:center;gap:14px;padding:16px 20px">
      <div class="provider-avatar" style="width:44px;height:44px;font-size:16px">
        <?= strtoupper(substr($other_name,0,1)) ?>
      </div>
      <div>
        <div style="font-weight:700"><?= htmlspecialchars($other_name) ?></div>
        <div style="font-size:12px;color:var(--muted)">
          Booking #<?= $booking_id ?> — <?= htmlspecialchars($booking['service_type']) ?>
          <span class="badge badge-<?= $booking['status'] ?>" style="margin-left:8px"><?= ucfirst($booking['status']) ?></span>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <!-- Chat messages -->
      <div class="chat-box" id="chatBox">
        <?php if (empty($messages)): ?>
          <p class="text-muted text-center" style="margin:auto">No messages yet. Start the conversation!</p>
        <?php else: ?>
          <?php foreach ($messages as $msg): ?>
            <div style="align-self:<?= $msg['sender_id']==$user_id ? 'flex-end' : 'flex-start' ?>">
              <?php if ($msg['sender_id'] != $user_id): ?>
              <div class="msg-name"><?= htmlspecialchars($msg['sender_name']) ?></div>
              <?php endif; ?>
              <div class="msg-bubble <?= $msg['sender_id']==$user_id ? 'msg-me' : 'msg-them' ?>">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                <div class="msg-time"><?= date('h:i A', strtotime($msg['sent_at'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Message input -->
      <form action="actions/chat-action.php" method="POST" style="display:flex;gap:10px">
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
        <input type="hidden" name="receiver_id" value="<?= $other_id ?>">
        <textarea name="message" rows="2" placeholder="Type your message..." style="flex:1;min-height:44px;resize:none" required></textarea>
        <button type="submit" class="btn btn-primary" style="align-self:flex-end">Send</button>
      </form>

    </div>
  </div>

  <div class="mt-2" style="display:flex;gap:10px">
    <a href="<?= $_SESSION['role']==='customer' ? 'dashboard-customer.php' : 'dashboard-provider.php' ?>" class="btn btn-outline btn-sm">← Dashboard</a>
  </div>
</div>

<script>
// Auto-scroll to bottom
const box = document.getElementById('chatBox');
box.scrollTop = box.scrollHeight;
// Auto-refresh every 5 seconds
setTimeout(() => location.reload(), 5000);
</script>

<?php require_once 'includes/footer.php'; ?>
