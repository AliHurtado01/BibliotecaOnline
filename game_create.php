<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();

$_SESSION['old'] = $_POST;

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$author = trim($_POST['author'] ?? '');
$category = trim($_POST['category'] ?? '');
$url = trim($_POST['url'] ?? '');
$year = trim($_POST['year'] ?? '');

$errors = [];

// Validaciones
if ($title === '')
  $errors[] = "El título es obligatorio.";
if ($description === '')
  $errors[] = "La descripción es obligatoria.";
if ($author === '')
  $errors[] = "El autor/estudio es obligatorio.";
if (!valid_year($year))
  $errors[] = "El año debe estar entre 1970 y 2100.";
if (!valid_url_or_empty($url))
  $errors[] = "La URL no es válida.";

$coverPath = null;

// Subida de imagen
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
        $coverPath = $newName;
      }
    }
  } else {
    $errors[] = "Error al subir la carátula (código " . $f['error'] . ").";
  }
}

if (!empty($errors)) {
  if ($coverPath && file_exists($coverPath))
    unlink($coverPath);
  set_flash('errors', $errors);
  header("Location: game_new.php");
  exit;
}

try {
  $pdo->beginTransaction();

  $st = $pdo->prepare("
    INSERT INTO games (user_id,title,description,author,cover_path,category,url,year)
    VALUES (?,?,?,?,?,?,?,?)
  ");
  $st->execute([
    current_user_id(),
    $title,
    $description,
    $author,
    $coverPath,
    $category ?: null,
    $url ?: null,
    (int) $year
  ]);

  $newId = $pdo->lastInsertId();
  $pdo->commit();

  clear_old();
  set_flash('ok', 'Videojuego añadido correctamente.');
  header("Location: game_view.php?id=" . urlencode($newId));
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction())
    $pdo->rollBack();

  // Si subimos imagen y luego falla BD, borramos el archivo
  if ($coverPath && file_exists($coverPath))
    unlink($coverPath);

  set_flash('errors', ['Error al guardar el juego: ' . $e->getMessage()]);
  header("Location: game_new.php");
  exit;
}