<?php
session_start();
include 'conexion.php';

if ($_POST) {

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $sql = "SELECT *
            FROM usuarios
            WHERE usuario='$usuario'
            AND password='$password'";

    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {

        $_SESSION['usuario'] = $usuario;

        header("Location: welcome.php");
    } else {
        echo "Credenciales incorrectas";
    }
}
?>

<form method="post">
    Usuario:
    <input type="text" name="usuario">

    Password:
    <input type="password" name="password">

    <button>Ingresar</button>
</form>
