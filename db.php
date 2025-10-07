<?php
// db.php: conexión a MySQL usando PDO
$DB_HOST = "localhost";
$DB_NAME = "biblioteca";
$DB_USER = "root";         // ajusta si no usas root
$DB_PASS = "";             // ajusta tu contraseña de MySQL

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
  );
} catch (PDOException $e) {
  die("Error de conexión: " . $e->getMessage());
}
