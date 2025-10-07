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
    // guardamos un mensaje y redirigimos a login
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
    header("Location: login.php");
    exit;
  }
}

function current_user_id() {
  return $_SESSION['user']['id'] ?? null;
}
