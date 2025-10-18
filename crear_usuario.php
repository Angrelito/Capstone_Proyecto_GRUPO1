<?php
require 'conexion.php';

$usuario = 'Silver';               // nombre del usuario que quieres crear
$email = 'shermanhornet@gmail.com';  // correo del usuario
$password = '123456';              // contraseña en texto plano
$hash = password_hash($password, PASSWORD_DEFAULT);  // genera el hash

$sql = "INSERT INTO empleados (usuario, password, email) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);   // 🔹 cambiar $conexion por $conn
$stmt->bind_param("sss", $usuario, $hash, $email);

if ($stmt->execute()) {
    echo "✅ Usuario creado correctamente.";
} else {
    echo "❌ Error: " . $stmt->error;
}
?>