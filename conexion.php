<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "ONG";

$conn = new mysqli($host, $usuario, $clave, $bd);

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
?>