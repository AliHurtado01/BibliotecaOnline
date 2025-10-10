<?php
require_once "db.php";
require_once "helpers.php";

$st = $pdo->query("SELECT id, title, year, cover_path FROM games ORDER BY created_at DESC");
$games = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include "header.php"; ?>

<h2>Catálogo</h2>
<p class="muted">Resumen: título, año y carátula. Haz clic para ver los detalles.</p>
<script>
function showResult(str) {
  if (str.length == 0) {
    document.getElementById("livesearch").innerHTML = "";
    document.getElementById("livesearch").style.border = "0px";
    return;
  }
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("livesearch").innerHTML = this.responseText;
      document.getElementById("livesearch").style.border = "1px solid #A5ACB2";
    }
  }
  xmlhttp.open("GET", "livesearch.php?q=" + str, true);
  xmlhttp.send();
}
</script>
</head>
<body>
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

<?php include "footer.php"; ?>
