<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Lógica para el "Recuérdame"
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    // Aseguramos que db.php se cargue solo si es necesario
    if (!isset($pdo)) {
      require_once 'db.php';
    }
    
    $token = $_COOKIE['remember_me'];
    $st = $pdo->prepare(
        "SELECT u.* FROM users u
         JOIN remember_tokens rt ON u.id = rt.user_id
         WHERE rt.token = ? AND rt.expires_at > NOW()"
    );
    $st->execute([$token]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Guardamos los datos correctos en la sesión
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'profile_image' => $user['profile_image'] // CORREGIDO: de 'perfilimg' a 'profile_image'
        ];
    } else {
        // Borrar cookie si el token no es válido o ha expirado
        setcookie('remember_me', '', time() - 3600, '/');
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Biblioteca de Videojuegos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* ... (Todos tus estilos CSS van aquí) ... */
    body { font-family: system-ui, Arial, sans-serif; margin: 0; background: #f5f6f8; color: #222 }
    header { background: #4a148c; color: #fff; padding: 12px 16px; display: flex; gap: 16px; align-items: center; justify-content: space-between }
    a { color: #4a148c; text-decoration: none }
    .container { max-width: 980px; margin: 16px auto; padding: 0 12px }
    .btn { display: inline-block; padding: 8px 12px; border-radius: 6px; border: 1px solid #4a148c; cursor: pointer; }
    .btn.primary { background: #4a148c; color: #fff; border-color: #4a148c }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px }
    .card { background: #fff; border: 1px solid #ddd; border-radius: 10px; overflow: hidden }
    .card img { width: 100%; height: 220px; object-fit: cover; background: #eee }
    .card .p { padding: 10px }
    .muted { color: #666; font-size: 0.9rem }
    form .row { display: flex; gap: 12px; flex-wrap: wrap }
    form input, form textarea, form select { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; }
    .error { background: #ffe6e6; color: #a40000; padding: 10px; border: 1px solid #f5b5b5; border-radius: 8px; margin-bottom: 12px }
    .ok { background: #e7ffe7; color: #0a7d0a; padding: 10px; border: 1px solid #a7e6a7; border-radius: 8px; margin-bottom: 12px }
    header nav { display: flex; align-items: center; gap: 16px; }
    header nav a { color: #fff; text-decoration: none; }
    .actions a { margin-right: 8px }
    #livesearch { border-radius: 6px; overflow: hidden; }
    .suggestion-item { display: block; padding: 10px; background-color: #fff; border-bottom: 1px solid #ddd; color: #4a148c; text-decoration: none; }
    .suggestion-item:last-child { border-bottom: none; }
    .suggestion-item:hover { background-color: #f5f6f8; }
    .profile-menu { position: relative; display: inline-block; }
    .profile-menu .avatar { width: 40px; height: 40px; border-radius: 50%; cursor: pointer; object-fit: cover; border: 2px solid #fff; }
    .dropdown-content { display: none; position: absolute; right: 0; background-color: #fff; min-width: 200px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 10; border-radius: 6px; overflow: hidden; }
    .dropdown-content a { color: black; padding: 12px 16px; text-decoration: none; display: block; font-size: 0.95rem; }
    .dropdown-content a:hover { background-color: #f1f1f1; }
    .profile-menu:hover .dropdown-content { display: block; }
  </style>
</head>
<body>
  <header>
    <a href="index.php" style="color:#fff;font-weight:bold;font-size:1.2rem;">🎮 Biblioteca de Videojuegos</a>
    <nav>
      <a href="index.php">Inicio</a>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="game_new.php">Añadir juego</a>
        <a href="stats.php">Estadísticas</a>
        <div class="profile-menu">
          <img src="<?= htmlspecialchars($_SESSION['user']['profile_image'] ?: 'img/avatar_default.png') ?>" alt="Perfil" class="avatar">
          <div class="dropdown-content">
            <a href="edit_profile.php">Editar Perfil</a>
            <a href="logout.php">Cerrar sesión (<?= htmlspecialchars($_SESSION['user']['name']) ?>)</a>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php">Entrar</a>
        <a href="register.php">Registro</a>
      <?php endif; ?>
    </nav>
  </header>
  <div class="container"></div>