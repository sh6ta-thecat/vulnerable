<?php
session_start();
include 'conexion.php';

if ($_POST) {

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $stmt = $conexion->prepare(
        "SELECT password
         FROM usuarios
         WHERE usuario=?"
    );

    $stmt->bind_param(
        "s",
        $usuario
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $fila = $resultado->fetch_assoc();

        if (password_verify(
                $password,
                $fila['password']
            )) {

            $_SESSION['usuario'] = $usuario;

            header("Location: welcome.php");
        } else {
            echo "Credenciales incorrectas";
        }
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
