<?php
// logout.php
require_once "helpers.php";
session_destroy();
session_start();
set_flash('ok', 'Sesión cerrada correctamente.');
header("Location: login.php");
