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

// --- Capturar filtros enviados desde JS ---
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$valores = isset($_GET['valores']) ? json_decode($_GET['valores'], true) : [];

// --- Construir la consulta SQL base ---
$sql = "SELECT id, nombre, correo, telefono, donacion, descripcion, fecha FROM donantes WHERE 1=1";

// Filtro de búsqueda general
if ($busqueda !== '') {
    $busqueda = $conn->real_escape_string($busqueda);
    $sql .= " AND (nombre LIKE '%$busqueda%' OR correo LIKE '%$busqueda%' OR donacion LIKE '%$busqueda%')";
}

// Filtros específicos (tipo / correo)
if (!empty($valores) && $tipo) {
    $lista = array_map(function($v) use ($conn) {
        return "'" . $conn->real_escape_string($v) . "'";
    }, $valores);

    if ($tipo === 'tipo') {
        $sql .= " AND donacion IN (" . implode(',', $lista) . ")";
    } elseif ($tipo === 'correo') {
        $sql .= " AND correo IN (" . implode(',', $lista) . ")";
    }
}

$sql .= " ORDER BY fecha DESC";
$resultado = $conn->query($sql);

// --- Generar HTML ---
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Donantes</title>
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
  <h2 style="text-align:center;">Reporte de Donantes</h2>
  <p><strong>Generado:</strong> ' . date("d/m/Y H:i") . '</p>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Teléfono</th>
        <th>Donación</th>
        <th>Descripción</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>';

// --- Agregar filas ---
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $html .= '<tr>
            <td>'.$row['id'].'</td>
            <td>'.htmlspecialchars($row['nombre']).'</td>
            <td>'.htmlspecialchars($row['correo']).'</td>
            <td>'.htmlspecialchars($row['telefono']).'</td>
            <td>'.htmlspecialchars($row['donacion']).'</td>
            <td>'.htmlspecialchars($row['descripcion']).'</td>
            <td>'.$row['fecha'].'</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="7">No hay donantes que coincidan con los filtros.</td></tr>';
}

$html .= '
    </tbody>
  </table>
  <br><p style="text-align:right;">Traperos de San Pablo © '.date("Y").'</p>
</body>
</html>';

// --- Generar PDF ---
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("reporte_donantes.pdf", ["Attachment" => true]);
?>