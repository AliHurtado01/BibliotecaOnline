<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();

$id = (int)($_POST['id'] ?? 0);

// Comprobamos propietario
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

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$author = trim($_POST['author'] ?? '');
$category = trim($_POST['category'] ?? '');
$url = trim($_POST['url'] ?? '');
$year = trim($_POST['year'] ?? '');

$errors = [];
if ($title === '') $errors[] = "El título es obligatorio.";
if ($description === '') $errors[] = "La descripción es obligatoria.";
if ($author === '') $errors[] = "El autor/estudio es obligatorio.";
if (!valid_year($year)) $errors[] = "El año debe estar entre 1970 y 2100.";
if (!valid_url_or_empty($url)) $errors[] = "La URL no es válida.";

$newCover = null;
$replaceCover = false;

if (!empty($_FILES['cover']['name'])) {
  $f = $_FILES['cover'];
  if ($f['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $mime = mime_content_type($f['tmp_name']);
    if (!isset($allowed[$mime])) {
      $errors[] = "Formato de imagen no permitido. Sube JPG o PNG.";
    } else {
      $ext = $allowed[$mime];
      $newName = 'uploads/' . uniqid('cover_', true) . '.' . $ext;
      if (!move_uploaded_file($f['tmp_name'], $newName)) {
        $errors[] = "No se pudo guardar la carátula.";
      } else {
        $newCover = $newName;
        $replaceCover = true;
      }
    }
  } elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
    $errors[] = "Error al subir la carátula (código ".$f['error'].").";
  }
}

if (!empty($errors)) {
  if ($newCover && file_exists($newCover)) unlink($newCover);
  set_flash('errors', $errors);
  header("Location: game_edit.php?id=".$id);
  exit;
}

// Si hay nueva cover, borro la anterior (si existía y no es la default)
if ($replaceCover) {
  if ($g['cover_path'] && file_exists($g['cover_path'])) {
    // No borres si estuviera en /img/default_cover.jpg (no debería estar en cover_path)
    @unlink($g['cover_path']);
  }
}

$st = $pdo->prepare("UPDATE games
  SET title=?, description=?, author=?, cover_path=?, category=?, url=?, year=?, updated_at=NOW()
  WHERE id=?");

$st->execute([
  $title,
  $description,
  $author,
  $replaceCover ? $newCover : $g['cover_path'], // deja igual si no hay nueva
  $category ?: null,
  $url ?: null,
  (int)$year,
  $id
]);

set_flash('ok', 'Juego actualizado correctamente.');
header("Location: game_view.php?id=".$id);
