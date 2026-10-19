<?php
require_once 'includes/auth.php';
require_login();

$s = $pdo->prepare('SELECT b.*, c.name AS cottage, u.name AS visitor, p.payment_method, p.transaction_reference_no, p.proof_image 
                    FROM bookings b 
                    JOIN users u ON u.id = b.user_id 
                    LEFT JOIN cottages c ON c.id = b.cottage_id 
                    LEFT JOIN payments p ON p.booking_id = b.id 
                    WHERE b.id = ?');
$s->execute([(int)($_GET['id'] ?? 0)]);
$b = $s->fetch();

if (!$b || ($b['user_id'] != current_user()['id'] && current_user()['role'] !== 'staff')) {
    http_response_code(404);
    exit('Ticket not found.');
}
if ($b['status'] !== 'paid') {
    header('Location: pay.php?id=' . $b['id']);
    exit;
}

page_header('E-Ticket Summary'); ?>
<main class="container narrow">
  <div class="card ticket">
    <h2>Booking Confirmation</h2>
    <p class="hint">Present this ticket code upon gate arrival.</p>
    
    <div style="margin: 1.5rem 0;">
      <small>Ticket Code</small>
      <p class="code" style="font-size: 1.8rem; font-weight: bold; margin: 0.2rem 0;"><?= e($b['ticket_code']) ?></p>
    </div>

    <table class="table">
      <tr><th>Customer Name</th><td><?= e($b['visitor']) ?></td></tr>
      <tr><th>Visit Date</th><td><?= e(date('F j, Y', strtotime($b['visit_date']))) ?></td></tr>
      <tr><th>Day Passes</th><td><?= (int)$b['visitors'] ?> visitor(s)</td></tr>
      <tr><th>Cottage Reserved</th><td><?= $b['cottage'] ? e($b['cottage']) . ' × ' . (int)$b['cottage_qty'] : 'None' ?></td></tr>
      <tr><th>Payment Method</th><td><?= e($b['payment_method'] ?? 'N/A') ?></td></tr>
      <tr><th>Ref Number</th><td><?= e($b['transaction_reference_no'] ?? 'N/A') ?></td></tr>
      <tr class="sum"><th>Total Paid</th><td><strong><?= money($b['total']) ?></strong></td></tr>
      <tr><th>Check-In Status</th><td><?= $b['checked_in_at'] ? 'Checked In at ' . e($b['checked_in_at']) : 'Valid — Pending Gate Arrival' ?></td></tr>
    </table>

    <div style="margin-top: 1.5rem;">
      <button class="btn" onclick="window.print()">Print Ticket</button>
    </div>
  </div>
</main>
<?php page_footer();