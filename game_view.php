<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT g.*, u.name AS owner_name FROM games g JOIN users u ON u.id = g.user_id WHERE g.id = ?");
$st->execute([$id]);
$game = $st->fetch(PDO::FETCH_ASSOC);

if (!$game) {
  include "header.php";
  echo "<div class='error'>Juego no encontrado.</div>";
  include "footer.php";
  exit;
}

$isOwner = is_logged() && current_user_id() === $game['user_id'];
?>
<?php include "header.php"; ?>

<h2><?= e($game['title']) ?> <span class="muted">(<?= (int)$game['year'] ?>)</span></h2>

<div class="row">
  <div style="flex:0 0 280px;max-width:280px">
    <img src="<?= e($game['cover_path'] ?: 'img/default_cover.jpg') ?>" alt="Carátula" style="width:100%;height:auto;border-radius:10px;border:1px solid #ddd">
  </div>
  <div style="flex:1 1 auto">
    <p><strong>Autor/Estudio:</strong> <?= e($game['author']) ?></p>
    <?php if ($game['category']): ?>
      <p><strong>Categoría:</strong> <?= e($game['category']) ?></p>
    <?php endif; ?>
    <?php if ($game['url']): ?>
      <p><strong>URL:</strong> <a href="<?= e($game['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($game['url']) ?></a></p>
    <?php endif; ?>
    <p><strong>Propietario:</strong> <?= e($game['owner_name']) ?></p>
    <p><strong>Descripción:</strong><br><?= nl2br(e($game['description'])) ?></p>

    <?php if ($isOwner): ?>
      <div class="actions" style="margin-top:12px">
        <a class="btn" href="game_edit.php?id=<?= (int)$game['id'] ?>">Editar</a>
        <a class="btn" href="game_delete.php?id=<?= (int)$game['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este juego?');">Eliminar</a>
      </div>
    <?php endif; ?>
    <?php if ($ok = flash('ok')): ?>
      <div class="ok" style="margin-top:12px"><?= e($ok) ?></div>
    <?php endif; ?>
  </div>
</div>

<?php include "footer.php"; ?>
