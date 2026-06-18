<?php
include 'conexion.php';

if (isset($_GET['buscar'])) {

    $texto = $_GET['buscar'];

    $sql = "SELECT *
            FROM usuarios
            WHERE usuario LIKE '%$texto%'";

    $resultado = $conexion->query($sql);

    while($fila = $resultado->fetch_assoc()) {
        echo $fila['usuario']."<br>";
    }
}
?>

<form>
    Buscar:
    <input type="text" name="buscar">
    <button>Buscar</button>
</form>
