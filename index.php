<?php
// index.php — Homepage
require_once 'includes/header.php';
require_once 'config/db.php';

// Fetch top-rated providers for homepage
$stmt = $pdo->query("
    SELECT u.*, ROUND(AVG(r.rating),1) AS avg_rating, COUNT(r.id) AS review_count
    FROM users u
    LEFT JOIN reviews r ON r.provider_id = u.id
    WHERE u.role = 'provider'
    GROUP BY u.id
    ORDER BY avg_rating DESC
    LIMIT 6
");
$top_providers = $stmt->fetchAll();
?>

<!-- Hero -->
<div class="hero">
  <h1>Find Trusted <span>Local Services</span><br>Near You</h1>
  <p>Connect with verified plumbers, electricians, cleaners, mechanics and more in your area.</p>
  <form action="search.php" method="GET" class="search-bar">
    <input type="text" name="service" placeholder="Service type (e.g. Electrician)">
    <input type="text" name="location" placeholder="Your location (e.g. Dhaka)">
    <button type="submit" class="btn btn-accent btn-lg">Search</button>
  </form>
</div>

<!-- Categories -->
<div class="page-wrap">
  <div class="section">
    <div class="section-title">Browse by Category</div>
    <div class="section-sub">Popular services people search for every day</div>
    <div class="grid-3" style="gap:12px">
      <?php
      $categories = [
        ['⚡','Electrician','electrical'],
        ['🔧','Plumber','plumbing'],
        ['❄️','AC Repair','AC'],
        ['🔨','Carpenter','carpentry'],
        ['🧹','Cleaner','cleaning'],
        ['🚗','Mechanic','mechanic'],
      ];
      foreach ($categories as $cat): ?>
        <a href="search.php?service=<?= $cat[2] ?>" style="
          display:flex; align-items:center; gap:12px;
          background:#fff; border:1px solid var(--border);
          border-radius:12px; padding:18px 20px;
          text-decoration:none; color:var(--dark);
          transition:all 0.2s; font-weight:500;
        " onmouseover="this.style.borderColor='var(--primary)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='none'">
          <span style="font-size:24px"><?= $cat[0] ?></span>
          <?= $cat[1] ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Top Providers -->
  <?php if (!empty($top_providers)): ?>
  <div class="section">
    <div class="section-title">Top Rated Providers</div>
    <div class="section-sub">Trusted by customers across the city</div>
    <div style="display:flex; flex-direction:column; gap:16px">
      <?php foreach ($top_providers as $p): ?>
        <a href="provider-profile.php?id=<?= $p['id'] ?>" class="provider-card">
          <div class="provider-avatar"><?= strtoupper(substr($p['name'],0,1)) ?></div>
          <div class="provider-info">
            <div class="provider-name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="provider-service"><?= htmlspecialchars($p['service_type'] ?? 'General') ?></div>
            <div class="provider-meta">
              <span class="meta-item">📍 <?= htmlspecialchars($p['location'] ?? '') ?></span>
              <span class="meta-item">💼 <?= $p['experience'] ?> yrs exp</span>
              <span class="meta-item stars">
                <?php
                $r = round($p['avg_rating'] ?? 0);
                echo str_repeat('★',$r) . str_repeat('☆',5-$r);
                ?>
                (<?= $p['review_count'] ?>)
              </span>
            </div>
          </div>
          <div>
            <span class="btn btn-outline btn-sm">View Profile</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- How it works -->
  <div class="section">
    <div class="section-title">How It Works</div>
    <div class="grid-3" style="gap:20px; text-align:center">
      <?php $steps = [['🔍','Search','Find the right service provider by type and location'],
                      ['📅','Book','Book your preferred provider with your chosen date and time'],
                      ['⭐','Review','After service, rate and review to help the community']];
      foreach ($steps as $i => $s): ?>
        <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:32px 24px">
          <div style="font-size:36px;margin-bottom:12px"><?= $s[0] ?></div>
          <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;margin-bottom:8px">
            Step <?= $i+1 ?>: <?= $s[1] ?>
          </div>
          <p style="color:var(--muted);font-size:14px"><?= $s[2] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<?php require_once 'includes/footer.php'; ?>
