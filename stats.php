<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login(); // Solo para usuarios logueados

$st = $pdo->query("SELECT title, views, likes, dislikes FROM games ORDER BY views DESC LIMIT 20");
$games = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include "header.php"; ?>

<h2>Estadísticas de Juegos (Top 20 más vistos)</h2>

<table style="width:100%; border-collapse: collapse;">
  <thead>
    <tr style="background:#eee;">
      <th style="padding:12px; border:1px solid #ddd; text-align:left;">Juego</th>
      <th style="padding:12px; border:1px solid #ddd; text-align:right;">Vistas</th>
      <th style="padding:12px; border:1px solid #ddd; text-align:right;">Likes 👍</th>
      <th style="padding:12px; border:1px solid #ddd; text-align:right;">Dislikes 👎</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($games)): ?>
        <tr><td colspan="4" style="padding:12px; border:1px solid #ddd; text-align:center;">No hay juegos para mostrar.</td></tr>
    <?php else: ?>
        <?php foreach ($games as $g): ?>
          <tr>
            <td style="padding:12px; border:1px solid #ddd;"><?= e($g['title']) ?></td>
            <td style="padding:12px; border:1px solid #ddd; text-align:right;"><?= (int)$g['views'] ?></td>
            <td style="padding:12px; border:1px solid #ddd; text-align:right;"><?= (int)$g['likes'] ?></td>
            <td style="padding:12px; border:1px solid #ddd; text-align:right;"><?= (int)$g['dislikes'] ?></td>
          </tr>
        <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<?php include "footer.php"; ?>