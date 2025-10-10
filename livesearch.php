<?php
require_once 'db.php';

$q = $_REQUEST["q"] ?? '';
$hint = "";

if ($q !== "") {
    try {
        // CAMBIO 1: Seleccionamos el 'id' y el 'title' de la tabla 'games'.
        $sql = "SELECT id, title FROM games WHERE title LIKE ? LIMIT 10";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$q . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            // CAMBIO 2: Creamos un enlace que apunta a "game_view.php" pasando el ID.
            // También añadimos un 'onclick' para que la caja de sugerencias se oculte al hacer clic.
            $hint .= '<a href="game_view.php?id=' . htmlspecialchars($row['id']) . '" 
                         class="suggestion-item" 
                         onclick="document.getElementById(\'livesearch\').innerHTML = \'\';">
                         ' . htmlspecialchars($row['title']) . '
                      </a>';
        }
    } catch (PDOException $e) {
        $hint = "Error al realizar la búsqueda.";
    }
}

// Si no se encontró nada, se devuelve un texto simple.
// Puedes darle estilo con CSS si quieres.
if ($hint === "" && $q !== "") {
    $hint = "<div class='suggestion-item' style='color:#666;'>No se encontraron sugerencias</div>";
}

echo $hint;
