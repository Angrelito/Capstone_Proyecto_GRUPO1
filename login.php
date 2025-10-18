<?php
session_start();
require 'conexion.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario  = $_POST['usuario'];
    $password = $_POST['password'];

    // Buscar usuario
    $sql = "SELECT * FROM empleados WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // ✅ Generar código
            $codigo = rand(100000, 999999);
            $_SESSION['codigo_verificacion'] = $codigo;
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nombre'] = $row['usuario'];
            // --- Enviar correo ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'hollowsilksongpablo@gmail.com';      // tu Gmail empresa
                $mail->Password   = 'huun kfts ylve ffqa';        // contraseña de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('tu_correo@gmail.com', 'ONG Traperos de San Pablo');
                $mail->addAddress($row['email']); // al correo del empleado

                $mail->isHTML(true);
                $mail->Subject = "Tu código de verificación";
                $mail->Body    = "Hola <b>{$row['usuario']}</b>,<br> Tu código de acceso es: <h2>$codigo</h2>";

                $mail->send();
                header("Location: login.html?verificar=1");
                exit();
            } catch (Exception $e) {
                echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
            }
        } else {
            // Contraseña incorrecta
            header("Location: login.html?error=2");
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: login.html?error=3");
        exit();
    }
}
?>
