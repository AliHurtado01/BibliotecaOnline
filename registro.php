<?php
session_start();
require "conexion.php";
$nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$pass = isset($_SESSION['pass']) ? $_SESSION['pass'] : '';
?>
<html>
<body>

<form action="registro.php" method="post">
    Nombre: <input type="text" name="nombre" value="<?php echo $nombre?>"><br>
    E-mail: <input type="text" name="email" value="<?php echo $email?>"><br>
    Pass: <input type="text" name="pass" value="<?php echo $pass?>"><br>
    Comprobar Pass: <input type="text" name="pass2" value="<?php echo $pass?>"><br>
<input type="submit">
</form>

<?php
if(isset($_SESSION['error'])){
    echo "ERROR: " . $_SESSION['error'];
    $_SESSION['error'] = null;
}
?>

</body>
</html>

<?php

if(!empty($_POST)){
    $_SESSION['nombre'] = $_POST['nombre'];
    $_SESSION['email'] = $_POST['email'];
    $_SESSION['pass'] = $_POST['pass'];
    if($_POST['pass'] !== $_POST['pass2']){
        $_SESSION['error'] = 'Las contraseñas no coinciden.';
        $_SESSION['pass'] = '';
    }
    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        $_SESSION['error'] .= ' El email es invalido.';
        $_SESSION['email'] = '';
    }
    if(empty($_POST['nombre'])){
        $_SESSION['error'] .= ' El nombre es obligatorio.';
        $_SESSION['nombre'] = '';
    }
    if(isset($_SESSION['error']) && $_SESSION['error'] != ''){
        header('Location: registro.php');
    }

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password)
    VALUES (:nombre, :email, :password)");
    $stmt->bindParam(':nombre', $_SESSION['nombre']);
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->bindParam(':password', $_SESSION['pass']);
    $stmt->execute();
    
    echo 'Usuario añadido correctamente.';
}