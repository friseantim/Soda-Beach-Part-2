<?php
require_once '../includes/auth.php';
require_staff();
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-t');
$days = max(1, (int)((strtotime($to) - strtotime($from)) / 86400) + 1);

$q = function ($sql, $p) use ($pdo) { $s = $pdo->prepare($sql); $s->execute($p); return $s->fetchAll(); };

$daily = $q("SELECT visit_date d, SUM(visitors) v, SUM(CASE WHEN checked_in_at IS NOT NULL THEN visitors ELSE 0 END) ci FROM bookings WHERE status='paid' AND visit_date BETWEEN ? AND ? GROUP BY visit_date ORDER BY visit_date", [$from, $to]);
$occ = $q("SELECT c.name, c.quantity, COALESCE(SUM(b.cottage_qty),0) booked FROM cottages c LEFT JOIN bookings b ON b.cottage_id=c.id AND b.status='paid' AND b.visit_date BETWEEN ? AND ? GROUP BY c.id ORDER BY c.price", [$from, $to]);
$rev = $q("SELECT DATE(paid_at) d, SUM(ticket_total) t, SUM(cottage_total) c, SUM(total) tot FROM bookings WHERE status='paid' AND DATE(paid_at) BETWEEN ? AND ? GROUP BY DATE(paid_at) ORDER BY d", [$from, $to]);
$peak = $q("SELECT visit_date d, SUM(visitors) v FROM bookings WHERE status='paid' AND visit_date BETWEEN ? AND ? GROUP BY visit_date ORDER BY v DESC LIMIT 10", [$from, $to]);
$maxV = max(array_merge([1], array_map('intval', array_column($daily, 'v'))));
$grand = array_sum(array_column($rev, 'tot'));

page_header('Reports', 'admin'); ?>
<main class="container">
  <h2>Reports</h2>
  <form method="get" class="inline card">
    <label>From <input type="date" name="from" value="<?= e($from) ?>"></label>
    <label>To <input type="date" name="to" value="<?= e($to) ?>"></label>
    <button class="btn" type="submit">Apply</button>
    <button class="btn" type="button" onclick="window.print()">Print</button>
  </form>

  <div class="card"><h3>Daily visitors</h3><div class="scroll"><table class="table">
    <tr><th>Date</th><th>Visitors</th><th>Checked in</th><th></th></tr>
    <?php foreach ($daily as $r): ?><tr><td><?= e($r['d']) ?></td><td><?= (int)$r['v'] ?></td><td><?= (int)$r['ci'] ?></td>
      <td class="barcell"><div class="bar"><span style="width:<?= round($r['v'] / $maxV * 100) ?>%"></span></div></td></tr>
    <?php endforeach; if (!$daily): ?><tr><td colspan="4">No data in this range.</td></tr><?php endif; ?>
  </table></div></div>

  <div class="card"><h3>Cottage occupancy</h3><p class="hint">Occupancy = cottage-days booked ÷ (units × <?= $days ?> days in range)</p><table class="table">
    <tr><th>Cottage</th><th>Units</th><th>Cottage-days booked</th><th>Occupancy</th></tr>
    <?php foreach ($occ as $o): $cap = $o['quantity'] * $days; $p = $cap ? round($o['booked'] / $cap * 100, 1) : 0; ?>
    <tr><td><?= e($o['name']) ?></td><td><?= (int)$o['quantity'] ?></td><td><?= (int)$o['booked'] ?></td><td><?= $p ?>%</td></tr>
    <?php endforeach; ?>
  </table></div>

  <div class="card"><h3>Revenue (by payment date)</h3><div class="scroll"><table class="table">
    <tr><th>Date</th><th>Day passes</th><th>Cottages</th><th>Total</th></tr>
    <?php foreach ($rev as $r): ?><tr><td><?= e($r['d']) ?></td><td><?= money($r['t']) ?></td><td><?= money($r['c']) ?></td><td><?= money($r['tot']) ?></td></tr>
    <?php endforeach; ?>
    <tr class="sum"><th colspan="3">Total revenue</th><th><?= money($grand) ?></th></tr>
  </table></div></div>

  <div class="card"><h3>Peak dates (top 10)</h3><table class="table">
    <tr><th>#</th><th>Date</th><th>Visitors</th></tr>
    <?php foreach ($peak as $i => $p): ?><tr><td><?= $i + 1 ?></td><td><?= e(date('D, M j, Y', strtotime($p['d']))) ?></td><td><?= (int)$p['v'] ?></td></tr><?php endforeach; ?>
  </table></div>
</main>
<?php page_footer();
