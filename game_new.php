<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login(); // Solo logueados

?>
<?php include "header.php"; ?>
<h2>Añadir videojuego</h2>

<?php if ($msgs = flash('errors')): ?>
  <div class="error"><ul><?php foreach ($msgs as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form action="game_create.php" method="post" enctype="multipart/form-data">
  <div class="row">
    <div style="flex:2 1 320px">
      <label>Título *</label>
      <input type="text" name="title" value="<?= old('title') ?>" required>
    </div>
    <div style="flex:1 1 160px">
      <label>Año *</label>
      <input type="number" name="year" min="1970" max="2100" value="<?= old('year') ?>">
    </div>
    <div style="flex:1 1 240px">
      <label>Categoría</label>
      <input type="text" name="category" value="<?= old('category') ?>" placeholder="Acción, RPG, etc.">
    </div>
  </div>

  <div class="row">
    <div style="flex:1 1 240px">
      <label>Autor/Estudio *</label>
      <input type="text" name="author" value="<?= old('author') ?>" required>
    </div>
    <div style="flex:2 1 320px">
      <label>URL (opcional)</label>
      <input type="url" name="url" value="<?= old('url') ?>" placeholder="https://...">
    </div>
  </div>

  <div class="row">
    <div style="flex:1 1 100%">
      <label>Descripción *</label>
      <textarea name="description" rows="5" required><?= old('description') ?></textarea>
    </div>
  </div>

  <div class="row">
    <div style="flex:1 1 320px">
      <label>Carátula (opcional: jpg/png)</label>
      <input type="file" name="cover" accept="image/jpeg,image/png">
      <p class="muted">Si no subes carátula, pondremos una por defecto.</p>
    </div>
  </div>

  <br>
  <button class="btn primary" type="submit">Guardar</button>
</form>
<?php include "footer.php"; ?>
