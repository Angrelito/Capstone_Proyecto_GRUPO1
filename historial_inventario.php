<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

// Consulta base
$sql = "
SELECT 
    id,
    objeto_donado,
    categoria,
    cantidad_total,
    generado_por,
    fecha_reporte
FROM historial_inventario
ORDER BY fecha_reporte DESC
";
$resultado = $conn->query($sql);

// Datos dinámicos para filtros
$categorias = $conn->query("
    SELECT categoria, COUNT(*) AS total 
    FROM historial_inventario 
    GROUP BY categoria 
    ORDER BY categoria ASC
");

$usuarios = $conn->query("
    SELECT generado_por, COUNT(*) AS total 
    FROM historial_inventario 
    GROUP BY generado_por 
    ORDER BY generado_por ASC
");

$usuario_nombre = $_SESSION['usuario_nombre'] ?? "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Reportes de Inventario - Traperos de San Pablo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f6f9;
      height: 100vh;
      overflow: hidden;
    }

    /* Barra lateral */
    .sidebar {
      height: 100vh;
      background: #ED582D;
      color: #fff;
      padding: 0;
      display: flex;
      flex-direction: column;
    }

    /* Cabecera de usuario */
    .sidebar .user-header {
      width: 100%;
      background: #c9461f; /* un tono más oscuro */
      text-align: center;
      padding: 2rem 1rem;
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

    /* Contenido */
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

  /* ======== ESTILOS MEJORADOS PARA FILTROS Y BÚSQUEDA ======== */
.filtros {
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fb 100%);
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  margin-bottom: 50px;
  border: 1px solid #e4e6eb;
  transition: all 0.3s ease;
}

.filtros:hover {
  box-shadow: none;
}



/* Campos de búsqueda y select */
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

.search-input::placeholder {
  color: #aaa;
  font-style: italic;
}

/* Botón limpiar */
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

/* Secciones desplegables de filtros */
.filter-section {
  display: none;
  margin-top: 20px;
  padding: 15px;
  border-radius: 12px;
  background: #fafafa;
  border: 1px dashed #ddd;
  animation: fadeIn 0.3s ease-in-out;
}

.filter-section label.fw-bold {
  color: #141414ff;
}

.form-check-label {
  color: #333;
}

.form-check-input:checked {
  background-color: #ED582D;
  border-color: #ED582D;
}

/* Estilo del rango de fechas */
.date-range {
  display: flex;
  gap: 15px;
  justify-content: flex-start;
  flex-wrap: wrap;
  margin-top: 10px;
}

.date-range input[type="date"] {
  border-radius: 25px;
  border: 1.5px solid #ccc;
  padding: 8px 15px;
  transition: all 0.3s ease;
  background-color: #fff;
}

.date-range input[type="date"]:focus {
  border-color: #ED582D;
  box-shadow: 0 0 6px rgba(237, 88, 45, 0.3);
}

  .table th {
    white-space: nowrap;
  }

  table {
  margin-top: 25px; /* 👈 separación visible sin usar .table-responsive */
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
      <button class="btn btn-outline-dark d-md-none mb-3" type="button" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i> Menú
      </button>

      <h1 class="mb-3"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Reportes</h1>

      <!-- 🔹 Filtros y búsqueda -->
      <div class="filtros">
        <div class="row g-3 align-items-center">
          <div class="col-md-4">
            <input type="text" id="busqueda" class="form-control search-input" placeholder="Buscar por objeto, categoría o usuario">
          </div>
          <div class="col-md-4">
            <select id="tipoFiltro" class="form-select">
              <option value="">Seleccionar tipo de filtro...</option>
              <option value="categoria">Categoría</option>
              <option value="usuario">Usuario</option>
              <option value="fecha">Fecha</option>
            </select>
          </div>
          <div class="col-md-4 text-end">
            <button class="btn btn-secondary" id="limpiarFiltros"><i class="fa-solid fa-eraser"></i> Limpiar</button>
          </div>
        </div>

        <!-- Secciones dinámicas -->
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

        <div id="filtroUsuario" class="filter-section">
          <hr>
          <label class="fw-bold mb-2">Filtrar por Usuario:</label><br>
            <?php while ($u = $usuarios->fetch_assoc()): ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input filtro-check" type="checkbox" value="<?= htmlspecialchars($u['generado_por']) ?>">
                <label class="form-check-label">
                <?= htmlspecialchars($u['generado_por']) ?> 
                <span class="badge bg-secondary"><?= $u['total'] ?></span>
                </label>
            </div>
            <?php endwhile; ?>
        </div>

        <div id="filtroFecha" class="filter-section">
        <hr>
        <label class="fw-bold mb-2">Filtrar por Fecha:</label>
        <div class="date-range">
            <div>
            <label class="form-label mb-0 small text-muted">Desde:</label>
            <input type="date" id="fechaDesde" class="form-control">
            </div>
            <div>
            <label class="form-label mb-0 small text-muted">Hasta:</label>
            <input type="date" id="fechaHasta" class="form-control">
            </div>
        </div>
      </div>

      <!-- 🔹 Tabla -->
      <div class="table-responsive shadow-sm">
        <table id="tablaHistorial" class="table table-bordered table-striped table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Objeto Donado</th>
              <th>Categoría</th>
              <th>Cantidad Total</th>
              <th>Generado Por</th>
              <th>Fecha del Reporte</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
              <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['objeto_donado']) ?></td>
                  <td><?= htmlspecialchars($row['categoria']) ?></td>
                  <td><?= htmlspecialchars($row['cantidad_total']) ?></td>
                  <td><?= htmlspecialchars($row['generado_por']) ?></td>
                  <td><?= date("d/m/Y H:i", strtotime($row['fecha_reporte'])) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center">No hay reportes registrados.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebarMenu").classList.toggle("show");
}

