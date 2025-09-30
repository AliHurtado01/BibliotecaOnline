<?php
// conexion.php
$usuario = "aliTest";           // 1) Usuario de MySQL (no el email del formulario)
$passUsuario = "qwerty"; // 2) Contraseña de MySQL
$servername = "localhost";   // 3) Host donde corre MySQL (localhost si está en tu PC)
$dbname = "mydb";            // 4) Nombre de tu base de datos

try {                        // 5) Intentamos conectar: si falla, saltará al catch
    // 6) Creamos el DSN (Data Source Name) dentro del new PDO:
    //    - "mysql:" -> driver
    //    - host=$servername -> servidor
    //    - dbname=$dbname   -> base de datos
    //    - charset=utf8mb4  -> codificación recomendada para acentos/emojis
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $usuario, $passUsuario);

    // 7) Configuramos atributos de PDO:
    //    - ERRMODE_EXCEPTION: cuando haya errores de SQL, lanza excepciones (más fácil de manejar)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {  // 8) Si falló la conexión, entramos aquí
    // 9) 'die' detiene el script y muestra un mensaje.
    //    En desarrollo está bien ver el error. En producción, mejor NO mostrar detalles al usuario.
    die("Error de conexión: " . $e->getMessage());
}