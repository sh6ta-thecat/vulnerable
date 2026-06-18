<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
}

echo "<h1>Bienvenido ".$_SESSION['usuario']."</h1>";
?>

<a href="search.php">Buscar usuarios</a>
