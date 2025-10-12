<?php // header.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Biblioteca de Videojuegos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: system-ui, Arial, sans-serif;
      margin: 0;
      background: #f5f6f8;
      color: #222
    }

    header {
      background: #4a148c;
      color: #fff;
      padding: 12px 16px;
      display: flex;
      gap: 16px;
      align-items: center;
      justify-content: space-between
    }

    a {
      color: #4a148c;
      text-decoration: none
    }

    .container {
      max-width: 980px;
      margin: 16px auto;
      padding: 0 12px
    }

    .btn {
      display: inline-block;
      padding: 8px 12px;
      border-radius: 6px;
      border: 1px solid #4a148c
    }

    .btn.primary {
      background: #4a148c;
      color: #fff;
      border-color: #4a148c
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 16px
    }

    .card {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 10px;
      overflow: hidden
    }

    .card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      background: #eee
    }

    .card .p {
      padding: 10px
    }

    .muted {
      color: #666;
      font-size: 0.9rem
    }

    form .row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap
    }

    form input,
    form textarea,
    form select {
      width: 100%;
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc
    }

    .error {
      background: #ffe6e6;
      color: #a40000;
      padding: 10px;
      border: 1px solid #f5b5b5;
      border-radius: 8px;
      margin-bottom: 12px
    }

    .ok {
      background: #e7ffe7;
      color: #0a7d0a;
      padding: 10px;
      border: 1px solid #a7e6a7;
      border-radius: 8px;
      margin-bottom: 12px
    }

    nav a {
      color: #fff;
      margin-right: 10px
    }

    .actions a {
      margin-right: 8px
    }

    #livesearch {
      border-radius: 6px;
      overflow: hidden;
      /* Para que los bordes redondeados se apliquen a los hijos */
    }

    .suggestion-item {
      display: block;
      /* <-- ¡Esta es la línea clave! Hace que cada enlace ocupe su propia línea. */
      padding: 10px;
      background-color: #fff;
      border-bottom: 1px solid #ddd;
      color: #4a148c;
      text-decoration: none;
    }

    .suggestion-item:last-child {
      border-bottom: none;
      /* Elimina el borde del último elemento */
    }

    .suggestion-item:hover {
      background-color: #f5f6f8;
      /* Un color de fondo sutil al pasar el ratón */
    }
  </style>
</head>

<body>
  <header>
    <div><strong>🎮 Biblioteca de Videojuegos</strong></div>
    <nav>
      <a href="index.php">Inicio</a>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="game_new.php">Añadir juego</a>
        <a href="logout.php">Salir (<?= htmlspecialchars($_SESSION['user']['name']) ?>)</a>
      <?php else: ?>
        <a href="login.php">Entrar</a>
        <a href="register.php">Registro</a>
      <?php endif; ?>
    </nav>
  </header>
  <div class="container">