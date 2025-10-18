<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_ingresado = $_POST['codigo'];

    if ($codigo_ingresado == $_SESSION['codigo_verificacion']) {
        header("Location: login.html?success=1");
        exit();
    } else {
        session_destroy();
        header("Location: login.html?error=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verificación</title>
</head>
<body>
  <h2>Introduce el código que recibiste en tu correo</h2>
  <form method="POST">
    <input type="text" name="codigo" placeholder="Código" required>
    <button type="submit">Verificar</button>
  </form>
  <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
</body>
</html>
