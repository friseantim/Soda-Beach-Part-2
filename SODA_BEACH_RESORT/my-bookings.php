<?php
require_once 'includes/auth.php';
require_login();
$s = $pdo->prepare('SELECT b.*, c.name AS cottage FROM bookings b LEFT JOIN cottages c ON c.id = b.cottage_id WHERE b.user_id = ? ORDER BY b.created_at DESC');
$s->execute([current_user()['id']]);
$rows = $s->fetchAll();
page_header('My bookings'); ?>
<main class="container">
  <div class="card">
    <h2>My bookings</h2>
    <?php if (!$rows): ?><p>No bookings yet. <a href="book.php">Book your first visit</a>.</p><?php else: ?>
    <div class="scroll"><table class="table">
      <tr><th>#</th><th>Visit date</th><th>Visitors</th><th>Cottage</th><th>Total</th><th>Status</th><th></th></tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td><td><?= e($r['visit_date']) ?></td><td><?= (int)$r['visitors'] ?></td>
        <td><?= $r['cottage'] ? e($r['cottage']) . ' × ' . (int)$r['cottage_qty'] : '—' ?></td>
        <td><?= money($r['total']) ?></td><td><?= status_badge($r['status']) ?></td>
        <td><?php if ($r['status'] === 'paid'): ?><a href="ticket.php?id=<?= $r['id'] ?>">View ticket</a>
            <?php elseif ($r['status'] === 'pending'): ?><a href="pay.php?id=<?= $r['id'] ?>">Pay now</a><?php endif; ?></td>
      </tr>
      <?php endforeach; ?>
    </table></div>
    <?php endif; ?>
  </div>
</main>
<?php page_footer();
