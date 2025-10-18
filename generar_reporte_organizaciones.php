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
            o.id,
            o.nombre_organizacion,
            o.sector,
            o.representante_nombre,
            o.representante_apellido,
            o.telefono,
            o.direccion,
            o.tipo_ayuda,
            o.descripcion,
            e.usuario AS empleado,
            o.fecha_registro
        FROM organizaciones o
        LEFT JOIN empleados e ON o.empleado_id = e.id
        WHERE 1=1";

// 🔹 Aplicar búsqueda
if ($busqueda !== '') {
    $busqueda = $conn->real_escape_string($busqueda);
    $sql .= " AND (
        o.nombre_organizacion LIKE '%$busqueda%' OR
        o.sector LIKE '%$busqueda%' OR
        o.representante_nombre LIKE '%$busqueda%' OR
        o.representante_apellido LIKE '%$busqueda%' OR
        o.tipo_ayuda LIKE '%$busqueda%' OR
        e.usuario LIKE '%$busqueda%'
    )";
}

// 🔹 Aplicar filtros múltiples
if (!empty($valores) && $tipo) {
    $lista = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $valores);
    if ($tipo === 'tipo') {
        $sql .= " AND o.tipo_ayuda IN (" . implode(',', $lista) . ")";
    } elseif ($tipo === 'empleado') {
        $sql .= " AND e.usuario IN (" . implode(',', $lista) . ")";
    } elseif ($tipo === 'sector') {
        $sql .= " AND o.sector IN (" . implode(',', $lista) . ")";
    }
}

$sql .= " ORDER BY o.fecha_registro DESC";
$resultado = $conn->query($sql);

// 🔹 Generar HTML del reporte
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Organizaciones Beneficiarias</title>
<style>
  body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
  h1 { text-align: center; color: #ED582D; margin-bottom: 0; }
  h2 { text-align: center; color: #333; margin-top: 5px; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
  th { background-color: #ED582D; color: #fff; }
  img { display: block; margin: 0 auto 10px; width: 100px; }
</style>
</head>
<body>
  <img src="http://localhost/ONG/img/logoprincipal.png" alt="Logo">
  <h1>Traperos de San Pablo</h1>
  <h2>Reporte de Organizaciones Beneficiarias</h2>
  <p><strong>Generado:</strong> ' . date("d/m/Y H:i") . '</p>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Organización</th>
        <th>Sector</th>
        <th>Representante</th>
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
        $representante = trim($row['representante_nombre'] . ' ' . $row['representante_apellido']);
        if ($representante === '') $representante = '—';
        
        $html .= '<tr>
            <td>' . $row['id'] . '</td>
            <td>' . htmlspecialchars($row['nombre_organizacion']) . '</td>
            <td>' . htmlspecialchars($row['sector']) . '</td>
            <td>' . htmlspecialchars($representante) . '</td>
            <td>' . htmlspecialchars($row['telefono']) . '</td>
            <td>' . htmlspecialchars($row['direccion']) . '</td>
            <td>' . htmlspecialchars($row['tipo_ayuda']) . '</td>
            <td>' . htmlspecialchars($row['empleado']) . '</td>
            <td>' . date("d/m/Y H:i", strtotime($row['fecha_registro'])) . '</td>
            <td>' . htmlspecialchars($row['descripcion']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="10">No hay organizaciones registradas según los filtros aplicados.</td></tr>';
}

$html .= '
    </tbody>
  </table>
  <br><p style="text-align:right;">Traperos de San Pablo © ' . date("Y") . '</p>
</body>
</html>';

// 🔹 Generar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // horizontal
$dompdf->render();

// 🔹 Descargar PDF
$dompdf->stream("reporte_organizaciones.pdf", ["Attachment" => true]);
?>