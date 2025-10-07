<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$st->execute([$id]);
$g = $st->fetch(PDO::FETCH_ASSOC);

if (!$g) {
  set_flash('errors', ["Juego no encontrado."]);
  header("Location: index.php");
  exit;
}

if ((int)$g['user_id'] !== current_user_id()) {
  set_flash('errors', ["No tienes permiso para editar este juego."]);
  header("Location: game_view.php?id=".$id);
  exit;
}
?>
<?php include "header.php"; ?>
<h2>Editar videojuego</h2>

<?php if ($msgs = flash('errors')): ?>
  <div class="error"><ul><?php foreach ($msgs as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form action="game_update.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
  <div class="row">
    <div style="flex:2 1 320px">
      <label>Título *</label>
      <input type="text" name="title" value="<?= e($g['title']) ?>" required>
    </div>
    <div style="flex:1 1 160px">
      <label>Año *</label>
      <input type="number" name="year" min="1970" max="2100" value="<?= (int)$g['year'] ?>" required>
    </div>
    <div style="flex:1 1 240px">
      <label>Categoría</label>
      <input type="text" name="category" value="<?= e($g['category']) ?>">
    </div>
  </div>

  <div class="row">
    <div style="flex:1 1 240px">
      <label>Autor/Estudio *</label>
      <input type="text" name="author" value="<?= e($g['author']) ?>" required>
    </div>
    <div style="flex:2 1 320px">
      <label>URL (opcional)</label>
      <input type="url" name="url" value="<?= e($g['url']) ?>">
    </div>
  </div>

  <div class="row">
    <div style="flex:1 1 100%">
      <label>Descripción *</label>
      <textarea name="description" rows="5" required><?= e($g['description']) ?></textarea>
    </div>
  </div>

  <div class="row">
    <div style="flex:1 1 320px">
      <label>Carátula actual</label><br>
      <img src="<?= e($g['cover_path'] ?: 'img/default_cover.jpg') ?>" alt="Carátula" style="width:160px;border:1px solid #ddd;border-radius:8px">
      <p class="muted">Si subes una nueva, reemplazará a la actual. (JPG/PNG)</p>
      <input type="file" name="cover" accept="image/jpeg,image/png">
    </div>
  </div>

  <br>
  <button class="btn primary" type="submit">Actualizar</button>
</form>
<?php include "footer.php"; ?>
