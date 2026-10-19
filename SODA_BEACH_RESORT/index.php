<?php
require_once 'includes/auth.php';
$cottages = $pdo->query('SELECT * FROM cottages ORDER BY price')->fetchAll();
page_header('Home'); ?>
<main class="container">
  <section class="hero">
    <h1>Soda Beach Resort</h1>
    <p>Skip the line. Buy your day pass online, reserve a cottage, and walk in with a QR ticket.</p>
    <a class="btn" href="<?= current_user() ? 'book.php' : 'login.php' ?>">Book your visit</a>
  </section>
  <section class="grid">
    <div class="card"><h3>Day Pass</h3><p class="price"><?= money(setting('day_pass_price', 0)) ?></p><p>per person · entrance</p></div>
    <?php foreach ($cottages as $c): ?>
      <div class="card"><h3><?= e($c['name']) ?></h3><p class="price"><?= money($c['price']) ?></p><p>Good for <?= e($c['pax_label']) ?></p></div>
    <?php endforeach; ?>
  </section>
</main>
<?php page_footer();