// 🔹 Mostrar solo el filtro seleccionado
document.getElementById("tipoFiltro").addEventListener("change", function() {
  document.querySelectorAll(".filter-section").forEach(div => div.style.display = "none");
  if (this.value) {
    const filtroSeleccionado = document.getElementById("filtro" + this.value.charAt(0).toUpperCase() + this.value.slice(1));
    filtroSeleccionado.style.display = "block";
  }
  filtrarTabla(); // Actualiza resultados al cambiar tipo
});

// 🔹 Escuchar todos los inputs
document.addEventListener("input", e => {
  if (["busqueda", "fechaDesde", "fechaHasta"].includes(e.target.id) || e.target.classList.contains("filtro-check")) {
    filtrarTabla();
  }
});

// 🔹 Botón limpiar filtros
document.getElementById("limpiarFiltros").addEventListener("click", () => location.reload());

// 🔹 Función principal de filtrado
function filtrarTabla() {
  const busqueda = document.getElementById("busqueda").value.toLowerCase();
  const tipoFiltro = document.getElementById("tipoFiltro").value;
  const checks = [...document.querySelectorAll(".filtro-check:checked")].map(c => c.value.toLowerCase());
  const fechaDesde = document.getElementById("fechaDesde")?.value || "";
  const fechaHasta = document.getElementById("fechaHasta")?.value || "";

  document.querySelectorAll("#tablaHistorial tbody tr").forEach(fila => {
    const textoFila = fila.innerText.toLowerCase();
    const categoria = fila.cells[2].innerText.toLowerCase();
    const usuario = fila.cells[4].innerText.toLowerCase();
    const fechaFila = fila.cells[5].innerText.split(' ')[0].split('/').reverse().join('-');

    let visible = textoFila.includes(busqueda);

    if (tipoFiltro === "categoria" && checks.length) visible = visible && checks.includes(categoria);
    if (tipoFiltro === "usuario" && checks.length) visible = visible && checks.includes(usuario);

    // 🔹 Nuevo: rango de fechas (desde - hasta)
    if (tipoFiltro === "fecha") {
      if (fechaDesde && fechaFila < fechaDesde) visible = false;
      if (fechaHasta && fechaFila > fechaHasta) visible = false;
    }

    fila.style.display = visible ? "" : "none";
  });
}
</script>
</body>
</html>