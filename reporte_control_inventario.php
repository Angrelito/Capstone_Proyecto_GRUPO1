<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

// 🔹 Consulta SQL: obtener datos directamente desde inventario
$sql = "
SELECT 
    id,
    objeto_donado,
    categoria,
    cantidad_disponible AS cantidad_total,
    ultima_actualizacion
FROM inventario
ORDER BY objeto_donado ASC
";
$resultado = $conn->query($sql);

// 🔹 Categorías dinámicas para filtros
$categorias = $conn->query("
    SELECT categoria, COUNT(*) AS total 
    FROM inventario 
    GROUP BY categoria 
    ORDER BY categoria ASC
");

// 🔹 Objetos dinámicos para filtros
$objetos = $conn->query("
    SELECT objeto_donado, COUNT(*) AS total 
    FROM inventario 
    GROUP BY objeto_donado 
    ORDER BY objeto_donado ASC
");


$usuario_nombre = $_SESSION['usuario_nombre'] ?? "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Control de Inventario - Traperos de San Pablo</title>
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
    .sidebar .user-header .user-icon {
      font-size: 70px;
      margin-bottom: 10px;
    }
    .sidebar .user-header .username {
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
      transition: background 0.3s ease;
    }
    .nav-seccion:hover {
      background: rgba(255, 255, 255, 0.4);
      cursor: default;
    }
    .sidebar hr {
      border: none;
      height: 3px;
      background-color: #c9461f;
      margin: 10px 0;
    }




        /* ====== FILTROS ====== */
    .filtros {
      background: linear-gradient(180deg, #ffffff 0%, #f8f9fb 100%);
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
      margin-bottom: 40px;
      border: 1px solid #e4e6eb;
    }

    .search-input, #tipoFiltro {
      border-radius: 40px;
      border: 1.6px solid #d0d4da;
      padding: 10px 18px;
      height: 42px;
      background: #fff;
      transition: all 0.3s ease;
    }

    .search-input:focus, #tipoFiltro:focus {
      border-color: #ED582D;
      box-shadow: 0 0 8px rgba(237, 88, 45, 0.25);
      outline: none;
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

    .filter-section {
      display: none;
      margin-top: 20px;
      padding: 15px;
      border-radius: 12px;
      background: #fafafa;
      border: 1px dashed #ddd;
      animation: fadeIn 0.3s ease-in-out;
    }

    .form-check-input:checked {
      background-color: #ED582D;
      border-color: #ED582D;
    }

    table {
      margin-top: 25px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      border: 1px solid #e0e0e0;
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
        <li class="nav-item">
          <a class="nav-link" href="bienvenido.php"><i class="fa-solid fa-house"></i> Inicio</a>
        </li>
        <hr class="mx-3 my-2 border-light opacity-50">
        <div class="nav-seccion px-3 py-2">
          <i class="fa-solid fa-warehouse me-2"></i> DONACIONES
        </div>
        <li class="nav-item"><a class="nav-link" href="reporte_donantes.php"><i class="fa-solid fa-box"></i> Reporte de donantes</a></li>
        <li class="nav-item"><a class="nav-link" href="reporte_empleados.php"><i class="fa-solid fa-users"></i> Reporte de empleados</a></li>
        <li class="nav-item"><a class="nav-link" href="reporte_retiro.php"><i class="fa-solid fa-truck"></i> Reporte de retiro</a></li>
        <hr class="mx-3 my-2 border-light opacity-50">
        <div class="nav-seccion px-3 py-2">
          <i class="fa-solid fa-boxes-stacked me-2"></i> INVENTARIO
        </div>
        <li class="nav-item"><a class="nav-link" href="reporte_donaciones.php"><i class="fa-solid fa-gear"></i> Reporte de productos donados</a></li>
        <li class="nav-item"><a class="nav-link active" href="reporte_control_inventario.php"><i class="fa-solid fa-clipboard-list"></i> Control de Inventario</a></li>
        <li class="nav-item"><a class="nav-link" href="historial_inventario.php"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Reportes</a></li>
                    <!-- 🔹 Separador visual -->
        <hr class="mx-3 my-2 border-light opacity-50">

            <!-- 🔹 Encabezado de sección tipo menú -->
        <div class="nav-seccion px-3 py-2">
          <i class="fa-solid fa-hand-holding-heart me-2"></i> DISTRIBUCION
        </div>

        <li class="nav-item">
          <a class="nav-link" href="reporte_beneficiarios.php"><i class="fa-solid fa-user"></i> Reporte de beneficiarios</a>
        </li>
        
        <li class="nav-item"><a class="nav-link" href="reporte_organizaciones.php"><i class="fa-solid fa-people-group"></i> Reporte de organizaciones</a></li>

        
        <hr class="mx-3 my-2 border-light opacity-50">
        <div class="nav-seccion px-3 py-2"><i class="fa-solid fa-right-from-bracket me-2"></i> SESIÓN</div>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Cerrar sesión</a></li>
      </ul>
    </nav>

    <!-- Contenido principal -->
    <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
      <button class="btn btn-outline-dark d-md-none mb-3" type="button" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i> Menú
      </button>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fa-solid fa-clipboard-list"></i> Control de Inventario</h1>
        <a href="#" id="btnGenerarPDF" target="_blank" class="btn btn-danger">
          <i class="fa-solid fa-file-pdf"></i> Generar PDF
        </a>
      </div>



            <!-- 🔹 Filtros -->
      <div class="filtros">
        <div class="row g-3 align-items-center">
          <div class="col-md-6">
            <input type="text" id="busqueda" class="form-control search-input" placeholder="Buscar por objeto o categoría">
          </div>
          <div class="col-md-4">
            <select id="tipoFiltro" class="form-select">
              <option value="">Seleccionar tipo de filtro...</option>
              <option value="categoria">Categoría</option>
              <option value="objeto">Objeto Donado</option>
            </select>
          </div>
          <div class="col-md-2 text-end">
            <button class="btn btn-secondary" id="limpiarFiltros"><i class="fa-solid fa-eraser"></i> Limpiar</button>
          </div>
        </div>

        <!-- 🔹 Filtro por categoría -->
        <div id="filtroCategoria" class="filter-section">
          <hr>
          <label class="fw-bold mb-2">Filtrar por Categoría:</label><br>
          <?php while ($c = $categorias->fetch_assoc()): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input filtro-check" type="checkbox" value="<?= htmlspecialchars($c['categoria']) ?>">
              <label class="form-check-label">
                <?= htmlspecialchars($c['categoria']) ?>
                <span class="badge bg-secondary"><?= $c['total'] ?></span>
              </label>
            </div>
          <?php endwhile; ?>
        </div>
      </div>


            <!-- 🔹 Filtro por Objeto Donado -->
      <div id="filtroObjeto" class="filter-section">
        <hr>
        <label class="fw-bold mb-2">Filtrar por Objeto Donado:</label><br>
        <?php while ($o = $objetos->fetch_assoc()): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input filtro-check" type="checkbox" value="<?= htmlspecialchars($o['objeto_donado']) ?>">
            <label class="form-check-label">
              <?= htmlspecialchars($o['objeto_donado']) ?>
              <span class="badge bg-secondary"><?= $o['total'] ?></span>
            </label>
          </div>
        <?php endwhile; ?>
      </div>



      <div class="table-responsive shadow-sm">
        <table id="tablaInventario" class="table table-bordered table-striped table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Objeto Donado</th>
              <th>Categoría</th>
              <th>Cantidad Disponible</th>
              <th>Última Actualización</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            if ($resultado && $resultado->num_rows > 0): 
              $contador = 1;
              while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['objeto_donado']) ?></td>
                  <td><?= htmlspecialchars($row['categoria']) ?></td>
                  <td><?= htmlspecialchars($row['cantidad_total']) ?></td>
                  <td><?= htmlspecialchars($row['ultima_actualizacion']) ?></td>
                </tr>
              <?php endwhile; 
            else: ?>
              <tr><td colspan="5" class="text-center">No hay registros de inventario.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
  document.getElementById("sidebarMenu").classList.toggle("show");
}


// Mostrar/ocultar filtros
document.getElementById("tipoFiltro").addEventListener("change", function() {
  document.querySelectorAll(".filter-section").forEach(div => div.style.display = "none");
  if (this.value) {
    document.getElementById("filtro" + this.value.charAt(0).toUpperCase() + this.value.slice(1)).style.display = "block";
  }
  filtrarTabla();
});

// Escuchar eventos de búsqueda o selección
document.addEventListener("input", e => {
  if (["busqueda"].includes(e.target.id) || e.target.classList.contains("filtro-check")) {
    filtrarTabla();
  }
});

// Botón limpiar filtros
document.getElementById("limpiarFiltros").addEventListener("click", () => location.reload());

// Filtrado dinámico
function filtrarTabla() {
  const busqueda = document.getElementById("busqueda").value.toLowerCase();
  const checks = [...document.querySelectorAll(".filtro-check:checked")].map(c => c.value.toLowerCase());

  const tipoFiltro = document.getElementById("tipoFiltro").value;
  const filas = document.querySelectorAll("#tablaInventario tbody tr");

  filas.forEach(fila => {
    const textoFila = fila.innerText.toLowerCase();
    const categoria = fila.cells[2].innerText.toLowerCase();
    const objeto = fila.cells[1].innerText.toLowerCase();

    let visible = textoFila.includes(busqueda);

    if (checks.length) {
      if (tipoFiltro === "categoria") {
        visible = visible && checks.includes(categoria);
      } else if (tipoFiltro === "objeto") {
        visible = visible && checks.includes(objeto);
      }
    }

    fila.style.display = visible ? "" : "none";
  });
}



document.getElementById("btnGenerarPDF").addEventListener("click", function (e) {
  e.preventDefault();

  const busqueda = encodeURIComponent(document.getElementById("busqueda").value);
  const tipoFiltro = document.getElementById("tipoFiltro").value;
  const checks = [...document.querySelectorAll(".filtro-check:checked")].map(c => c.value);

  const url = `generar_reporte_inventario.php?busqueda=${busqueda}&tipo=${tipoFiltro}&valores=${encodeURIComponent(JSON.stringify(checks))}`;
  window.open(url, "_blank");
});

</script>
</body>
</html>