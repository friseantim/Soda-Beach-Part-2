<?php
require_once '../includes/auth.php';
require_staff();

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $staffId = current_user()['id'];
    
    $s = $pdo->prepare('SELECT b.*, u.name, c.name AS cottage FROM bookings b JOIN users u ON u.id=b.user_id LEFT JOIN cottages c ON c.id=b.cottage_id WHERE b.ticket_code = ?');
    $s->execute([$code]);
    $b = $s->fetch();
    
    if (!$b) {
        $result = ['bad', 'Ticket code not found.'];
    } elseif ($b['status'] !== 'paid') {
        $result = ['bad', 'Ticket is not paid.', $b];
    } elseif ($b['visit_date'] !== date('Y-m-d')) {
        $result = ['bad', 'Ticket is scheduled for ' . date('F j, Y', strtotime($b['visit_date'])) . ', not today.', $b];
    } elseif ($b['checked_in_at']) {
        $result = ['bad', 'Duplicate Entry: Already used at ' . $b['checked_in_at'] . '.', $b];
        
        // Log Duplicate Validation Attempt
        $pdo->prepare('INSERT INTO check_in_logs (booking_id, staff_id, validation_status) VALUES (?, ?, ?)')->execute([$b['id'], $staffId, 'Duplicate']);
    } else {
        // Grant Entry
        $pdo->prepare('UPDATE bookings SET checked_in_at = NOW() WHERE id = ?')->execute([$b['id']]);
        
        // Log Valid Scan
        $pdo->prepare('INSERT INTO check_in_logs (booking_id, staff_id, validation_status) VALUES (?, ?, ?)')->execute([$b['id'], $staffId, 'Valid']);
        
        $result = ['ok', 'Valid ticket — Entry Granted.', $b];
    }
}

page_header('Validate Ticket', 'admin'); ?>
<main class="container narrow">
  <div class="card">
    <h2>Gate Check-In Validation</h2>
    <form method="post" id="validate-form" class="inline" style="margin-bottom: 1rem;">
      <input name="code" id="code" placeholder="ENTER TICKET CODE" autofocus required style="text-transform: uppercase;">
      <button class="btn" type="submit">Verify</button>
    </form>

    <?php if ($result): ?>
      <p class="alert <?= $result[0] === 'ok' ? 'ok' : '' ?>"><?= e($result[1]) ?></p>
      <?php if (!empty($result[2])): $b = $result[2]; ?>
        <table class="table">
          <tr><th>Customer</th><td><?= e($b['name']) ?></td></tr>
          <tr><th>Visitors</th><td><?= (int)$b['visitors'] ?></td></tr>
          <tr><th>Cottage</th><td><?= $b['cottage'] ? e($b['cottage']) . ' × ' . (int)$b['cottage_qty'] : 'None' ?></td></tr>
        </table>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>
<?php page_footer();