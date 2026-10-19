<?php
require_once 'includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? ''); $email = strtolower(trim($_POST['email'] ?? ''));
    $pw = $_POST['password'] ?? ''; $pw2 = $_POST['confirm'] ?? '';
    if ($name === '' || $address === '' || $contact === '') $error = 'Please fill in all fields.';
    elseif (strtolower($name) === 'admin') $error = 'That name is reserved.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email address.';
    elseif (strlen($pw) < 6) $error = 'Password must be at least 6 characters.';
    elseif ($pw !== $pw2) $error = 'Passwords do not match.';
    else {
        $c = $pdo->prepare('SELECT 1 FROM users WHERE email = ?'); $c->execute([$email]);
        if ($c->fetch()) $error = 'That email is already registered.';
        else {
            $pdo->prepare('INSERT INTO users (name,address,contact,email,password) VALUES (?,?,?,?,?)')
                ->execute([$name, $address, $contact, $email, password_hash($pw, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1'); exit;
        }
    }
}
page_header('Register', 'auth'); ?>
<main class="auth-box">
  <h1>Get started with us</h1>
  <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" class="auth-form">
    <input name="name" placeholder="NAME" value="<?= e($_POST['name'] ?? '') ?>" required>
    <input name="address" placeholder="ADDRESS" value="<?= e($_POST['address'] ?? '') ?>" required>
    <input name="contact" placeholder="CONTACT" value="<?= e($_POST['contact'] ?? '') ?>" required>
    <input type="email" name="email" placeholder="EMAIL" value="<?= e($_POST['email'] ?? '') ?>" required>
    <?= pw_field('password', 'PASSWORD') ?>
    <?= pw_field('confirm', 'CONFIRM PASSWORD') ?>
    <div class="actions"><button class="btn" type="submit">Register</button>
    <a class="link" href="login.php">Already have an account? Login</a></div>
  </form>
</main>
<?php page_footer();
