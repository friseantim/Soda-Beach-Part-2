<?php
require_once 'includes/auth.php';
if (current_user()) { header('Location: ' . (current_user()['role'] === 'staff' ? 'admin/dashboard.php' : 'index.php')); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['user'] ?? ''); $pw = $_POST['password'] ?? '';
    // Visitors log in with email; staff log in with their account name (e.g. Admin)
    $s = $pdo->prepare("SELECT * FROM users WHERE email = ? OR (role = 'staff' AND name = ?) LIMIT 1");
    $s->execute([strtolower($u), $u]);
    $row = $s->fetch();
    if ($row && password_verify($pw, $row['password'])) {
        login_user($row);
        header('Location: ' . ($row['role'] === 'staff' ? 'admin/dashboard.php' : 'index.php')); exit;
    }
    $error = 'Invalid login details.';
}
page_header('Login', 'auth'); ?>
<main class="auth-box">
  <h1>Welcome to Soda Beach Resort</h1>
  <?php if (isset($_GET['registered'])): ?><p class="alert ok">Account created. You can log in now.</p><?php endif; ?>
  <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" class="auth-form">
    <input name="user" placeholder="USER" required autofocus>
    <?= pw_field('password', 'PASSWORD') ?>
    <div class="actions">
      <button class="btn" type="submit">Login</button>
      <a class="btn" href="register.php">Register now</a>
      <a class="link" href="#">Forgot password?</a>
    </div>
  </form>
</main>
<?php page_footer();
