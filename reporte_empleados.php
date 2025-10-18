<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

// Consultar empleados
$sql = "SELECT id, usuario, email, creado_en FROM empleados ORDER BY creado_en DESC";
$resultado = $conn->query($sql);

// Nombre del usuario logueado
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Empleados - Traperos de San Pablo</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome -->
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



    /* Responsive: el sidebar ocupa toda la pantalla en móvil */
    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .main-content {
        padding: 1rem;
      }
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
        <div class="username"><?php echo htmlspecialchars($usuario_nombre); ?></div>
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
              <a class="nav-link active" href="reporte_empleados.php"><i class="fa-solid fa-users"></i> Reporte de empleados</a>
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

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="m-0"><i class="fa-solid fa-users"></i> Reporte de Empleados</h1>
        <a href="registrar_empleado.php" class="btn btn-success">
          <i class="fa-solid fa-user-plus"></i> Registrar empleado
        </a>
      </div>

      <div class="table-responsive shadow-sm">
        <table class="table table-bordered table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Usuario</th>
              <th>Email</th>
              <th>Fecha de creación</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($resultado->num_rows > 0): ?>
              <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $row['id']; ?></td>
                  <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo $row['creado_en']; ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center">No hay empleados registrados.</td></tr>
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
</script>
</body>
</html>