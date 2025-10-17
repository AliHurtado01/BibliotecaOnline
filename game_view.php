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

// Incrementar contador de vistas
$st_update_views = $pdo->prepare("UPDATE games SET views = views + 1 WHERE id = ?");
$st_update_views->execute([$id]);

$isOwner = is_logged() && current_user_id() === $game['user_id'];
?>
<?php include "header.php"; ?>

<h2><?= e($game['title']) ?> <span class="muted">(<?= (int)$game['year'] ?>)</span></h2>

<div class="row">
  <div style="flex:0 0 280px;max-width:280px">
    <img src="<?= e($game['cover_path'] ?: 'img/default_cover.jpg') ?>" alt="Carátula" style="width:100%;height:auto;border-radius:10px;border:1px solid #ddd">
  </div>
  <div style="flex:1 1 auto; padding-left: 20px;">
    <p><strong>Autor/Estudio:</strong> <?= e($game['author']) ?></p>
    <?php if ($game['category']): ?><p><strong>Categoría:</strong> <?= e($game['category']) ?></p><?php endif; ?>
    <?php if ($game['url']): ?><p><strong>URL:</strong> <a href="<?= e($game['url']) ?>" target="_blank"><?= e($game['url']) ?></a></p><?php endif; ?>
    <p><strong>Propietario:</strong> <?= e($game['owner_name']) ?></p>
    <p><strong>Descripción:</strong><br><?= nl2br(e($game['description'])) ?></p>

    <?php if (is_logged()): ?>
      <div class="voting" style="margin-top:20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
        <p><strong>Puntuación:</strong> <span id="score" style="font-weight:bold;"><?= (int)($game['likes'] - $game['dislikes']) ?></span></p>
        <button class="btn" onclick="vote(<?= (int)$game['id'] ?>, 1)">Me gusta 👍 (<span id="likes-count"><?= (int)$game['likes'] ?></span>)</button>
        <button class="btn" onclick="vote(<?= (int)$game['id'] ?>, -1)">No me gusta 👎 (<span id="dislikes-count"><?= (int)$game['dislikes'] ?></span>)</button>
        <div id="vote-message" style="margin-top:10px; font-weight: bold;"></div>
      </div>
    <?php endif; ?>

    <?php if ($isOwner): ?>
      <div class="actions" style="margin-top:20px"><a class="btn" href="game_edit.php?id=<?= (int)$game['id'] ?>">Editar</a><a class="btn" href="game_delete.php?id=<?= (int)$game['id'] ?>" onclick="return confirm('¿Seguro?');">Eliminar</a></div>
    <?php endif; ?>
    <?php if ($ok = flash('ok')): ?><div class="ok" style="margin-top:12px"><?= e($ok) ?></div><?php endif; ?>
  </div>
</div>

<?php if (is_logged()): ?>
<script>
function vote(gameId, voteType) {
  const messageDiv = document.getElementById('vote-message');
  messageDiv.textContent = 'Votando...';
  fetch('vote.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ game_id: gameId, vote_type: voteType })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('likes-count').textContent = data.likes;
      document.getElementById('dislikes-count').textContent = data.dislikes;
      document.getElementById('score').textContent = data.likes - data.dislikes;
      messageDiv.style.color = 'green';
    } else {
      messageDiv.style.color = 'red';
    }
    messageDiv.textContent = data.message;
  })
  .catch(error => {
    messageDiv.style.color = 'red';
    messageDiv.textContent = 'Error de conexión.';
  });
}
</script>
<?php endif; ?>

<?php include "footer.php"; ?>