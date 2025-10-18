<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

// Consulta uniendo donaciones, donantes y empleados
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
        ORDER BY d.fecha DESC";
$resultado = $conn->query($sql);



// 🔹 Obtener categorías únicas (tipo_donacion)
$categorias = $conn->query("
  SELECT tipo_donacion AS categoria, COUNT(*) AS total
  FROM donaciones
  GROUP BY tipo_donacion
  ORDER BY tipo_donacion ASC
");

// 🔹 Obtener objetos donados únicos
$objetos = $conn->query("
  SELECT dn.donacion AS objeto_donado, COUNT(*) AS total
  FROM donaciones d
  INNER JOIN donantes dn ON d.donante_id = dn.id
  GROUP BY dn.donacion
  ORDER BY dn.donacion ASC
");

// 🔹 Donantes
$donantes = $conn->query("
  SELECT dn.nombre AS donante, COUNT(*) AS total
  FROM donaciones d
  INNER JOIN donantes dn ON d.donante_id = dn.id
  GROUP BY dn.nombre
  ORDER BY dn.nombre ASC
");

// 🔹 Empleados
$empleados = $conn->query("
  SELECT e.usuario AS empleado, COUNT(*) AS total
  FROM donaciones d
  INNER JOIN empleados e ON d.empleado_id = e.id
  GROUP BY e.usuario
  ORDER BY e.usuario ASC
");


// Nombre del usuario logueado
$usuario_nombre = $_SESSION['usuario_nombre'] ?? "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Donaciones - Traperos de San Pablo</title>
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

    .table .btn i {
    font-size: 1rem;
    }
    .table .btn {
    transition: transform 0.2s ease, background 0.2s ease;
    }
    .table .btn:hover {
    transform: scale(1.1);
    }




    .filtros {
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fb 100%);
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  border: 1px solid #e4e6eb;
}

.filter-section {
  background: #fafafa;
  border: 1px dashed #ddd;
  border-radius: 12px;
  animation: fadeIn 0.3s ease-in-out;
}

.search-input, #tipoFiltro {
  border-radius: 40px;
  border: 1.6px solid #d0d4da;
  padding: 10px 18px;
  height: 42px;
  background: #fff;
  transition: all 0.3s ease;
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
            <li class="nav-item">
              <a class="nav-link" href="bienvenido.php"><i class="fa-solid fa-house"></i> Inicio</a>
            </li>

            <!-- 🔹 Separador visual -->
            <hr class="mx-3 my-2 border-light opacity-50">

            <!-- 🔹 Encabezado de sección tipo menú -->
            <div class="nav-seccion px-3 py-2">
              <i class="fa-solid fa-warehouse me-2"></i> DONACIONES
            </div>

            <li class="nav-item">
              <a class="nav-link" href="reporte_donantes.php"><i class="fa-solid fa-box"></i> Reporte de donantes</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="reporte_empleados.php"><i class="fa-solid fa-users"></i> Reporte de empleados</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="reporte_retiro.php"><i class="fa-solid fa-truck"></i> Reporte de retiro</a>
            </li>

            <!-- 🔹 Separador visual -->
            <hr class="mx-3 my-2 border-light opacity-50">

            <!-- 🔹 Encabezado de sección tipo menú -->
            <div class="nav-seccion px-3 py-2">
              <i class="fa-solid fa-boxes-stacked me-2"></i> INVENTARIO
            </div>

            <li class="nav-item">
              <a class="nav-link active" href="reporte_donaciones.php"><i class="fa-solid fa-gear"></i> Reporte de productos donados</a>
            </li>

        <li class="nav-item"><a class="nav-link" href="reporte_control_inventario.php"><i class="fa-solid fa-clipboard-list"></i> Control de Inventario</a></li>
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
        <h1 class="m-0"><i class="fa-solid fa-gear"></i> Reporte de Donaciones</h1>
        <div>
            <!-- 🔹 Botón para generar PDF -->
            <a href="#" id="btnGenerarPDF" target="_blank" class="btn btn-danger me-2">
              <i class="fa-solid fa-file-pdf"></i> Generar PDF
            </a>
            <!-- 🔹 Botón para registrar donación -->
            <a href="registrar_donacion.php" class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Registrar donación
            </a>
        </div>
        </div>


      

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Donación registrada correctamente.</div>
      <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger">❌ Error al registrar la donación.</div>
      <?php endif; ?>

<!-- 🔹 Filtros -->
<div class="filtros mb-4 p-4 bg-white shadow-sm rounded">
  <div class="row g-3 align-items-center">
    <div class="col-md-6">
      <input type="text" id="busqueda" class="form-control search-input" placeholder="Buscar por objeto, categoría o donante...">
    </div>
    <div class="col-md-4">
      <select id="tipoFiltro" class="form-select">
        <option value="">Seleccionar tipo de filtro...</option>
        <option value="categoria">Categoría</option>
        <option value="objeto">Objeto Donado</option>
        <option value="donante">Donante</option>
        <option value="empleado">Empleado</option>
      </select>
    </div>
    <div class="col-md-2 text-end">
      <button class="btn btn-secondary" id="limpiarFiltros"><i class="fa-solid fa-eraser"></i> Limpiar</button>
    </div>
  </div>

  <!-- 🔹 Filtro por Categoría -->
  <div id="filtroCategoria" class="filter-section mt-3 p-3 border rounded" style="display:none;">
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

  <!-- 🔹 Filtro por Objeto Donado -->
  <div id="filtroObjeto" class="filter-section mt-3 p-3 border rounded" style="display:none;">
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



    <!-- 🔹 Filtro por Donante -->
  <div id="filtroDonante" class="filter-section mt-3 p-3 border rounded" style="display:none;">
    <label class="fw-bold mb-2">Filtrar por Donante:</label><br>
    <?php while ($d = $donantes->fetch_assoc()): ?>
      <div class="form-check form-check-inline">
        <input class="form-check-input filtro-check" type="checkbox" value="<?= htmlspecialchars($d['donante']) ?>">
        <label class="form-check-label">
          <?= htmlspecialchars($d['donante']) ?>
          <span class="badge bg-secondary"><?= $d['total'] ?></span>
        </label>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- 🔹 Filtro por Empleado -->
  <div id="filtroEmpleado" class="filter-section mt-3 p-3 border rounded" style="display:none;">
    <label class="fw-bold mb-2">Filtrar por Empleado:</label><br>
    <?php while ($e = $empleados->fetch_assoc()): ?>
      <div class="form-check form-check-inline">
        <input class="form-check-input filtro-check" type="checkbox" value="<?= htmlspecialchars($e['empleado']) ?>">
        <label class="form-check-label">
          <?= htmlspecialchars($e['empleado']) ?>
          <span class="badge bg-secondary"><?= $e['total'] ?></span>
        </label>
      </div>
    <?php endwhile; ?>
  </div>
</div>


<div class="table-responsive shadow-sm">
  <table class="table table-bordered table-striped table-hover align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Donante</th>
        <th>Objeto Donado</th>
        <th>Categoría</th>
        <th>Cantidad</th>
        <th>Empleado</th>
        <th>Descripción</th>
        <th>Fecha</th>
        <th class="text-center">Acciones</th> <!-- 🔹 NUEVA COLUMNA -->
      </tr>
    </thead>
    <tbody>
      <?php if ($resultado && $resultado->num_rows > 0): ?>
        <?php while ($row = $resultado->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nombre_donante']) ?></td>
            <td><?= htmlspecialchars($row['objeto_donado']) ?></td>
            <td><?= htmlspecialchars($row['tipo_donacion']) ?></td>
            <td><?= $row['cantidad'] ?></td>
            <td><?= htmlspecialchars($row['usuario_empleado']) ?></td>
            <td><?= htmlspecialchars($row['descripcion']) ?></td>
            <td><?= $row['fecha'] ?></td>
            <td class="text-center">
              <!-- 🔹 Botón Editar -->
              <a href="editar_donacion.php?id=<?= $row['id'] ?>" 
                 class="btn btn-sm btn-primary me-1" title="Editar">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>

                <!-- 🔹 Botón Eliminar con modal -->
                <button type="button" 
                        class="btn btn-sm btn-danger" 
                        title="Eliminar"
                        data-bs-toggle="modal" 
                        data-bs-target="#confirmarEliminarModal"
                        data-id="<?= $row['id'] ?>"
                        data-donante="<?= htmlspecialchars($row['nombre_donante']) ?>"
                        data-objeto="<?= htmlspecialchars($row['objeto_donado']) ?>">
                <i class="fa-solid fa-trash"></i>
                </button>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="9" class="text-center">No hay donaciones registradas.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
<!-- 🔹 Modal Confirmación de Eliminación -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-labelledby="confirmarEliminarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmarEliminarLabel">
          <i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p>¿Deseas eliminar esta donación?</p>
        <p class="fw-bold text-danger" id="donacionInfo"></p>
        <small class="text-muted">Esta acción no se puede deshacer.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" id="btnEliminarConfirmado" class="btn btn-danger">
          <i class="fa-solid fa-trash"></i> Eliminar
        </a>
      </div>
    </div>
  </div>
</div>

<!-- 🔹 Modal de Mensaje -->
<div class="modal fade" id="mensajeModal" tabindex="-1" aria-labelledby="mensajeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="mensajeModalLabel">
          <i class="fa-solid fa-circle-check"></i> Operación completada
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="mensajeModalBody">Donación eliminada correctamente.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
  document.getElementById("sidebarMenu").classList.toggle("show");
}

let idAEliminar = null;

// Cuando se abre el modal de confirmación
const modalEliminar = document.getElementById('confirmarEliminarModal');
modalEliminar.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget;
  idAEliminar = button.getAttribute('data-id');
  const nombre = button.getAttribute('data-donante');
  const objeto = button.getAttribute('data-objeto');

  document.getElementById('donacionInfo').innerText = `${nombre} — ${objeto}`;
});

// Cuando se confirma la eliminación
document.getElementById('btnEliminarConfirmado').addEventListener('click', function() {
  if (!idAEliminar) return;

  fetch(`eliminar_donacion.php?id=${idAEliminar}`)
    .then(response => response.text())
    .then(data => {
      const modalInstance = bootstrap.Modal.getInstance(modalEliminar);
      modalInstance.hide();

      const mensajeModal = new bootstrap.Modal(document.getElementById('mensajeModal'));
      document.getElementById('mensajeModalBody').innerText = "✅ Donación eliminada correctamente.";
      mensajeModal.show();

      setTimeout(() => { window.location.reload(); }, 1500);
    })
    .catch(err => {
      const mensajeModal = new bootstrap.Modal(document.getElementById('mensajeModal'));
      document.getElementById('mensajeModalLabel').innerText = "❌ Error";
      document.getElementById('mensajeModalBody').innerText = "Ocurrió un error al intentar eliminar la donación.";
      const header = document.querySelector('#mensajeModal .modal-header');
      header.classList.remove('bg-success');
      header.classList.add('bg-danger');
      mensajeModal.show();
    });
});


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
  const filas = document.querySelectorAll("table tbody tr");

  filas.forEach(fila => {
    const textoFila = fila.innerText.toLowerCase();
    const categoria = fila.cells[3]?.innerText.toLowerCase() || "";
    const objeto = fila.cells[2]?.innerText.toLowerCase() || "";
    const donante = fila.cells[1]?.innerText.toLowerCase() || "";
    const empleado = fila.cells[5]?.innerText.toLowerCase() || "";

    let visible = textoFila.includes(busqueda);

    if (checks.length) {
      if (tipoFiltro === "categoria") visible = visible && checks.includes(categoria);
      else if (tipoFiltro === "objeto") visible = visible && checks.includes(objeto);
      else if (tipoFiltro === "donante") visible = visible && checks.includes(donante);
      else if (tipoFiltro === "empleado") visible = visible && checks.includes(empleado);
    }

    fila.style.display = visible ? "" : "none";
  });
}



document.getElementById("btnGenerarPDF").addEventListener("click", function (e) {
  e.preventDefault();
  
  const busqueda = encodeURIComponent(document.getElementById("busqueda").value);
  const tipoFiltro = document.getElementById("tipoFiltro").value;
  const checks = [...document.querySelectorAll(".filtro-check:checked")].map(c => c.value);

  const url = `generar_reporte_donaciones.php?busqueda=${busqueda}&tipo=${tipoFiltro}&valores=${encodeURIComponent(JSON.stringify(checks))}`;
  window.open(url, "_blank");
});

</script>
</body>
</html>