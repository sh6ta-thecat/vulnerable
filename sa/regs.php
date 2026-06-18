<?php
include 'conexion.php';

if ($_POST) {

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $sql = "INSERT INTO usuarios(usuario,password)
            VALUES('$usuario','$password')";

    $conexion->query($sql);

    echo "Usuario registrado";
}
?>

<form method="post">
    Usuario:
    <input type="text" name="usuario">

    Password:
    <input type="password" name="password">

    <button>Registrar</button>
</form>
