<?php
// auth.php: funciones de autenticación
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function is_logged() {
  return !empty($_SESSION['user']);
}

function require_login() {
  if (!is_logged()) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
    header("Location: login.php");
    exit;
  }
}

function current_user_id() {
  return $_SESSION['user']['id'] ?? null;
}

function set_remember_cookie($user_id) {
  global $pdo; // 
  $token = bin2hex(random_bytes(32)); // Token seguro
  $expires_in = 60 * 60 * 24 * 30; // 30 días
  $expires_at = date('Y-m-d H:i:s', time() + $expires_in);

  try {
    $st = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $st->execute([$user_id, $token, $expires_at]);
    setcookie('remember_me', $token, [
        'expires' => time() + $expires_in,
        'path' => '/',
        'httponly' => true, // Más seguro
        'samesite' => 'Lax'
    ]);
  } catch (PDOException $e) {
    // No hacer nada si falla, el login normal ya funcionó
  }
}