<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';
require 'dompdf/autoload.inc.php';


date_default_timezone_set('America/Lima');

use Dompdf\Dompdf;
use Dompdf\Options;

// 🔹 Configuración de Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// 🔹 Usuario que genera el reporte
$usuario = $_SESSION['usuario_nombre'] ?? 'Desconocido';

// 🔹 Capturar filtros desde GET (si existen)
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$valores = isset($_GET['valores']) ? json_decode($_GET['valores'], true) : [];

// 🔹 Consulta base del inventario
$sql = "
SELECT 
    id,
    objeto_donado,
    categoria,
    cantidad_disponible AS cantidad_total,
    ultima_actualizacion
FROM inventario
WHERE 1=1
";

// 🔹 Filtro de búsqueda libre
if ($busqueda !== '') {
    $busqueda = $conn->real_escape_string($busqueda);
    $sql .= " AND (objeto_donado LIKE '%$busqueda%' OR categoria LIKE '%$busqueda%')";
}

// 🔹 Filtros específicos (categoría u objeto)
if (!empty($valores) && $tipo) {
    $lista = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $valores);
    if ($tipo === 'categoria') {
        $sql .= " AND categoria IN (" . implode(',', $lista) . ")";
    } elseif ($tipo === 'objeto') {
        $sql .= " AND objeto_donado IN (" . implode(',', $lista) . ")";
    }
}

$sql .= " ORDER BY objeto_donado ASC";
$resultado = $conn->query($sql);

// 🔹 Guardar snapshot en historial_inventario y almacenar datos para el PDF
$registros = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $registros[] = $row;
    }

    // 🔸 Insertar todos los registros en historial_inventario
    $insert = $conn->prepare("
        INSERT INTO historial_inventario (objeto_donado, categoria, cantidad_total, generado_por)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($registros as $row) {
        $insert->bind_param("ssis", $row['objeto_donado'], $row['categoria'], $row['cantidad_total'], $usuario);
        $insert->execute();
    }
}

// 🔸 Generar HTML del reporte PDF
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Inventario - Traperos de San Pablo</title>
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
  <h2 style="text-align:center;">Reporte de Control de Inventario</h2>
  <p><strong>Generado por:</strong> '.htmlspecialchars($usuario).'</p>
  <p><strong>Fecha:</strong> '.date("d/m/Y H:i").'</p>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Objeto Donado</th>
        <th>Categoría</th>
        <th>Cantidad Disponible</th>
        <th>Última Actualización</th>
      </tr>
    </thead>
    <tbody>';

// 🔹 Agregar filas al reporte
if (!empty($registros)) {
    foreach ($registros as $row) {
        $html .= '<tr>
            <td>'.htmlspecialchars($row['id']).'</td>
            <td>'.htmlspecialchars($row['objeto_donado']).'</td>
            <td>'.htmlspecialchars($row['categoria']).'</td>
            <td>'.htmlspecialchars($row['cantidad_total']).'</td>
            <td>'.htmlspecialchars($row['ultima_actualizacion']).'</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5">No hay registros de inventario según los filtros aplicados.</td></tr>';
}

$html .= '
    </tbody>
  </table>
  <br>
  <p style="text-align:right;">Traperos de San Pablo © '.date("Y").'</p>
</body>
</html>';

// 🔹 Generar y enviar PDF al navegador
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar automáticamente
$dompdf->stream("reporte_inventario.pdf", ["Attachment" => true]);
?>