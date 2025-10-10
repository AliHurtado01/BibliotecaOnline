<?php
require_once 'db.php';

// Obtener el término de búsqueda del frontend (parámetro 'q')
$q = $_REQUEST["q"] ?? ''; // Usamos ?? '' para evitar errores si 'q' no existe
$hint = "";

// Solo buscar si el término de búsqueda no está vacío
if ($q !== "") {
    try {
        // 2. La sintaxis de la consulta cambia un poco al usar PDO.
        // La consulta SQL es la misma, pero la forma de ejecutarla es diferente.
        $sql = "SELECT title, url FROM games WHERE title LIKE ? LIMIT 10";

        // Preparamos la sentencia usando el objeto $pdo de tu archivo db.php
        $stmt = $pdo->prepare($sql);

        // Ejecutamos la sentencia, pasando el parámetro directamente.
        // El '%' es el comodín que busca cualquier cosa que comience con el texto.
        $stmt->execute([$q . '%']);

        // Obtenemos todos los resultados como un array asociativo
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recorremos los resultados
        foreach ($results as $row) {
            // Creamos un enlace por cada juego encontrado.
            // Usamos htmlspecialchars para evitar problemas de seguridad (XSS).
            $hint .= '<a href="' . htmlspecialchars($row['url']) . '" class="suggestion-item">' . htmlspecialchars($row['title']) . '</a>';
        }
    } catch (PDOException $e) {
        // En caso de un error en la base de datos, no mostramos el error al usuario final
        // pero podrías registrarlo en un archivo de logs.
        $hint = "Error al realizar la búsqueda.";
    }
}

// Devolvemos el resultado al frontend.
// Si no se encontró nada, se devuelve "No se encontraron sugerencias".
echo $hint === "" ? "No se encontraron sugerencias" : $hint;

?>