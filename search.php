<?php
// search.php
require_once 'includes/header.php';
require_once 'config/db.php';

$service  = trim($_GET['service'] ?? '');
$location = trim($_GET['location'] ?? '');

$query = "SELECT u.*, ROUND(AVG(r.rating),1) AS avg_rating, COUNT(r.id) AS review_count
          FROM users u
          LEFT JOIN reviews r ON r.provider_id = u.id
          WHERE u.role = 'provider'";
$params = [];

if ($service) {
    $query .= " AND u.service_type LIKE ?";
    $params[] = "%$service%";
}
if ($location) {
    $query .= " AND u.location LIKE ?";
    $params[] = "%$location%";
}

$query .= " GROUP BY u.id ORDER BY avg_rating DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$providers = $stmt->fetchAll();
?>

<div class="page-wrap">
  <!-- Search bar -->
  <div class="card mb-3">
    <div class="card-body">
      <form action="search.php" method="GET" style="display:flex;gap:12px;flex-wrap:wrap">
        <input type="text" name="service" placeholder="Service type..." value="<?= htmlspecialchars($service) ?>" style="flex:1;min-width:160px">
        <input type="text" name="location" placeholder="Location..." value="<?= htmlspecialchars($location) ?>" style="flex:1;min-width:160px">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="search.php" class="btn btn-outline">Clear</a>
      </form>
    </div>
  </div>

  <!-- Results header -->
  <div class="flex justify-between items-center mb-2">
    <div>
      <div class="section-title" style="margin-bottom:4px">
        <?= $service ? htmlspecialchars($service) . ' Providers' : 'All Providers' ?>
      </div>
      <p class="text-muted" style="font-size:14px">
        <?= count($providers) ?> provider<?= count($providers) !== 1 ? 's' : '' ?> found
        <?= $location ? 'in ' . htmlspecialchars($location) : '' ?>
      </p>
    </div>
  </div>

  <!-- Results -->
  <?php if (empty($providers)): ?>
    <div class="card">
      <div class="card-body text-center" style="padding:48px">
        <div style="font-size:48px;margin-bottom:16px">🔍</div>
        <div style="font-family:'Syne',sans-serif;font-size:20px;font-weight:700;margin-bottom:8px">No providers found</div>
        <p class="text-muted">Try a different service or location</p>
      </div>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:16px">
      <?php foreach ($providers as $p): ?>
        <a href="provider-profile.php?id=<?= $p['id'] ?>" class="provider-card">
          <div class="provider-avatar"><?= strtoupper(substr($p['name'],0,1)) ?></div>
          <div class="provider-info">
            <div class="provider-name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="provider-service"><?= htmlspecialchars($p['service_type'] ?? 'General') ?></div>
            <div class="provider-meta">
              <span class="meta-item">📍 <?= htmlspecialchars($p['location'] ?? '') ?></span>
              <span class="meta-item">💼 <?= $p['experience'] ?> yrs exp</span>
              <?php if ($p['avg_rating']): ?>
              <span class="meta-item stars">
                <?php $r = round($p['avg_rating']); echo str_repeat('★',$r).str_repeat('☆',5-$r); ?>
                <?= $p['avg_rating'] ?>/5 (<?= $p['review_count'] ?> reviews)
              </span>
              <?php else: ?>
              <span class="meta-item text-muted">No reviews yet</span>
              <?php endif; ?>
            </div>
            <?php if ($p['bio']): ?>
            <p style="font-size:13px;color:var(--muted);margin-top:8px;line-height:1.5">
              <?= htmlspecialchars(substr($p['bio'],0,100)) ?>...
            </p>
            <?php endif; ?>
          </div>
          <div style="flex-shrink:0">
            <span class="btn btn-primary btn-sm">Book Now</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
