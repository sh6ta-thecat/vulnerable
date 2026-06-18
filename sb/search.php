<?php
include 'conexion.php';

if (isset($_GET['buscar'])) {

    $texto = "%".$_GET['buscar']."%";

    $stmt = $conexion->prepare(
        "SELECT usuario
         FROM usuarios
         WHERE usuario LIKE ?"
    );

    $stmt->bind_param(
        "s",
        $texto
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    while($fila = $resultado->fetch_assoc()) {
        echo htmlspecialchars(
            $fila['usuario']
        )."<br>";
    }
}
?>

<form>
    Buscar:
    <input type="text" name="buscar">
    <button>Buscar</button>
</form>
