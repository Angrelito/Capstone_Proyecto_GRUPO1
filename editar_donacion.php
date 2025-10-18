<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

require 'conexion.php';

// ✅ Obtener el ID de la donación
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: reporte_donaciones.php?error=sin_id");
    exit();
}

// ✅ Obtener datos actuales de la donación
$sql = "SELECT * FROM donaciones WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$donacion = $result->fetch_assoc();

if (!$donacion) {
    header("Location: reporte_donaciones.php?error=no_encontrado");
    exit();
}

// ✅ Obtener lista de donantes y empleados
$donantes = $conn->query("SELECT id, nombre, donacion FROM donantes ORDER BY nombre");
$empleados = $conn->query("SELECT id, usuario FROM empleados ORDER BY usuario");

// ✅ Nombre del usuario logueado
$usuario_nombre = $_SESSION['usuario_nombre'] ?? "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Donación - Traperos de San Pablo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f6f9;
    }
    .card {
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="p-4">

  <div class="container">
    <a href="reporte_donaciones.php" class="btn btn-secondary mb-3">
      <i class="fa-solid fa-arrow-left"></i> Volver al reporte
    </a>

    <div class="card p-4">
      <h2 class="mb-4"><i class="fa-solid fa-pen-to-square"></i> Editar Donación</h2>

      <form action="procesar_editar_donacion.php" method="POST">
        <input type="hidden" name="id" value="<?= $donacion['id'] ?>">

        <div class="mb-3">
          <label class="form-label">Donante</label>
          <select name="donante_id" class="form-select" required>
            <option value="">Seleccione un donante</option>
            <?php while ($d = $donantes->fetch_assoc()): ?>
              <option value="<?= $d['id'] ?>" 
                data-donacion="<?= htmlspecialchars($d['donacion']) ?>"
                <?= $d['id'] == $donacion['donante_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['nombre']) ?> - <?= htmlspecialchars($d['donacion']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Categoría de donación</label>
          <input type="text" name="tipo_donacion" id="tipo_donacion" 
                 class="form-control" value="<?= htmlspecialchars($donacion['tipo_donacion']) ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Cantidad</label>
          <input type="number" name="cantidad" class="form-control" min="1" 
                 value="<?= htmlspecialchars($donacion['cantidad']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Empleado que registró</label>
          <select name="empleado_id" class="form-select" required>
            <?php while ($e = $empleados->fetch_assoc()): ?>
              <option value="<?= $e['id'] ?>" 
                <?= $e['id'] == $donacion['empleado_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['usuario']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" required><?= htmlspecialchars($donacion['descripcion']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
        </button>
      </form>
    </div>
  </div>

<script>
document.querySelector("select[name='donante_id']").addEventListener("change", function() {
  const selected = this.options[this.selectedIndex];
  const objeto = selected.dataset.donacion?.toLowerCase() || "";
  let categoria = "General";

  if (["cama","mesa","silla","sofá","ropero","colchón","alfombra"].includes(objeto))
      categoria = "Mueble";
  else if (["camisa","pantalón","zapatos","chompa","casaca","vestido","falda"].includes(objeto))
      categoria = "Ropa";
  else if (["televisor","radio","refrigeradora","licuadora","microondas","ventilador","cocina"].includes(objeto))
      categoria = "Artefacto";
  else if (["celular","laptop","tablet","impresora","consola de videojuegos"].includes(objeto))
      categoria = "Electrónico";

  document.getElementById("tipo_donacion").value = categoria;
});
</script>

</body>
</html>