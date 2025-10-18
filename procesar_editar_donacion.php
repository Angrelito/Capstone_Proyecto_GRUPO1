<?php
require 'conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$id = $_POST['id'] ?? null;
$donante_id = $_POST['donante_id'] ?? null;
$empleado_id = $_POST['empleado_id'] ?? null;
$tipo_donacion = $_POST['tipo_donacion'] ?? '';
$cantidad = $_POST['cantidad'] ?? 1;
$descripcion = $_POST['descripcion'] ?? '';

if (!$id || !$donante_id || !$empleado_id || empty($tipo_donacion)) {
    header("Location: reporte_donaciones.php?error=campos_invalidos");
    exit();
}

$sql = "UPDATE donaciones 
        SET donante_id = ?, empleado_id = ?, tipo_donacion = ?, cantidad = ?, descripcion = ?
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisisi", $donante_id, $empleado_id, $tipo_donacion, $cantidad, $descripcion, $id);

if ($stmt->execute()) {
    header("Location: reporte_donaciones.php?success=editado");
} else {
    header("Location: reporte_donaciones.php?error=editar");
}
exit();
?>