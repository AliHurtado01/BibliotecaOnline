<?php
// helpers.php: funciones de apoyo

// Arranca la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Muestra y limpia mensajes "flash" (errores o éxito)
function flash($key) {
  if (!empty($_SESSION[$key])) {
    $msg = $_SESSION[$key];
    unset($_SESSION[$key]);
    return $msg;
  }
  return null;
}

// Guarda "flash"
function set_flash($key, $value) {
  $_SESSION[$key] = $value;
}

// Devuelve el valor anterior del formulario (old input)
function old($key, $default = '') {
  if (isset($_SESSION['old'][$key])) {
    $v = $_SESSION['old'][$key];
    // no lo borramos aún para poder reusar si recarga
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
  }
  return htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
}

// Limpia old inputs
function clear_old() {
  unset($_SESSION['old']);
}

// Protege campos de texto simples
function e($str) {
  return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Valida año razonable (por ejemplo 1970..2100)
function valid_year($y) {
  return ctype_digit($y) && (int)$y >= 1970 && (int)$y <= 2100;
}

// Comprueba URL válida (opcional)
function valid_url_or_empty($u) {
  if ($u === '' || $u === null) return true;
  return filter_var($u, FILTER_VALIDATE_URL) !== false;
}
