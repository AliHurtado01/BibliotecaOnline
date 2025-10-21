<?php
require_once "db.php";
require_once "helpers.php";

$st = $pdo->query("SELECT id, title, year, cover_path FROM games ORDER BY created_at DESC");
$games = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include "header.php"; ?>

<?php if ($msgs = flash('errors')): ?>
  <div class="error"><ul><?php foreach ($msgs as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<?php if ($ok = flash('ok')): ?>
  <div class="ok"><?= e($ok) ?></div>
<?php endif; ?>


<h2>Catálogo</h2>
<p class="muted">Resumen: título, año y carátula. Haz clic para ver los detalles.</p>

<form>
  <input type="text" size="30" onkeyup="showResult(this.value)" placeholder="Escribe el nombre de un juego...">
  <div id="livesearch"></div>
</form><br>
<div class="grid">
  <?php foreach ($games as $g): ?>
    <div class="card">
      <a href="game_view.php?id=<?= (int)$g['id'] ?>">
        <img src="<?= e($g['cover_path'] ?: 'img/default_cover.jpg') ?>" alt="Carátula">
      </a>
      <div class="p">
        <div><strong><?= e($g['title']) ?></strong></div>
        <div class="muted"><?= (int)$g['year'] ?></div>
        <div style="margin-top:8px">
          <a class="btn" href="game_view.php?id=<?= (int)$g['id'] ?>">Ver</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>


<?php ?>
<script>
function showResult(str) {
  if (str.length == 0) {
    document.getElementById("livesearch").innerHTML = "";
    document.getElementById("livesearch").style.border = "0px";
    return;
  }
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    // Se ejecuta cuando la petición termina (no importa si fue exitosa o no)
    if (this.readyState == 4) {
      document.getElementById("livesearch").style.border = "1px solid #A5ACB2";
      // Si el estado es 200 (OK), mostramos la respuesta
      if (this.status == 200) {
        document.getElementById("livesearch").innerHTML = this.responseText;
      } else {
        // Si hay un error (ej: 404, 500), mostramos un mensaje de error
        document.getElementById("livesearch").innerHTML = "<div class='suggestion-item' style='color:red;'>Error al realizar la búsqueda.</div>";
      }
    }
  }
  xmlhttp.open("GET", "livesearch.php?q=" + str, true);
  xmlhttp.send();
}
</script>

<?php include "footer.php"; ?>