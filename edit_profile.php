<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();

$user_id = current_user_id();

// --- LÓGICA DE ACTUALIZACIÓN (IMPLEMENTADA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
  // 1. Obtener datos del usuario actual (para comparar)
  $st_current = $pdo->prepare("SELECT email, passhash, profile_image FROM users WHERE id = ?");
  $st_current->execute([$user_id]);
  $currentUser = $st_current->fetch(PDO::FETCH_ASSOC);

  // 2. Recoger datos del formulario
  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $pass2 = $_POST['password2'] ?? '';
  
  $errors = [];

  // 3. Validaciones
  if ($name === '') $errors[] = "El nombre es obligatorio.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido.";

  // Validar email duplicado (solo si es diferente al actual)
  if ($email !== $currentUser['email']) {
    $st_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $st_email->execute([$email]);
    if ($st_email->fetch()) {
      $errors[] = "Ese email ya está registrado por otro usuario.";
    }
  }

  // Validar contraseña (solo si se quiere cambiar)
  $newPassHash = $currentUser['passhash']; // Por defecto, mantiene la antigua
  if ($pass !== '') {
    if (strlen($pass) < 9) $errors[] = "La contraseña debe tener al menos 9 caracteres.";
    if (!preg_match('/[A-Z]/', $pass) || !preg_match('/[a-z]/', $pass)) $errors[] = "La contraseña debe contener mayúsculas y minúsculas.";
    if (!preg_match('/\d/', $pass)) $errors[] = "La contraseña debe contener al menos un número.";
    if (preg_match_all('/[!@#$%^&*()_\-=\[\]{};\':"\\|,.<>\/?]/', $pass) < 2) $errors[] = "La contraseña debe contener al menos 2 caracteres especiales.";
    if ($pass !== $pass2) $errors[] = "Las contraseñas no coinciden.";
    
    // Si no hay errores de contraseña, la hasheamos
    if (empty($errors)) {
        $newPassHash = password_hash($pass, PASSWORD_DEFAULT);
    }
  }

  // 4. Procesar subida de imagen (si hay una nueva)
  $newProfileImagePath = $currentUser['profile_image']; // Por defecto, mantiene la antigua
  
  if (!empty($_FILES['profile_image']['name'])) {
    $f = $_FILES['profile_image'];
    if ($f['error'] === UPLOAD_ERR_OK) {
      $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
      $mime = mime_content_type($f['tmp_name']);
      if (!isset($allowed[$mime])) {
        $errors[] = "Formato de imagen no permitido. Sube JPG o PNG.";
      } else {
        $ext = $allowed[$mime];
        if (!is_dir('uploads/avatars')) mkdir('uploads/avatars', 0777, true);
        $newName = 'uploads/avatars/' . uniqid('avatar_', true) . '.' . $ext;
        
        if (!move_uploaded_file($f['tmp_name'], $newName)) {
          $errors[] = "No se pudo guardar la nueva imagen de perfil.";
        } else {
          // Borrar la imagen antigua (si no es la de por defecto)
          if ($currentUser['profile_image'] && $currentUser['profile_image'] != 'img/avatar_default.png' && file_exists($currentUser['profile_image'])) {
            @unlink($currentUser['profile_image']);
          }
          $newProfileImagePath = $newName;
        }
      }
    } elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
      $errors[] = "Error al subir la imagen de perfil (código " . $f['error'] . ").";
    }
  }

  // 5. Si hay errores, volver atrás
  if (!empty($errors)) {
    set_flash('errors', $errors);
    header("Location: edit_profile.php");
    exit;
  }

  // 6. Actualizar la base de datos
  try {
    $st_update = $pdo->prepare(
      "UPDATE users SET name = ?, email = ?, passhash = ?, profile_image = ? WHERE id = ?"
    );
    $st_update->execute([$name, $email, $newPassHash, $newProfileImagePath, $user_id]);

    // 7. Actualizar la sesión
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['profile_image'] = $newProfileImagePath;

    set_flash('ok', 'Perfil actualizado correctamente.');
    header("Location: edit_profile.php");
    exit;

  } catch (PDOException $e) {
    set_flash('errors', ['Error al actualizar la base de datos: ' . $e->getMessage()]);
    header("Location: edit_profile.php");
    exit;
  }
}
// --- FIN DE LA LÓGICA DE ACTUALIZACIÓN ---


// Obtener datos del usuario actual (para mostrar en el formulario)
$st = $pdo->prepare("SELECT name, email, profile_image FROM users WHERE id = ?");
$st->execute([$user_id]);
$user = $st->fetch(PDO::FETCH_ASSOC);

?>
<?php include "header.php"; ?>

<h2>Editar Perfil</h2>

<?php if ($ok = flash('ok')): ?>
  <div class="ok"><?= e($ok) ?></div>
<?php endif; ?>
<?php if ($msgs = flash('errors')): ?>
  <div class="error"><ul><?php foreach ($msgs as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div class="row">
        <div style="flex:1 1 280px">
            <label>Nombre</label>
            <input type="text" name="name" value="<?= e($user['name']) ?>" required>
        </div>
        <div style="flex:1 1 280px">
            <label>Email</label>
            <input type="email" name="email" value="<?= e($user['email']) ?>" required>
        </div>
    </div>
    <div class="row" style="margin-top:12px">
        <div style="flex:1 1 280px">
            <label>Nueva Contraseña (dejar en blanco para no cambiar)</label>
            <input type="password" name="password">
        </div>
        <div style="flex:1 1 280px">
            <label>Repetir Nueva Contraseña</label>
            <input type="password" name="password2">
        </div>
    </div>
    <div class="row" style="margin-top:12px">
        <div style="flex:1 1 320px">
            <label>Imagen de Perfil Actual</label><br>
            <img src="<?= e($user['profile_image'] ?: 'img/avatar_default.png') ?>" alt="Perfil" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">
            <p class="muted">Sube una nueva para reemplazarla (JPG/PNG)</p>
            <input type="file" name="profile_image" accept="image/jpeg,image/png">
        </div>
    </div>
    <br>
    <button class="btn primary" type="submit">Guardar Cambios</button>
</form>

<?php include "footer.php"; ?>