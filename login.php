<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['old'] = $_POST;
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $errors = [];

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email no válido.";
  if ($pass === '') $errors[] = "Debes introducir la contraseña.";

  if (empty($errors)) {
    $st = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $st->execute([$email]);
    $u = $st->fetch(PDO::FETCH_ASSOC);

    if (!$u || !password_verify($pass, $u['passhash'])) {
      $errors[] = "Credenciales incorrectas.";
    } else {
      $_SESSION['user'] = [
        'id' => $u['id'],
        'name' => $u['name'],
        'email' => $u['email'],
        'profile_image' => $u['profile_image']
      ];

      if (isset($_POST['remember'])) {
        set_remember_cookie($u['id']);
      }

      clear_old();
      $to = $_SESSION['redirect_to'] ?? 'index.php';
      unset($_SESSION['redirect_to']);
      header("Location: $to");
      exit;
    }
  }
  set_flash('errors', $errors);
  header("Location: login.php");
  exit;
}
?>
<?php include "header.php"; ?>
<h2>Iniciar sesión</h2>
<?php if ($msgs = flash('errors')): ?>
  <div class="error"><ul><?php foreach ($msgs as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<?php if ($ok = flash('ok')): ?>
  <div class="ok"><?= e($ok) ?></div>
<?php endif; ?>
<form method="post" autocomplete="off">
  <div class="row"><div style="flex:1 1 280px"><label>Email</label><input type="email" name="email" value="<?= old('email') ?>" required></div></div>
  <div class="row"><div style="flex:1 1 240px"><label>Contraseña</label><input type="password" name="password" required></div></div>
  <div class="row" style="margin-top:12px;"><div style="flex:1 1 240px"><label><input type="checkbox" name="remember" style="width:auto;"> Recuérdame</label></div></div>
  <br>
  <button class="btn primary" type="submit">Entrar</button>
</form>
<?php include "footer.php"; ?>