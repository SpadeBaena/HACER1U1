<?php
session_start(); 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $nombre = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $correo_usuario = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9]/', '', filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING)); // Capturamos el teléfono
    $mensaje_usuario = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if (empty($nombre) || empty($correo_usuario) || empty($phone) || empty($mensaje_usuario) || !filter_var($correo_usuario, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message_status'] = 'error';
        $_SESSION['message_text'] = 'Por favor, completa todos los campos correctamente.';
        $_SESSION['contact_form_submitted'] = true; 
        header("Location: bienvenido.php"); 
        exit;
    }

    $mail = new PHPMailer(true); 

    try {
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'baenaspoti@gmail.com'; 
        $mail->Password = 'qlnm qgtj ifwa nbqi'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8'; 

        
        $mail->setFrom('baenaspoti@gmail.com', 'Protect-U'); 
        
        $mail->addAddress($correo_usuario, $nombre);
        $mail->addReplyTo('baenaspoti@gmail.com', 'Protect-U Soporte'); 

       
        $mail->isHTML(true);
        $mail->Subject = '¡Gracias por contactarnos, ' . $nombre . '!';
        $mail->Body = "
            <h3>Hola $nombre,</h3>
            <p>Gracias por escribirnos. Hemos recibido tu mensaje:</p>
            <blockquote>$mensaje_usuario</blockquote>
            <p>Vamos a revisar tu solicitud, Estamos agradecidos de que te acerques con nosotros.
             Te contactaremos lo más pronto posible una vez hayamos revisado tu Solicitud.</p>
            <br>
            <strong>Atentamente,<br>El equipo de Protect-U</strong>
        ";

        $mail->send();

        $mail->clearAddresses(); 
        $mail->clearReplyTos();  
        $mail->clearAllRecipients(); 

      
        $mail->addAddress('baenaspoti@gmail.com', 'Administrador'); 
        $mail->setFrom('no-reply@tudominio.com', 'Formulario Web'); 
        $mail->addReplyTo($correo_usuario, $nombre); 

        
        $mail->Subject = 'Nuevo Mensaje de Contacto de ' . $nombre;
        $mail->Body = "
            <h3>Tienes un nuevo mensaje de contacto desde tu sitio web:</h3>
            <p><strong>Nombre:</strong> $nombre</p>
            <p><strong>Email:</strong> $correo_usuario</p>
            <p><strong>Teléfono:</strong> $phone</p>
            <p><strong>Mensaje:</strong></p>
            <blockquote>$mensaje_usuario</blockquote>
            <p>Responde a este correo o contacta al usuario directamente.</p>
        ";

       
        $mail->send();

        $country_code_for_whatsapp = "52"; 
        $your_whatsapp_number = "8441476201"; 
        $user_whatsapp_initial_message = urlencode("Hola, mi nombre es " . $nombre . ". Te contacto desde tu sitio web sobre mi solicitud: " . substr($mensaje_usuario, 0, 150) . "...");
        $whatsapp_link_to_you = "https://wa.me/" . $country_code_for_whatsapp . $your_whatsapp_number . "?text=" . $user_whatsapp_initial_message;

   
        $_SESSION['message_status'] = 'success';
        $_SESSION['message_text'] = '¡Tu mensaje ha sido enviado exitosamente! Hemos enviado un correo de confirmación. Si lo deseas, puedes chatear directamente con nosotros por WhatsApp.';
        $_SESSION['whatsapp_link'] = $whatsapp_link_to_you;
        $_SESSION['contact_form_submitted'] = true; 

        header("Location: bienvenido.php"); 
        exit;

    } catch (Exception $e) {
        $_SESSION['message_status'] = 'error';
        $_SESSION['message_text'] = 'No se pudo enviar tu mensaje. Por favor, inténtalo de nuevo más tarde o contáctanos por otros medios. Error técnico: ' . $mail->ErrorInfo;
        $_SESSION['contact_form_submitted'] = true;
        header("Location: bienvenido.php"); 
        exit;
    }
} else {
    header("Location: bienvenido.php"); 
    exit;
}
?>