<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();

$id = (int)($_GET['id'] ?? 0);

// Busca y valida propietario
$st = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$st->execute([$id]);
$g = $st->fetch(PDO::FETCH_ASSOC);

if (!$g) {
  set_flash('errors', ["Juego no encontrado."]);
  header("Location: index.php");
  exit;
}

if ((int)$g['user_id'] !== current_user_id()) {
  set_flash('errors', ["No tienes permiso para eliminar este juego."]);
  header("Location: game_view.php?id=".$id);
  exit;
}

// Borra archivo de cover si es propio
if ($g['cover_path'] && file_exists($g['cover_path'])) {
  @unlink($g['cover_path']);
}

// Borra juego
$del = $pdo->prepare("DELETE FROM games WHERE id = ?");
$del->execute([$id]);

set_flash('ok', 'Juego eliminado.');
header("Location: index.php");
