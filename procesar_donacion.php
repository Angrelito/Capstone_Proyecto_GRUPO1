<?php
include("conexion.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

// Cargar Dompdf desde la carpeta local
require 'dompdf/autoload.inc.php';

// ====== CAPTURAR DATOS DEL FORMULARIO ======
$nombre       = $_POST['nombre'];
$correo       = $_POST['correo'];
$telefono     = $_POST['telefono'];
$donacion  = $_POST['donacion'];
$descripcion  = $_POST['descripcion'];

// ====== GUARDAR EN LA BD ======
$sql = "INSERT INTO donantes (nombre, correo, telefono, donacion, descripcion) 
        VALUES ('$nombre', '$correo', '$telefono', '$donacion', '$descripcion')";

if ($conn->query($sql) === TRUE) {
    
    // ====== CREAR PDF CON DOMPDF ======
    $dompdf = new Dompdf();
    $htmlPDF = "
    <html>
    <head>
      <meta charset='UTF-8'>
      <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; margin: 20px; }
        .header { text-align: center; border-bottom: 3px solid #ED582D; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #ED582D; margin: 0; font-size: 22px; }
        .content h2 { color: #444; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table th, .info-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .info-table th { background: #f4f4f4; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
      </style>
    </head>
    <body>
      <div class='header'>
        <h1>Comprobante de Donación</h1>
      </div>
      <div class='content'>
        <h2>¡Gracias por tu donación, $nombre!</h2>
        <p>Estos son los datos de tu donación:</p>
        <table class='info-table'>
          <tr><th>Nombre</th><td>$nombre</td></tr>
          <tr><th>Correo</th><td>$correo</td></tr>
          <tr><th>Teléfono</th><td>$telefono</td></tr>
          <tr><th>Donacion</th><td>$donacion</td></tr>
          <tr><th>Descripción</th><td>$descripcion</td></tr>
        </table>
        <p>Nos pondremos en contacto contigo para coordinar la recolección.</p>
      </div>
      <div class='footer'>
        © ".date("Y")." Traperos de San Pablo | Todos los derechos reservados
      </div>
    </body>
    </html>
    ";

    $dompdf->loadHtml($htmlPDF);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfOutput = $dompdf->output();
    $pdfFile = "comprobante_donacion_" . time() . ".pdf";
    file_put_contents($pdfFile, $pdfOutput);

    // ====== ENVIAR CORREO CON PHPMailer ======
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hollowsilksongpablo@gmail.com'; // Gmail Empresa
        $mail->Password   = 'huun kfts ylve ffqa'; // Contraseña
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('hola@traperosdesanpablo.org', 'Traperos de San Pablo');
        $mail->addAddress($correo, $nombre);

        // Adjuntar PDF
        $mail->addAttachment($pdfFile);

        $mail->isHTML(true);
        $mail->Subject = 'Comprobante de Donación - Traperos de San Pablo';
        $mail->Body = "
        <div style='background:#f9f9f9; padding:20px; font-family:Arial, sans-serif;'>
            <div style='max-width:600px; margin:0 auto; background:#fff; border:1px solid #eee; border-radius:8px; padding:20px;'>
            <h2 style='color:#ED582D; text-align:center;'>¡Gracias por tu donación, $nombre!</h2>
            <p style='font-size:15px; color:#555; text-align:center;'>
                Hemos recibido tu solicitud de donación con los siguientes datos:
            </p>
            <table style='width:100%; border-collapse:collapse; margin:20px 0; font-size:14px;'>
                <tr><td style='padding:8px; border:1px solid #eee;'><b>Nombre</b></td><td style='padding:8px; border:1px solid #eee;'>$nombre</td></tr>
                <tr><td style='padding:8px; border:1px solid #eee;'><b>Correo</b></td><td style='padding:8px; border:1px solid #eee;'>$correo</td></tr>
                <tr><td style='padding:8px; border:1px solid #eee;'><b>Teléfono</b></td><td style='padding:8px; border:1px solid #eee;'>$telefono</td></tr>
                <tr><td style='padding:8px; border:1px solid #eee;'><b>Donacion</b></td><td style='padding:8px; border:1px solid #eee;'>$donacion</td></tr>
                <tr><td style='padding:8px; border:1px solid #eee;'><b>Descripción</b></td><td style='padding:8px; border:1px solid #eee;'>$descripcion</td></tr>
            </table>
            <p style='color:#555; font-size:14px;'>
                En breve nos pondremos en contacto contigo para coordinar la recolección. 
                También encontrarás adjunto tu comprobante en formato PDF.
            </p>
            <div style='text-align:center; margin-top:30px; font-size:12px; color:#999;'>
                © ".date("Y")." Traperos de San Pablo | Todos los derechos reservados
            </div>
            </div>
        </div>
        ";

        $mail->send();

        // Borrar archivo temporal del servidor
        unlink($pdfFile);

        header("Location: donar_mueble.html?success=1");
        exit;

    } catch (Exception $e) {
        // Si falla el envío de correo
        if (file_exists($pdfFile)) unlink($pdfFile);
        header("Location: donar_mueble.html?error=1");
        exit;
    }
} else {
    // Si falla la BD
    header("Location: donar_mueble.html?error=1");
    exit;
}

$conn->close();
?>