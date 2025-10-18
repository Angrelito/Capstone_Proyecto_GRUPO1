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
          d.id,
          dn.nombre AS nombre_donante,
          dn.donacion AS objeto_donado,
          e.usuario AS usuario_empleado,
          d.tipo_donacion,
          d.cantidad,
          d.descripcion,
          d.fecha
        FROM donaciones d
        LEFT JOIN donantes dn ON d.donante_id = dn.id
        LEFT JOIN empleados e ON d.empleado_id = e.id
        WHERE 1=1";

// 🔹 Aplicar búsqueda
if ($busqueda !== '') {
    $busqueda = $conn->real_escape_string($busqueda);
    $sql .= " AND (
        dn.nombre LIKE '%$busqueda%' OR
        dn.donacion LIKE '%$busqueda%' OR
        d.tipo_donacion LIKE '%$busqueda%' OR
        e.usuario LIKE '%$busqueda%'
    )";
}

// 🔹 Aplicar filtros múltiples
if (!empty($valores) && $tipo) {
    $lista = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $valores);
    if ($tipo === 'categoria') $sql .= " AND d.tipo_donacion IN (" . implode(',', $lista) . ")";
    elseif ($tipo === 'objeto') $sql .= " AND dn.donacion IN (" . implode(',', $lista) . ")";
    elseif ($tipo === 'donante') $sql .= " AND dn.nombre IN (" . implode(',', $lista) . ")";
    elseif ($tipo === 'empleado') $sql .= " AND e.usuario IN (" . implode(',', $lista) . ")";
}

$sql .= " ORDER BY d.fecha DESC";
$resultado = $conn->query($sql);

// Generar HTML del reporte
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Donaciones</title>
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
  <h2 style="text-align:center;">Reporte de Donaciones</h2>
  <p><strong>Generado:</strong> ' . date("d/m/Y H:i") . '</p>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Donante</th>
        <th>Objeto</th>
        <th>Categoría</th>
        <th>Cantidad</th>
        <th>Empleado</th>
        <th>Descripción</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>';

// Agregar filas
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $html .= '<tr>
            <td>'.$row['id'].'</td>
            <td>'.htmlspecialchars($row['nombre_donante']).'</td>
            <td>'.htmlspecialchars($row['objeto_donado']).'</td>
            <td>'.htmlspecialchars($row['tipo_donacion']).'</td>
            <td>'.$row['cantidad'].'</td>
            <td>'.htmlspecialchars($row['usuario_empleado']).'</td>
            <td>'.htmlspecialchars($row['descripcion']).'</td>
            <td>'.$row['fecha'].'</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="8">No hay donaciones registradas según los filtros aplicados.</td></tr>';
}

$html .= '
    </tbody>
  </table>
  <br><p style="text-align:right;">Traperos de San Pablo © '.date("Y").'</p>
</body>
</html>';

// Generar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar PDF
$dompdf->stream("reporte_donaciones.pdf", ["Attachment" => true]);
?>