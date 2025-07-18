<?php
session_start();
require_once 'db.php';


$message = "";

if (isset($_GET["token"]) && !empty(trim($_GET["token"]))) {
    $token_received = trim($_GET["token"]);

    $sql = "SELECT id, activo FROM usuarios WHERE token_activacion = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_token);
        $param_token = $token_received;

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($user_id, $is_active);
                $stmt->fetch();

                if ($is_active == 1) {
                    $message = "¡Tu cuenta ya ha sido activada previamente! Puedes iniciar sesión.";
                } else {
                    $update_sql = "UPDATE usuarios SET activo = 1, token_activacion = NULL WHERE id = ?";
                    if ($update_stmt = $conn->prepare($update_sql)) {
                        $update_stmt->bind_param("i", $user_id);
                        if ($update_stmt->execute()) {
                            $message = "¡Felicidades! Tu cuenta ha sido activada exitosamente. Ahora puedes <a href='Login.php'>iniciar sesión</a>.";
                        } else {
                            $message = "Error al activar la cuenta. Por favor, inténtalo de nuevo más tarde.";
                        }
                        $update_stmt->close();
                    } else {
                        $message = "ERROR: No se pudo preparar la consulta para activar la cuenta.";
                    }
                }
            } else {
                $message = "El token de activación no es válido o ha expirado.";
            }
        } else {
            $message = "¡Ups! Algo salió mal al verificar el token.";
        }
        $stmt->close();
    } else {
        $message = "ERROR: No se pudo preparar la consulta SQL para verificar el token.";
    }
} else {
    $message = "No se proporcionó un token de activación válido.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Cuenta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('assets/img/header-bg.jpg');
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: white;
        }

        .login-container { 
            background-color: #212529;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.95);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-container h2 {
            color: #ffc800; 
            margin-bottom: 25px;
        }
        .login-container p {
            color: white;
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        .login-container .success-message {
            color: #25d366; 
            margin-bottom: 15px;
            font-size: 1em;
            font-weight: bold;
        }
        .login-container .error-message {
            color: #ff6b6b; 
            margin-bottom: 15px;
            font-size: 1em;
            font-weight: bold;
        }
        .login-container a {
            color: #ffc800; 
            text-decoration: none;
            font-weight: bold;
        }
        .login-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Activación de Cuenta</h2>
        <?php 
        if (strpos($message, "exitosamente") !== false || strpos($message, "ya ha sido activada") !== false) {
            echo '<div class="success-message">' . $message . '</div>';
        } else {
            echo '<div class="error-message">' . $message . '</div>';
        }
        ?>
        <p style="margin-top: 20px;"><a href="Login.php">Volver al Login</a></p>
    </div>
</body>
</html>