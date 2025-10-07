<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();

$_SESSION['old'] = $_POST; // por si falla validación

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$author = trim($_POST['author'] ?? '');
$category = trim($_POST['category'] ?? '');
$url = trim($_POST['url'] ?? '');
$year = trim($_POST['year'] ?? '');

$errors = [];

// Validaciones básicas
if ($title === '') $errors[] = "El título es obligatorio.";
if ($description === '') $errors[] = "La descripción es obligatoria.";
if ($author === '') $errors[] = "El autor/estudio es obligatorio.";
if (!valid_year($year)) $errors[] = "El año debe estar entre 1970 y 2100.";
if (!valid_url_or_empty($url)) $errors[] = "La URL no es válida.";

$coverPath = null; // si no hay, luego usamos default

// Procesamos imagen si el usuario subió una
if (!empty($_FILES['cover']['name'])) {
  $f = $_FILES['cover'];
  if ($f['error'] === UPLOAD_ERR_OK) {
    // Validar tipo
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $mime = mime_content_type($f['tmp_name']);
    if (!isset($allowed[$mime])) {
      $errors[] = "Formato de imagen no permitido. Sube JPG o PNG.";
    } else {
      // Nombre único
      $ext = $allowed[$mime];
      $newName = 'uploads/' . uniqid('cover_', true) . '.' . $ext;
      if (!move_uploaded_file($f['tmp_name'], $newName)) {
        $errors[] = "No se pudo guardar la carátula.";
      } else {
        $coverPath = $newName;
      }
    }
  } else {
    $errors[] = "Error al subir la carátula (código ".$f['error'].").";
  }
}

if (!empty($errors)) {
  // si subimos una imagen pero luego hay errores, conviene borrarla
  if ($coverPath && file_exists($coverPath)) unlink($coverPath);
  set_flash('errors', $errors);
  header("Location: game_new.php");
  exit;
}

// Insertar
$st = $pdo->prepare("INSERT INTO games (user_id,title,description,author,cover_path,category,url,year) VALUES (?,?,?,?,?,?,?,?)");
$st->execute([
  current_user_id(),
  $title,
  $description,
  $author,
  $coverPath,               // puede ser NULL; en vista usamos default
  $category ?: null,
  $url ?: null,
  (int)$year
]);

clear_old();
set_flash('ok', 'Videojuego añadido correctamente.');
header("Location: index.php");
