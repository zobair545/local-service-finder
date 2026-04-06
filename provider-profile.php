<?php
// provider-profile.php
require_once 'includes/header.php';
require_once 'config/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: search.php'); exit; }

$stmt = $pdo->prepare("
    SELECT u.*, ROUND(AVG(r.rating),1) AS avg_rating, COUNT(r.id) AS review_count
    FROM users u LEFT JOIN reviews r ON r.provider_id = u.id
    WHERE u.id = ? AND u.role = 'provider' GROUP BY u.id
");
$stmt->execute([$id]);
$provider = $stmt->fetch();
if (!$provider) { header('Location: search.php'); exit; }

// Get reviews
$stmt2 = $pdo->prepare("
    SELECT r.*, u.name AS customer_name
    FROM reviews r JOIN users u ON u.id = r.customer_id
    WHERE r.provider_id = ? ORDER BY r.created_at DESC
");
$stmt2->execute([$id]);
$reviews = $stmt2->fetchAll();
?>

<div class="page-wrap">
  <div class="grid-2" style="gap:28px;align-items:start">

    <!-- Left: Provider info -->
    <div>
      <div class="card mb-3">
        <div class="card-body" style="text-align:center;padding:36px">
          <div class="provider-avatar" style="width:80px;height:80px;font-size:28px;margin:0 auto 16px">
            <?= strtoupper(substr($provider['name'],0,1)) ?>
          </div>
          <h1 style="font-family:'Syne',sans-serif;font-size:22px;font-weight:800;margin-bottom:4px">
            <?= htmlspecialchars($provider['name']) ?>
          </h1>
          <div style="color:var(--primary);font-weight:500;margin-bottom:12px">
            <?= htmlspecialchars($provider['service_type'] ?? 'Service Provider') ?>
          </div>
          <?php if ($provider['avg_rating']): ?>
          <div class="stars" style="font-size:20px;margin-bottom:4px">
            <?php $r = round($provider['avg_rating']); echo str_repeat('★',$r).str_repeat('☆',5-$r); ?>
          </div>
          <div style="font-size:13px;color:var(--muted)"><?= $provider['avg_rating'] ?>/5 from <?= $provider['review_count'] ?> reviews</div>
          <?php else: ?>
          <div style="color:var(--muted);font-size:13px">No reviews yet</div>
          <?php endif; ?>
        </div>
        <div class="divider" style="margin:0"></div>
        <div class="card-body">
          <div style="display:flex;flex-direction:column;gap:12px">
            <div style="display:flex;gap:12px">
              <span>📍</span>
              <span><?= htmlspecialchars($provider['location'] ?? 'N/A') ?></span>
            </div>
            <div style="display:flex;gap:12px">
              <span>💼</span>
              <span><?= $provider['experience'] ?> years experience</span>
            </div>
            <div style="display:flex;gap:12px">
              <span>📞</span>
              <span><?= htmlspecialchars($provider['phone']) ?></span>
            </div>
            <div style="display:flex;gap:12px">
              <span>✉️</span>
              <span><?= htmlspecialchars($provider['email']) ?></span>
            </div>
          </div>
          <?php if ($provider['bio']): ?>
          <div class="divider"></div>
          <p style="font-size:14px;color:var(--muted);line-height:1.7">
            <?= nl2br(htmlspecialchars($provider['bio'])) ?>
          </p>
          <?php endif; ?>

          <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
          <div class="mt-3">
            <a href="book.php?provider_id=<?= $provider['id'] ?>" class="btn btn-primary btn-block">
              📅 Book This Provider
            </a>
          </div>
          <?php elseif (!isset($_SESSION['user_id'])): ?>
          <div class="mt-3">
            <a href="login.php" class="btn btn-primary btn-block">Login to Book</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right: Reviews -->
    <div>
      <div class="card">
        <div class="card-header">
          <h2>Customer Reviews (<?= count($reviews) ?>)</h2>
        </div>
        <div class="card-body">
          <?php if (empty($reviews)): ?>
            <p class="text-muted text-center" style="padding:24px 0">No reviews yet. Be the first!</p>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:20px">
              <?php foreach ($reviews as $rev): ?>
                <div style="border-bottom:1px solid var(--border);padding-bottom:16px">
                  <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                    <strong style="font-size:14px"><?= htmlspecialchars($rev['customer_name']) ?></strong>
                    <span class="stars" style="font-size:14px">
                      <?php echo str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?>
                    </span>
                  </div>
                  <p style="font-size:14px;color:var(--muted);line-height:1.6">
                    <?= htmlspecialchars($rev['review_text']) ?>
                  </p>
                  <span style="font-size:12px;color:#bbb">
                    <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
