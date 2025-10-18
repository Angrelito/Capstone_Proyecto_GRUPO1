<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

// 🔹 Consulta principal
$sql = "
SELECT 
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
ORDER BY o.fecha_registro DESC
";
$resultado = $conn->query($sql);

// 🔹 Sectores
$sectores = $conn->query("
SELECT sector, COUNT(*) AS total
FROM organizaciones
GROUP BY sector
ORDER BY sector ASC
");

// 🔹 Tipos de ayuda
$tipos_ayuda = $conn->query("
SELECT tipo_ayuda, COUNT(*) AS total
FROM organizaciones
GROUP BY tipo_ayuda
ORDER BY tipo_ayuda ASC
");

// 🔹 Empleados
$empleados = $conn->query("
SELECT e.usuario, COUNT(*) AS total
FROM organizaciones o
INNER JOIN empleados e ON o.empleado_id = e.id
GROUP BY e.usuario
ORDER BY e.usuario ASC
");

$usuario_nombre = $_SESSION['usuario_nombre'] ?? "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Organizaciones Beneficiarias - Traperos de San Pablo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f6f9;
      height: 100vh;
      overflow: hidden;
    }
    .sidebar {
      height: 100vh;
      background: #ED582D;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 0;
    }
    .sidebar .user-header {
      background: #c9461f;
      text-align: center;
      padding: 2rem 1rem;
      width: 100%;
    }
    .sidebar .user-icon {
      font-size: 70px;
      margin-bottom: 10px;
    }
    .sidebar .username {
      font-size: 1.2rem;
      font-weight: bold;
    }
    .sidebar .nav-link {
      color: #fff;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
      border-radius: 6px;
      transition: background 0.3s ease;
    }
    .sidebar .nav-link:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    .sidebar .nav-link.active {
      background: rgba(255, 255, 255, 0.3);
      font-weight: bold;
    }
    .main-content {
      padding: 2rem;
      overflow-y: auto;
      height: 100vh;
    }
    .nav-seccion {
      background: rgba(255, 255, 255, 0.3);
      color: #fff;
      font-weight: bold;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin: 4px 8px;
    }
    .filtros {
      background: #fff;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      margin-bottom: 30px;
    }
    #limpiarFiltros {
      background: #fff;
      color: #ED582D;
      border: 1.8px solid #ED582D;
      border-radius: 25px;
      padding: 8px 20px;
      transition: all 0.3s ease;
      font-weight: 500;
    }
    #limpiarFiltros:hover {
      background: #ED582D;
      color: #fff;
      transform: scale(1.05);
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 sidebar collapse d-md-flex" id="sidebarMenu">
      <div class="user-header">
        <i class="fa-solid fa-user-circle user-icon"></i>
        <div class="username"><?= htmlspecialchars($usuario_nombre) ?></div>
      </div>
      <ul class="nav flex-column mt-3">
        <li class="nav-item"><a class="nav-link" href="bienvenido.php"><i class="fa-solid fa-house"></i> Inicio</a></li>
        <hr class="mx-3 my-2 border-light opacity-50">

        <div class="nav-seccion px-3 py-2"><i class="fa-solid fa-warehouse me-2"></i> DONACIONES</div>
        <li class="nav-item"><a class="nav-link" href="reporte_donantes.php"><i class="fa-solid fa-box"></i> Reporte de donantes</a></li>
        <li class="nav-item"><a class="nav-link" href="reporte_empleados.php"><i class="fa-solid fa-users"></i> Reporte de empleados</a></li>
        <li class="nav-item"><a class="nav-link" href="reporte_retiro.php"><i class="fa-solid fa-truck"></i> Reporte de retiro</a></li>

        <hr class="mx-3 my-2 border-light opacity-50">
        <div class="nav-seccion px-3 py-2"><i class="fa-solid fa-boxes-stacked me-2"></i> INVENTARIO</div>
        <li class="nav-item"><a class="nav-link" href="reporte_donaciones.php"><i class="fa-solid fa-gear"></i> Reporte de productos donados</a></li>
        <li class="nav-item"><a class="nav-link" href="reporte_control_inventario.php"><i class="fa-solid fa-clipboard-list"></i> Control de Inventario</a></li>
        <li class="nav-item"><a class="nav-link" href="historial_inventario.php"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Reportes</a></li>

        <hr class="mx-3 my-2 border-light opacity-50">
        <div class="nav-seccion px-3 py-2"><i class="fa-solid fa-hand-holding-heart me-2"></i> DISTRIBUCIÓN</div>
        <li class="nav-item"><a class="nav-link" href="reporte_beneficiarios.php"><i class="fa-solid fa-user"></i> Reporte de beneficiarios</a></li>
        <li class="nav-item"><a class="nav-link active" href="reporte_organizaciones.php"><i class="fa-solid fa-people-group"></i> Reporte de organizaciones</a></li>

        <hr class="mx-3 my-2 border-light opacity-50">
        <div class="nav-seccion px-3 py-2"><i class="fa-solid fa-right-from-bracket me-2"></i> SESIÓN</div>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Cerrar sesión</a></li>
      </ul>
    </nav>

    <!-- Contenido principal -->
    <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fa-solid fa-people-group"></i> Reporte de Organizaciones Beneficiarias</h1>
        <div>
          <a id="btnGenerarPDF" href="generar_reporte_organizaciones.php" class="btn btn-danger" target="_blank">
            <i class="fa-solid fa-file-pdf"></i> Generar PDF
          </a>
          <a href="registrar_organizacion.php" class="btn btn-success"><i class="fa-solid fa-people-group"></i> Registrar Organización</a>
        </div>
      </div>

      <!-- Filtros -->
      <div class="filtros">
        <div class="row g-3 align-items-center">
          <div class="col-md-6">
            <input type="text" id="busqueda" class="form-control" placeholder="Buscar por nombre, sector, tipo de ayuda o empleado">
          </div>
          <div class="col-md-4">
            <select id="tipoFiltro" class="form-select">
              <option value="">Seleccionar filtro...</option>
              <option value="sector">Sector</option>
              <option value="tipo">Tipo de Ayuda</option>
              <option value="empleado">Empleado</option>
            </select>
          </div>
          <div class="col-md-2 text-end">
            <button id="limpiarFiltros" class="btn"><i class="fa-solid fa-eraser"></i> Limpiar</button>
          </div>
        </div>

        <!-- Filtro Sector -->
        <div id="filtroSector" class="mt-3" style="display:none;">
          <label class="fw-bold mb-2">Filtrar por sector:</label><br>
          <?php while ($s = $sectores->fetch_assoc()): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input filtro-check" type="checkbox" value="<?= htmlspecialchars($s['sector']) ?>">
              <label class="form-check-label">
                <?= htmlspecialchars($s['sector']) ?>
                <span class="badge bg-secondary"><?= $s['total'] ?></span>
              </label>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- Filtro Tipo -->
        <div id="filtroTipo" class="mt-3" style="display:none;">
          <label class="fw-bold mb-2">Filtrar por tipo de ayuda:</label><br>
          <?php while ($t = $tipos_ayuda->fetch_assoc()): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input filtro-check" type="checkbox" value="<?= htmlspecialchars($t['tipo_ayuda']) ?>">
              <label class="form-check-label">
                <?= htmlspecialchars($t['tipo_ayuda']) ?> 
                <span class="badge bg-secondary"><?= $t['total'] ?></span>
              </label>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- Filtro Empleado -->
        <div id="filtroEmpleado" class="mt-3" style="display:none;">
          <label class="fw-bold mb-2">Filtrar por empleado:</label><br>
          <?php while ($e = $empleados->fetch_assoc()): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input filtro-check" type="checkbox" value="<?= htmlspecialchars($e['usuario']) ?>">
              <label class="form-check-label">
                <?= htmlspecialchars($e['usuario']) ?> 
                <span class="badge bg-secondary"><?= $e['total'] ?></span>
              </label>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <!-- Tabla -->
      <div class="table-responsive shadow-sm">
        <table class="table table-bordered table-striped table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Organización</th>
              <th>Sector</th>
              <th>Representante</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Tipo de Ayuda</th>
              <th>Empleado</th>
              <th>Fecha</th>
              <th>Descripción</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
              <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['nombre_organizacion']) ?></td>
                  <td><?= htmlspecialchars($row['sector']) ?></td>
                  <td><?= htmlspecialchars($row['representante_nombre'] . ' ' . $row['representante_apellido']) ?></td>
                  <td><?= htmlspecialchars($row['telefono']) ?></td>
                  <td><?= htmlspecialchars($row['direccion']) ?></td>
                  <td><?= htmlspecialchars($row['tipo_ayuda']) ?></td>
                  <td><?= htmlspecialchars($row['empleado']) ?></td>
                  <td><?= date("d/m/Y H:i", strtotime($row['fecha_registro'])) ?></td>
                  <td><?= htmlspecialchars($row['descripcion']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="10" class="text-center">No hay organizaciones registradas.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>

<script>
document.getElementById("tipoFiltro").addEventListener("change", function() {
  document.querySelectorAll("#filtroSector, #filtroTipo, #filtroEmpleado").forEach(div => div.style.display = "none");
  if (this.value === "sector") document.getElementById("filtroSector").style.display = "block";
  if (this.value === "tipo") document.getElementById("filtroTipo").style.display = "block";
  if (this.value === "empleado") document.getElementById("filtroEmpleado").style.display = "block";
});

function filtrarTabla() {
  const busqueda = document.getElementById("busqueda").value.toLowerCase();
  const tipoFiltro = document.getElementById("tipoFiltro").value;
  const checks = [...document.querySelectorAll(".filtro-check:checked")].map(c => c.value.toLowerCase());
  const filas = document.querySelectorAll("tbody tr");

  filas.forEach(fila => {
    const textoFila = fila.innerText.toLowerCase();
    const sector = fila.cells[2].innerText.toLowerCase();
    const tipoAyuda = fila.cells[6].innerText.toLowerCase();
    const empleado = fila.cells[7].innerText.toLowerCase();

    let visible = textoFila.includes(busqueda);

    if (checks.length) {
      if (tipoFiltro === "sector") visible = visible && checks.includes(sector);
      else if (tipoFiltro === "tipo") visible = visible && checks.includes(tipoAyuda);
      else if (tipoFiltro === "empleado") visible = visible && checks.includes(empleado);
    }

    fila.style.display = visible ? "" : "none";
  });
}

document.addEventListener("input", e => {
  if (["busqueda"].includes(e.target.id) || e.target.classList.contains("filtro-check")) filtrarTabla();
});
document.getElementById("tipoFiltro").addEventListener("change", filtrarTabla);
document.getElementById("limpiarFiltros").addEventListener("click", () => location.reload());

document.getElementById("btnGenerarPDF").addEventListener("click", function() {
  const busqueda = document.getElementById("busqueda").value;
  const tipo = document.getElementById("tipoFiltro").value;
  const valores = [];
  document.querySelectorAll(".filtro-check:checked").forEach(chk => valores.push(chk.value));
  this.href = `generar_reporte_organizaciones.php?busqueda=${encodeURIComponent(busqueda)}&tipo=${encodeURIComponent(tipo)}&valores=${encodeURIComponent(JSON.stringify(valores))}`;
});
</script>
</body>
</html>