<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

// ID del empleado logueado
$empleado_id = $_SESSION['usuario_id'];

// 🔸 Procesar formulario de actualización
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['donante_id'], $_POST['estado'])) {
    $donante_id = intval($_POST['donante_id']);
    $estado = $_POST['estado'];

    // Comprobar si ya existe un registro de retiro para este donante
    $check_sql = "SELECT id FROM retiro WHERE donante_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $donante_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Actualizar registro existente
        $update_sql = "UPDATE retiro 
                       SET estado = ?, empleado_id = ?, fecha_actualizacion = NOW() 
                       WHERE donante_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", $estado, $empleado_id, $donante_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Insertar nuevo registro
        $insert_sql = "INSERT INTO retiro (donante_id, estado, empleado_id) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isi", $donante_id, $estado, $empleado_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $stmt->close();
}

// 🔸 Consultar donantes + retiro + empleado
$sql = "
SELECT d.id, d.nombre, d.correo, d.telefono, d.donacion, d.descripcion, d.fecha AS fecha_donacion,
       COALESCE(r.estado, 'En proceso') AS estado,
       r.fecha_actualizacion,
       e.usuario AS empleado
FROM donantes d
LEFT JOIN retiro r ON d.id = r.donante_id
LEFT JOIN empleados e ON r.empleado_id = e.id
ORDER BY d.fecha DESC
";
$result = $conn->query($sql);

// Nombre del usuario logueado
$usuario_nombre = $_SESSION['usuario_nombre'] ?? "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Estado del Retiro - Traperos de San Pablo</title>
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
    .badge {
      font-size: 0.9em;
    }
    .fecha-actualizacion {
      font-size: 0.85em;
      color: #6c757d;
    }
    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        width: 100%;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }
      .sidebar.show { transform: translateX(0); }
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
              <a class="nav-link active" href="reporte_retiro.php"><i class="fa-solid fa-truck"></i> Reporte de retiro</a>
            </li>

            <!-- 🔹 Separador visual -->
            <hr class="mx-3 my-2 border-light opacity-50">

            <!-- 🔹 Encabezado de sección tipo menú -->
            <div class="nav-seccion px-3 py-2">
              <i class="fa-solid fa-boxes-stacked me-2"></i> INVENTARIO
            </div>

            <li class="nav-item">
              <a class="nav-link" href="reporte_donaciones.php"><i class="fa-solid fa-gear"></i> Reporte de productos donados</a>
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

      <h2 class="mb-4"><i class="fa-solid fa-truck"></i> Reporte de Estado del Retiro</h2>

      <div class="card shadow">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Teléfono</th>
                  <th>Donación</th>
                  <th>Descripción</th>
                  <th>Fecha de Registro</th>
                  <th>Estado</th>
                  <th>Última actualización</th>
                  <th>Actualizar</th>
                </tr>
              </thead>
              <tbody>
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                  $estado = $row['estado'];
                  $badge_class = match($estado) {
                    'Recibido' => 'bg-success',
                    'Cancelado' => 'bg-danger',
                    default => 'bg-warning text-dark'
                  };

                  $fecha_actualizacion = $row['fecha_actualizacion']
                      ? date("d/m/Y H:i", strtotime($row['fecha_actualizacion']))
                      : null;
                  $empleado = $row['empleado'] ?? null;
                ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= htmlspecialchars($row['correo']) ?></td>
                    <td><?= htmlspecialchars($row['telefono']) ?></td>
                    <td><?= htmlspecialchars($row['donacion']) ?></td>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($row['fecha_donacion'])) ?></td>
                    <td><span class="badge <?= $badge_class ?>"><?= htmlspecialchars($estado) ?></span></td>
                    <td>
                      <?php if ($empleado && $fecha_actualizacion): ?>
                        <div class="fecha-actualizacion">
                          <strong><?= htmlspecialchars($empleado) ?></strong><br>
                          <small><?= $fecha_actualizacion ?></small>
                        </div>
                      <?php else: ?>
                        <em class="text-muted">Sin actualizar</em>
                      <?php endif; ?>
                    </td>
                    <td>
                      <form method="POST" class="d-flex">
                        <input type="hidden" name="donante_id" value="<?= $row['id'] ?>">
                        <select name="estado" class="form-select form-select-sm me-2">
                          <option <?= $estado == 'En proceso' ? 'selected' : '' ?>>En proceso</option>
                          <option <?= $estado == 'Recibido' ? 'selected' : '' ?>>Recibido</option>
                          <option <?= $estado == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                          <i class="fa-solid fa-save"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="10" class="text-center text-muted">No hay registros de retiro</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
  document.getElementById("sidebarMenu").classList.toggle("show");
}
</script>
</body>
</html>