<?php
// logout.php
require_once "helpers.php";

// Borrar la cookie de "Recuérdame"
if (isset($_COOKIE['remember_me'])) {
    unset($_COOKIE['remember_me']);
    setcookie('remember_me', '', time() - 3600, '/');
}

session_destroy();
session_start();
set_flash('ok', 'Sesión cerrada correctamente.');
header("Location: login.php");