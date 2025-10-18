<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

// Procesar formulario
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni = trim($_POST['dni']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $tipo_ayuda = trim($_POST['tipo_ayuda']);
    $descripcion = trim($_POST['descripcion']);
    $empleado_id = $_SESSION['usuario_id'];

    if (empty($nombre) || empty($apellido) || empty($dni) || empty($tipo_ayuda)) {
        $mensaje = "<div class='alert alert-danger'>Por favor, completa todos los campos obligatorios.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO beneficiarios 
            (nombre, apellido, dni, telefono, direccion, tipo_ayuda, descripcion, empleado_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssi", $nombre, $apellido, $dni, $telefono, $direccion, $tipo_ayuda, $descripcion, $empleado_id);

        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success'>✅ Beneficiario registrado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>❌ Error al registrar beneficiario: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}

$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Beneficiario - Traperos de San Pablo</title>
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

    .sidebar .user-header {
      width: 100%;
      background: #c9461f;
      text-align: center;
      padding: 2rem 1rem;
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

    .sidebar hr {
      border: none;
      height: 3px;
      background-color: #c9461f;
      margin: 10px 0;
    }

    /* Responsive */
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

      <h1 class="mb-4"><i class="fa-solid fa-user-plus"></i> Registrar Beneficiario</h1>

      <?= $mensaje; ?>

      <form method="POST" class="bg-white p-4 rounded shadow-sm">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Apellido</label>
            <input type="text" name="apellido" class="form-control" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">DNI</label>
            <input type="text" name="dni" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Tipo de Ayuda</label>
          <select name="tipo_ayuda" class="form-select" required>
            <option value="">Seleccionar...</option>
            <option value="Mueble">Mueble</option>
            <option value="Ropa">Ropa</option>
            <option value="Artefacto">Artefacto</option>
            <option value="Electrónico">Electrónico</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalle de la ayuda o comentarios..."></textarea>
        </div>

        <div class="d-flex justify-content-between">
          <a href="reporte_beneficiarios.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Volver al reporte
          </a>
          <button type="submit" class="btn btn-success">
            <i class="fa-solid fa-check"></i> Registrar beneficiario
          </button>
        </div>
      </form>
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