<?php
require_once 'includes/auth.php';
require_login();

$s = $pdo->prepare('SELECT b.*, c.name AS cottage FROM bookings b LEFT JOIN cottages c ON c.id = b.cottage_id WHERE b.id = ? AND b.user_id = ?');
$s->execute([(int)($_GET['id'] ?? 0), current_user()['id']]);
$b = $s->fetch();

if (!$b) { http_response_code(404); exit('Booking not found.'); }
if ($b['status'] === 'paid') { header('Location: ticket.php?id=' . $b['id']); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = trim($_POST['payment_method'] ?? '');
    $refNo  = trim($_POST['transaction_reference_no'] ?? '');
    
    if (empty($method) || empty($refNo)) {
        $error = 'Please fill in the payment method and reference number.';
    } elseif (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload a valid receipt or proof of payment image.';
    } else {
        $ext = strtolower(pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!in_array($ext, $allowed)) {
            $error = 'Invalid image type. Only JPG, PNG, and WEBP files are allowed.';
        } else {
            $uploadDir = __DIR__ . '/assets/uploads/proofs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'proof_' . $b['id'] . '_' . time() . '.' . $ext;
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetFile)) {
                $pdo->beginTransaction();
                
                // Record in Payment table
                $ins = $pdo->prepare('INSERT INTO payments (booking_id, payment_method, amount_paid, transaction_reference_no, proof_image, payment_status) VALUES (?, ?, ?, ?, ?, ?)');
                $ins->execute([$b['id'], $method, $b['total'], $refNo, $fileName, 'Paid']);
                
                // Update Booking Status
                $up = $pdo->prepare("UPDATE bookings SET status = 'paid', paid_at = NOW() WHERE id = ?");
                $up->execute([$b['id']]);
                
                $pdo->commit();
                header('Location: ticket.php?id=' . $b['id']);
                exit;
            } else {
                $error = 'Failed to upload receipt image. Please try again.';
            }
        }
    }
}

page_header('Payment Proof Upload'); ?>
<main class="container narrow">
  <div class="card">
    <h2>Review &amp; Submit Payment</h2>
    <table class="table">
      <tr><th>Visit date</th><td><?= e(date('F j, Y', strtotime($b['visit_date']))) ?></td></tr>
      <tr><th>Day passes</th><td><?= (int)$b['visitors'] ?> × <?= money($b['ticket_total'] / $b['visitors']) ?> = <?= money($b['ticket_total']) ?></td></tr>
      <?php if ($b['cottage']): ?><tr><th>Cottage</th><td><?= e($b['cottage']) ?> × <?= (int)$b['cottage_qty'] ?> = <?= money($b['cottage_total']) ?></td></tr><?php endif; ?>
      <tr class="sum"><th>Total Due</th><td><strong><?= money($b['total']) ?></strong></td></tr>
    </table>

    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="auth-form" style="margin-top: 1.5rem;">
      <label>Payment Channel
        <select name="payment_method" required>
          <option value="">-- Select Channel --</option>
          <option value="GCash">GCash</option>
          <option value="Maya">Maya</option>
          <option value="Bank Transfer">Bank Transfer</option>
          <option value="Over The Counter">Over The Counter</option>
        </select>
      </label>

      <label>Transaction Reference No.
        <input name="transaction_reference_no" placeholder="e.g. 1002938481" required>
      </label>

      <label>Upload Proof of Payment (Image)
        <input type="file" name="proof_image" accept="image/*" required>
      </label>

      <div class="actions">
        <button class="btn" type="submit">Submit Payment</button>
      </div>
    </form>
  </div>
</main>
<?php page_footer();