<?php
include 'conexion.php';

if ($_POST) {

    $usuario = $_POST['usuario'];

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $stmt = $conexion->prepare(
        "INSERT INTO usuarios(usuario,password)
         VALUES(?,?)"
    );

    $stmt->bind_param(
        "ss",
        $usuario,
        $password
    );

    $stmt->execute();

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
