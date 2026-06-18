<?php

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "prueba_sql"
);

if ($conexion->connect_error) {
    die("Error de conexión");
}
