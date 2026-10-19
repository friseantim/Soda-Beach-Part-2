<?php
require_once 'includes/auth.php';
require_login();
if (current_user()['role'] === 'staff') { header('Location: admin/dashboard.php'); exit; }

$cottages = $pdo->query('SELECT * FROM cottages ORDER BY price')->fetchAll();
$pass = (float)setting('day_pass_price', 0);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['visit_date'] ?? '';
    $visitors = (int)($_POST['visitors'] ?? 0);
    $cid = (int)($_POST['cottage_id'] ?? 0);
    $qty = $cid ? max(1, (int)($_POST['cottage_qty'] ?? 1)) : 0;
    $d = DateTime::createFromFormat('Y-m-d', $date);

    if (!$d || $d->format('Y-m-d') !== $date || $date < date('Y-m-d')) $error = 'Choose a valid visit date (today or later).';
    elseif ($visitors < 1 || $visitors > 50) $error = 'Visitors must be between 1 and 50.';
    else {
        $pdo->beginTransaction();
        $pdo->query("SELECT v FROM settings WHERE k='daily_capacity' FOR UPDATE"); // serialise concurrent bookings
        $cottage = null;
        if ($cid) { $s = $pdo->prepare('SELECT * FROM cottages WHERE id = ?'); $s->execute([$cid]); $cottage = $s->fetch(); }
        if ($visitors > slots_left($date)) $error = 'Sorry, only ' . slots_left($date) . ' day-pass slots are left for that date.';
        elseif ($cid && !$cottage) $error = 'Invalid cottage.';
        elseif ($cid && $qty > cottage_left($cid, $date)) $error = 'That cottage is not available in that quantity on this date.';

        if ($error) { $pdo->rollBack(); }
        else {
            $ticketTotal = $visitors * $pass;
            $cottageTotal = $cottage ? $cottage['price'] * $qty : 0;
            $code = strtoupper(bin2hex(random_bytes(6)));
            $pdo->prepare('INSERT INTO bookings (user_id,visit_date,visitors,cottage_id,cottage_qty,ticket_total,cottage_total,total,ticket_code) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([current_user()['id'], $date, $visitors, $cid ?: null, $qty, $ticketTotal, $cottageTotal, $ticketTotal + $cottageTotal, $code]);
            $id = $pdo->lastInsertId();
            $pdo->commit();
            header('Location: pay.php?id=' . $id); exit;
        }
    }
}
page_header('Book a visit'); ?>
<main class="container narrow">
  <div class="card">
    <h2>Book your visit</h2>
    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" id="booking-form" data-base="<?= BASE_URL ?>" data-pass="<?= $pass ?>">
      <label>Visit date
        <input type="date" name="visit_date" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['visit_date'] ?? '') ?>" required></label>
      <p class="hint" id="slots">Pick a date to see availability.</p>
      <label>Number of visitors (<?= money($pass) ?> each)
        <input type="number" name="visitors" min="1" max="50" value="<?= e($_POST['visitors'] ?? 1) ?>" required></label>
      <fieldset>
        <legend>Cottage (optional)</legend>
        <label class="opt"><input type="radio" name="cottage_id" value="0" checked> No cottage</label>
        <?php foreach ($cottages as $c): ?>
          <label class="opt"><input type="radio" name="cottage_id" value="<?= $c['id'] ?>" data-price="<?= $c['price'] ?>">
            <?= e($c['name']) ?> · <?= e($c['pax_label']) ?> · <strong><?= money($c['price']) ?></strong>
            <small id="left-<?= $c['id'] ?>"></small></label>
        <?php endforeach; ?>
        <label>Quantity <input type="number" name="cottage_qty" min="1" max="5" value="1"></label>
      </fieldset>
      <p class="total">Total: <strong id="total"><?= money($pass) ?></strong></p>
      <button class="btn" type="submit">Continue to payment</button>
    </form>
  </div>
</main>
<?php page_footer();
