<?php
// login.php
require_once "db.php";
require_once "helpers.php";

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

    if (!$u || !password_verify($pass, $u['password_hash'])) {
      $errors[] = "Credenciales incorrectas.";
    } else {
      // login ok
      $_SESSION['user'] = [
        'id' => $u['id'],
        'name' => $u['name'],
        'email' => $u['email']
      ];
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
  <div class="row">
    <div style="flex:1 1 280px">
      <label>Email</label>
      <input type="email" name="email" value="<?= old('email') ?>" required>
    </div>
  </div>
  <div class="row">
    <div style="flex:1 1 240px">
      <label>Contraseña</label>
      <input type="password" name="password" required>
    </div>
  </div>
  <br>
  <button class="btn primary" type="submit">Entrar</button>
</form>
<?php include "footer.php"; ?>
