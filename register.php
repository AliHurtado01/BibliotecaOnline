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

  // Validaciones de datos básicos
  if ($name === '') $errors[] = "El nombre es obligatorio.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido.";

  // VALIDACIONES DE CONTRASEÑA
  if (strlen($pass) < 9) {
    $errors[] = "La contraseña debe tener al menos 9 caracteres.";
  }
  if (!preg_match('/[A-Z]/', $pass) || !preg_match('/[a-z]/', $pass)) {
    $errors[] = "La contraseña debe contener mayúsculas y minúsculas.";
  }
  if (!preg_match('/\d/', $pass)) {
    $errors[] = "La contraseña debe contener al menos un número.";
  }
  if (preg_match_all('/[!@#$%^&*()_\-=\[\]{};\':"\\|,.<>\/?]/', $pass) < 2) {
    $errors[] = "La contraseña debe contener al menos 2 caracteres especiales.";
  }
  if ($pass !== $re) {
    $errors[] = "Las contraseñas no coinciden.";
  }


  // ¿email ya registrado? (Solo si no hay otros errores)
  if (empty($errors)) {
    $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $st->execute([$email]);
    if ($st->fetch()) {
      $errors[] = "Ese email ya está registrado.";
    }
  }

  // Subida de imagen de perfil
  $profileImagePath = 'img/avatar_default.png'; // Avatar por defecto
  
  // Usamos 'profile_image' para coincidir con el form
  if (!empty($_FILES['profile_image']['name'])) {
    $f = $_FILES['profile_image'];
    if ($f['error'] === UPLOAD_ERR_OK) {
      $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
      $mime = mime_content_type($f['tmp_name']);
      if (!isset($allowed[$mime])) {
        $errors[] = "Formato de imagen de perfil no permitido. Sube JPG o PNG.";
      } else {
        $ext = $allowed[$mime];
        if (!is_dir('uploads/avatars')) mkdir('uploads/avatars', 0777, true);
        $newName = 'uploads/avatars/' . uniqid('avatar_', true) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'], $newName)) {
          $errors[] = "No se pudo guardar la imagen de perfil.";
        } else {
          $profileImagePath = $newName;
        }
      }
    } elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
      $errors[] = "Error al subir la imagen de perfil (código " . $f['error'] . ").";
    }
  }

  // Si hay algún error, volvemos al formulario de registro
  if (!empty($errors)) {
    set_flash('errors', $errors);
    header("Location: register.php");
    exit;
  }

  // Si todo está bien, registramos al usuario
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  // Usamos la columna 'profile_image'
  $st = $pdo->prepare("INSERT INTO users (name, email, passhash, profile_image) VALUES (?, ?, ?, ?)");
  $st->execute([$name, $email, $hash, $profileImagePath]);

  set_flash('ok', "Registro completado. ¡Ahora puedes iniciar sesión!");
  clear_old();
  header("Location: login.php");
  exit;
}
?>
<?php include "header.php"; ?>
<script>
  // Tu script de showHint() no necesita cambios
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

<form method="post" autocomplete="off" enctype="multipart/form-data">
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
      <p style=color:red id="txtLength">Debe tener al menos 9 caracteres</p>
      <p style=color:red id="txtSpecials">Debe contener al menos 2 caracteres especiales</p>
      <p style=color:red id="txtNumber">Debe contener al menos un número</p>
      <p style=color:red id="txtMayusMinus">Debe contener mayúsculas y minúsculas</p>
    </div>
    <div style="flex:1 1 240px">
      <label>Repite la contraseña</label>
      <input type="password" name="password2" required>
    </div>
  </div>
  <div class="row" style="margin-top:12px;">
      <div style="flex:1 1 320px">
        <label>Imagen de Perfil (opcional: jpg/png)</label>
        <input type="file" name="profile_image" accept="image/jpeg,image/png">
        <p class="muted">Si no subes una imagen, usaremos un avatar por defecto.</p>
      </div>
  </div>
  <br>
  <button class="btn primary" type="submit">Crear cuenta</button>
</form>
<?php include "footer.php"; ?>