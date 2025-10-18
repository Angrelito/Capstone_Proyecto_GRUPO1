<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donante_id = $_POST['donante_id'];
    $tipo_donacion = $_POST['tipo_donacion'];
    $cantidad = $_POST['cantidad'];
    $descripcion = $_POST['descripcion'];
    $empleado_id = $_SESSION['usuario_id'];

    $sql = "INSERT INTO donaciones (donante_id, empleado_id, tipo_donacion, cantidad, descripcion)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisis", $donante_id, $empleado_id, $tipo_donacion, $cantidad, $descripcion);

    if ($stmt->execute()) {
        $donacion_id = $conn->insert_id;
        // 🔹 Obtener el nombre del objeto donado desde la tabla donantes
        $objetoQuery = $conn->prepare("SELECT donacion FROM donantes WHERE id = ?");
        $objetoQuery->bind_param("i", $donante_id);
        $objetoQuery->execute();
        $objetoResult = $objetoQuery->get_result();
        $objeto = $objetoResult->fetch_assoc()['donacion'] ?? 'Desconocido';
        $objetoQuery->close();

        // 🔹 Verificar si ya existe en inventario
        $checkInv = $conn->prepare("SELECT id, cantidad_disponible FROM inventario WHERE objeto_donado = ? AND categoria = ?");
        $checkInv->bind_param("ss", $objeto, $tipo_donacion);
        $checkInv->execute();
        $resInv = $checkInv->get_result();

        if ($resInv->num_rows > 0) {
            // 🔸 Si ya existe, actualizamos la cantidad
            $inv = $resInv->fetch_assoc();
            $nuevaCantidad = $inv['cantidad_disponible'] + $cantidad;

            $updateInv = $conn->prepare("UPDATE inventario 
                SET cantidad_disponible = ?, ultima_actualizacion = NOW() 
                WHERE id = ?");
            $updateInv->bind_param("ii", $nuevaCantidad, $inv['id']);
            $updateInv->execute();
            $updateInv->close();
        } else {
            // 🔸 Si no existe, insertamos un nuevo registro en inventario
            // 🔸 Si no existe, insertamos un nuevo registro en inventario (con donacion_id)
            $insertInv = $conn->prepare("
                INSERT INTO inventario (donacion_id, objeto_donado, categoria, cantidad_disponible, ultima_actualizacion)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $insertInv->bind_param("issi", $donacion_id, $objeto, $tipo_donacion, $cantidad);
            $insertInv->execute();
            $insertInv->close();
        }

        $checkInv->close();

        // ✅ Redirigir con mensaje de éxito
        header("Location: reporte_donaciones.php?success=1");
        exit();
    } else {
        header("Location: reporte_donaciones.php?error=1");
        exit();
    }
}

$donantes = $conn->query("
  SELECT d.id, d.nombre, d.donacion 
  FROM donantes d
  INNER JOIN retiro r ON d.id = r.donante_id
  WHERE r.estado = 'Recibido'
  ORDER BY d.nombre ASC
");


$usuario_nombre = $_SESSION['usuario_nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Donación - Traperos de San Pablo</title>
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

    <!-- Contenido -->
    <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
      <button class="btn btn-outline-dark d-md-none mb-3" type="button" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i> Menú
      </button>
      <h1 class="mb-4"><i class="fa-solid fa-plus"></i> Registrar nueva donación</h1>

      <form method="POST" class="card shadow p-4 bg-white">
        <div class="mb-3">
          <label for="donante_id" class="form-label">Seleccionar Donante</label>
          <select name="donante_id" id="donante_id" class="form-select" required>
            <option value="" disabled selected>-- Selecciona un donante --</option>
            <?php while ($d = $donantes->fetch_assoc()): ?>
              <option value="<?= $d['id'] ?>" data-donacion="<?= htmlspecialchars($d['donacion']) ?>">
                <?= htmlspecialchars($d['nombre']) ?> — <?= htmlspecialchars($d['donacion']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="tipo_donacion" class="form-label">Categoría de donación</label>
            <select id="tipo_donacion" class="form-select" disabled>
            <option value="" disabled selected>-- Categoría --</option>
            <option value="Mueble">Mueble</option>
            <option value="Ropa">Ropa</option>
            <option value="Artefacto">Artefacto</option>
            <option value="Electrónico">Electrónico</option>
            </select>
            <input type="hidden" name="tipo_donacion" id="tipo_donacion_hidden">
        </div>

        <div class="mb-3">
          <label for="cantidad" class="form-label">Cantidad</label>
          <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" required>
        </div>

        <div class="mb-3">
          <label for="descripcion" class="form-label">Descripción</label>
          <textarea name="descripcion" id="descripcion" class="form-control" rows="3" placeholder="Detalles adicionales de la donación" required></textarea>
        </div>

        <div class="d-flex justify-content-between">
          <a href="reporte_donaciones.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Volver</a>
          <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Guardar Donación</button>
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

// Asignar categoría automáticamente según el objeto donado
document.getElementById('donante_id').addEventListener('change', function() {
  const selectedOption = this.options[this.selectedIndex];
  const donacion = selectedOption.getAttribute('data-donacion')?.toLowerCase();
  const categoriaSelect = document.getElementById('tipo_donacion');
  const hiddenInput = document.getElementById('tipo_donacion_hidden');

  let categoria = "";
  if (["cama", "silla", "mesa", "sofá", "colchón", "ropero", "alfombra"].includes(donacion)) {
    categoria = "Mueble";
  } else if (["pantalón", "camisa", "chompa", "abrigo", "falda"].includes(donacion)) {
    categoria = "Ropa";
  } else if (["televisor", "radio", "licuadora", "microondas"].includes(donacion)) {
    categoria = "Artefacto";
  } else if (["celular", "computadora", "tablet"].includes(donacion)) {
    categoria = "Electrónico";
  }

  // Mostrar la categoría en el select
  for (let i = 0; i < categoriaSelect.options.length; i++) {
    if (categoriaSelect.options[i].value === categoria) {
      categoriaSelect.selectedIndex = i;
      break;
    }
  }

  // Enviar el valor real al input hidden
  hiddenInput.value = categoria;
});

</script>
</body>
</html>