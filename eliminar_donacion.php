<?php
require 'conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: reporte_donaciones.php?alert=error&msg=Falta%20el%20ID%20de%20la%20donación");
    exit();
}

$sql = "DELETE FROM donaciones WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: reporte_donaciones.php?alert=success&msg=✅%20Donación%20eliminada%20correctamente");
} else {
    header("Location: reporte_donaciones.php?alert=error&msg=❌%20No%20se%20pudo%20eliminar%20la%20donación");
}
exit();
?>