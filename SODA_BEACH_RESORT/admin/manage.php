<?php
require_once '../includes/auth.php';
require_staff();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cap = max(1, (int)$_POST['daily_capacity']); $pass = max(0, (float)$_POST['day_pass_price']);
    $up = $pdo->prepare('UPDATE settings SET v = ? WHERE k = ?');
    $up->execute([$cap, 'daily_capacity']); $up->execute([$pass, 'day_pass_price']);
    $uc = $pdo->prepare('UPDATE cottages SET price = ?, quantity = ? WHERE id = ?');
    foreach ($_POST['price'] as $id => $price) $uc->execute([max(0, (float)$price), max(0, (int)$_POST['quantity'][$id]), (int)$id]);
    $msg = 'Saved.';
}
$cottages = $pdo->query('SELECT * FROM cottages ORDER BY price')->fetchAll();
page_header('Manage', 'admin'); ?>
<main class="container narrow">
  <div class="card">
    <h2>Capacity &amp; pricing</h2>
    <?php if ($msg): ?><p class="alert ok"><?= $msg ?></p><?php endif; ?>
    <form method="post">
      <label>Daily visitor capacity <input type="number" name="daily_capacity" min="1" value="<?= e(setting('daily_capacity')) ?>"></label>
      <label>Day-pass price per person (PHP) <input type="number" step="0.01" name="day_pass_price" value="<?= e(setting('day_pass_price')) ?>"></label>
      <h3>Cottages</h3>
      <table class="table">
        <tr><th>Cottage</th><th>Price (PHP)</th><th>Units available</th></tr>
        <?php foreach ($cottages as $c): ?>
        <tr><td><?= e($c['name']) ?> <small>(<?= e($c['pax_label']) ?>)</small></td>
          <td><input type="number" step="0.01" name="price[<?= $c['id'] ?>]" value="<?= e($c['price']) ?>"></td>
          <td><input type="number" name="quantity[<?= $c['id'] ?>]" min="0" value="<?= e($c['quantity']) ?>"></td></tr>
        <?php endforeach; ?>
      </table>
      <button class="btn" type="submit">Save changes</button>
    </form>
  </div>
</main>
<?php page_footer();
