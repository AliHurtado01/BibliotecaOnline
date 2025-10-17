<?php
require_once "db.php";
require_once "auth.php";

header('Content-Type: application/json');

if (!is_logged()) {
  echo json_encode(['success' => false, 'message' => 'Necesitas iniciar sesión para votar.']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$game_id = $data['game_id'] ?? 0;
$vote_type = $data['vote_type'] ?? 0;

if (!in_array($vote_type, [1, -1]) || $game_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
  exit;
}

$user_id = current_user_id();

try {
  $pdo->beginTransaction();

  $st = $pdo->prepare("SELECT vote_type FROM votes WHERE user_id = ? AND game_id = ?");
  $st->execute([$user_id, $game_id]);
  $existing_vote = $st->fetchColumn();

  if ($existing_vote) {
    echo json_encode(['success' => false, 'message' => 'Ya has votado por este juego.']);
    $pdo->rollBack();
    exit;
  }

  $st_insert = $pdo->prepare("INSERT INTO votes (user_id, game_id, vote_type) VALUES (?, ?, ?)");
  $st_insert->execute([$user_id, $game_id, $vote_type]);

  $column_to_update = ($vote_type == 1) ? 'likes' : 'dislikes';
  $st_update = $pdo->prepare("UPDATE games SET $column_to_update = $column_to_update + 1 WHERE id = ?");
  $st_update->execute([$game_id]);

  $pdo->commit();

  $st_counts = $pdo->prepare("SELECT likes, dislikes FROM games WHERE id = ?");
  $st_counts->execute([$game_id]);
  $counts = $st_counts->fetch(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'message' => '¡Gracias por tu voto!',
    'likes' => $counts['likes'],
    'dislikes' => $counts['dislikes']
  ]);

} catch (PDOException $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}