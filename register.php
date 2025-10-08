<?php
require_once "db.php";
require_once "helpers.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Guardamos "old" para repoblar
  $_SESSION['old'] = $_POST;

  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $re    = $_POST['password2'] ?? '';

  $errors = [];

  if ($name === '') $errors[] = "El nombre es obligatorio.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido.";
  if (strlen($pass) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres.";
  if ($pass !== $re) $errors[] = "Las contraseñas no coinciden.";

  // ¿email ya registrado?
  if (empty($errors)) {
    $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $st->execute([$email]);
    if ($st->fetch()) {
      $errors[] = "Ese email ya está registrado.";
    }
  }

  if (!empty($errors)) {
    set_flash('errors', $errors);
    header("Location: register.php");
    exit;
  }

  // Registrar
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $st = $pdo->prepare("INSERT INTO users (name,email,passhash) VALUES (?,?,?)");
  $st->execute([$name, $email, $hash]);

  set_flash('ok', "Registro completado. ¡Ahora puedes iniciar sesión!");
  clear_old();
  header("Location: login.php");
  exit;
}
?>
<?php include "header.php"; ?>
<script>
  function showHint(str) {
    const lengthRegex = /^.{9,}$/;
    const specialsRegex = /(?:.*[!@#$%^&*()_\-=\[\]{};':"\\|,.<>\/?]){2,}/;
    const numberRegex = /\d/;
    const mayusMinusRegex = /(?=.*[A-Z])(?=.*[a-z])/;

    document.getElementById('txtLength').style.color = lengthRegex.test(str) ? 'green' : 'red';
    document.getElementById('txtSpecials').style.color = specialsRegex.test(str) ? 'green' : 'red';
    document.getElementById('txtNumber').style.color = numberRegex.test(str) ? 'green' : 'red';
    document.getElementById('txtMayusMinus').style.color = mayusMinusRegex.test(str) ? 'green' : 'red';
  }
</script>

<h2>Registro</h2>

<?php if ($msgs = flash('errors')): ?>
  <div class="error">
    <ul><?php foreach ($msgs as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form method="post" autocomplete="off">
  <div class="row">
    <div style="flex:1 1 280px">
      <label>Nombre</label>
      <input type="text" name="name" value="<?= old('name') ?>" required>
    </div>
    <div style="flex:1 1 280px">
      <label>Email</label>
      <input type="email" name="email" value="<?= old('email') ?>" required>
    </div>
  </div>
  <div class="row">
    <div style="flex:1 1 240px">
      <label>Contraseña</label>
      <input type="password" name="password" required onkeyup="showHint(this.value)">
      <p style=color:red id="txtLength">Tiene que tener mas de 8 caracteres</p>
      <p style=color:red id="txtSpecials">Tiene que contener caracteres especiales</p>
      <p style=color:red id="txtNumber">Tiene que contener numeros</p>
      <p style=color:red id="txtMayusMinus">Tiene que contener mayusculas y minusculas</p>
    </div>
    <div style="flex:1 1 240px">
      <label>Repite la contraseña</label>
      <input type="password" name="password2" required>
    </div>
  </div>
  <br>
  <button class="btn primary" type="submit">Crear cuenta</button>
</form>
<?php include "footer.php"; ?>