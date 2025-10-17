<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();

$user_id = current_user_id();

// Lógica para actualizar el perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Aquí iría la lógica para cambiar nombre, email, contraseña e imagen.
  // Es complejo y requiere validaciones cuidadosas, especialmente con la contraseña.
  // Por ahora, solo mostramos un mensaje.
  set_flash('ok', 'Funcionalidad de actualizar perfil aún no implementada.');
  header("Location: edit_profile.php");
  exit;
}

// Obtener datos del usuario actual
$st = $pdo->prepare("SELECT name, email, profile_image FROM users WHERE id = ?");
$st->execute([$user_id]);
$user = $st->fetch(PDO::FETCH_ASSOC);

?>
<?php include "header.php"; ?>

<h2>Editar Perfil</h2>

<?php if ($ok = flash('ok')): ?>
  <div class="ok"><?= e($ok) ?></div>
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
            <img src="<?= e($user['profile_image']) ?>" alt="Perfil" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">
            <p class="muted">Sube una nueva para reemplazarla (JPG/PNG)</p>
            <input type="file" name="profile_image" accept="image/jpeg,image/png">
        </div>
    </div>
    <br>
    <button class="btn primary" type="submit">Guardar Cambios</button>
</form>

<?php include "footer.php"; ?>