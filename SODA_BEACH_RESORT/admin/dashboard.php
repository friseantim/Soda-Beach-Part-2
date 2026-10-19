<?php
require_once '../includes/auth.php';
require_staff();
$today = date('Y-m-d');

$s = $pdo->prepare("SELECT COALESCE(SUM(visitors),0) v, COALESCE(SUM(CASE WHEN checked_in_at IS NOT NULL THEN visitors END),0) c, COALESCE(SUM(cottage_qty),0) cot FROM bookings WHERE status='paid' AND visit_date = ?");
$s->execute([$today]); $t = $s->fetch();
$r = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM bookings WHERE status='paid' AND DATE(paid_at) = ?");
$r->execute([$today]); $revenue = $r->fetchColumn();
$capacity = (int)setting('daily_capacity', 0);
$totalCottages = (int)$pdo->query('SELECT COALESCE(SUM(quantity),0) FROM cottages')->fetchColumn();
$pct = $capacity ? min(100, round($t['v'] / $capacity * 100)) : 0;

$up = $pdo->prepare("SELECT b.*, u.name, c.name AS cottage FROM bookings b JOIN users u ON u.id=b.user_id LEFT JOIN cottages c ON c.id=b.cottage_id WHERE b.status='paid' AND b.visit_date BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY) ORDER BY b.visit_date, b.id");
$up->execute([$today, $today]); $rows = $up->fetchAll();

page_header('Staff dashboard', 'admin'); ?>
<main class="container">
  <h2>Dashboard · <?= e(date('F j, Y')) ?></h2>
  <section class="grid stats">
    <div class="card"><small>Expected visitors today</small><p class="big"><?= (int)$t['v'] ?></p>
      <div class="bar"><span style="width:<?= $pct ?>%"></span></div><small><?= $pct ?>% of <?= $capacity ?> capacity</small></div>
    <div class="card"><small>Checked in</small><p class="big"><?= (int)$t['c'] ?></p></div>
    <div class="card"><small>Cottages booked today</small><p class="big"><?= (int)$t['cot'] ?> / <?= $totalCottages ?></p></div>
    <div class="card"><small>Revenue today</small><p class="big"><?= money($revenue) ?></p></div>
  </section>
  <div class="card">
    <h3>Upcoming paid bookings (next 7 days)</h3>
    <div class="scroll"><table class="table">
      <tr><th>Date</th><th>Name</th><th>Visitors</th><th>Cottage</th><th>Total</th><th>Ticket</th><th>Entry</th></tr>
      <?php foreach ($rows as $b): ?>
      <tr><td><?= e($b['visit_date']) ?></td><td><?= e($b['name']) ?></td><td><?= (int)$b['visitors'] ?></td>
        <td><?= $b['cottage'] ? e($b['cottage']) . ' × ' . (int)$b['cottage_qty'] : '—' ?></td>
        <td><?= money($b['total']) ?></td><td><?= e($b['ticket_code']) ?></td>
        <td><?= $b['checked_in_at'] ? 'Checked in' : 'Not yet' ?></td></tr>
      <?php endforeach; if (!$rows): ?><tr><td colspan="7">No bookings yet.</td></tr><?php endif; ?>
    </table></div>
  </div>
</main>
<?php page_footer();
