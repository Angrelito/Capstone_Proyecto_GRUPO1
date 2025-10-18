<?php
require 'dompdf/autoload.inc.php';
require 'conexion.php';

date_default_timezone_set('America/Lima');

use Dompdf\Dompdf;
use Dompdf\Options;

// Configuración de Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// 🔹 Capturar filtros desde GET
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$valores = isset($_GET['valores']) ? json_decode($_GET['valores'], true) : [];

// 🔹 Consulta base
$sql = "SELECT 
            b.id,
            b.nombre,
            b.apellido,
            b.dni,
            b.telefono,
            b.direccion,
            b.tipo_ayuda,
            b.descripcion,
            e.usuario AS empleado,
            b.fecha_registro
        FROM beneficiarios b
        LEFT JOIN empleados e ON b.empleado_id = e.id
        WHERE 1=1";

// 🔹 Aplicar búsqueda
if ($busqueda !== '') {
    $busqueda = $conn->real_escape_string($busqueda);
    $sql .= " AND (
        b.nombre LIKE '%$busqueda%' OR
        b.apellido LIKE '%$busqueda%' OR
        b.dni LIKE '%$busqueda%' OR
        b.tipo_ayuda LIKE '%$busqueda%' OR
        e.usuario LIKE '%$busqueda%'
    )";
}

// 🔹 Aplicar filtros múltiples
if (!empty($valores) && $tipo) {
    $lista = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $valores);
    if ($tipo === 'tipo') $sql .= " AND b.tipo_ayuda IN (" . implode(',', $lista) . ")";
    elseif ($tipo === 'empleado') $sql .= " AND e.usuario IN (" . implode(',', $lista) . ")";
}

$sql .= " ORDER BY b.fecha_registro DESC";
$resultado = $conn->query($sql);

// 🔹 Generar HTML del reporte
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Beneficiarios</title>
<style>
  body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
  h1 { text-align: center; color: #ED582D; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
  th { background-color: #ED582D; color: #fff; }
  img { display: block; margin: 0 auto 10px; width: 100px; }
</style>
</head>
<body>
  <img src="http://localhost/ONG/img/logoprincipal.png" alt="Logo">
  <h1>Traperos de San Pablo</h1>
  <h2 style="text-align:center;">Reporte de Beneficiarios</h2>
  <p><strong>Generado:</strong> ' . date("d/m/Y H:i") . '</p>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>DNI</th>
        <th>Teléfono</th>
        <th>Dirección</th>
        <th>Tipo de Ayuda</th>
        <th>Empleado</th>
        <th>Fecha Registro</th>
        <th>Descripción</th>
      </tr>
    </thead>
    <tbody>';

// 🔹 Agregar filas
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $html .= '<tr>
            <td>'.$row['id'].'</td>
            <td>'.htmlspecialchars($row['nombre']).'</td>
            <td>'.htmlspecialchars($row['apellido']).'</td>
            <td>'.htmlspecialchars($row['dni']).'</td>
            <td>'.htmlspecialchars($row['telefono']).'</td>
            <td>'.htmlspecialchars($row['direccion']).'</td>
            <td>'.htmlspecialchars($row['tipo_ayuda']).'</td>
            <td>'.htmlspecialchars($row['empleado']).'</td>
            <td>'.date("d/m/Y H:i", strtotime($row['fecha_registro'])).'</td>
            <td>'.htmlspecialchars($row['descripcion']).'</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="10">No hay beneficiarios registrados según los filtros aplicados.</td></tr>';
}

$html .= '
    </tbody>
  </table>
  <br><p style="text-align:right;">Traperos de San Pablo © '.date("Y").'</p>
</body>
</html>';

// 🔹 Generar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // ✅ más ancho para todas las columnas
$dompdf->render();

// 🔹 Descargar PDF
$dompdf->stream("reporte_beneficiarios.pdf", ["Attachment" => true]);
?>