<?php
session_start();
require_once 'db.php';

$new_password_err = $confirm_password_err = $message = "";
$token_from_url = "";

if (isset($_GET["token"]) && !empty(trim($_GET["token"]))) {
    $token_from_url = trim($_GET["token"]);

    $sql = "SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expire_at > NOW()";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_token);
        $param_token = $token_from_url;
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 0) {
                $message = "El enlace de restablecimiento de contraseña no es válido o ha expirado. Por favor, solicita uno nuevo.";
            } else {
                $stmt->bind_result($user_id_for_reset); 
                $stmt->fetch();
            }
        } else {
            $message = "¡Ups! Algo salió mal al verificar el token.";
        }
        $stmt->close();
    } else {
        $message = "ERROR: No se pudo preparar la consulta SQL para verificar el token.";
    }
} else {
    $message = "No se proporcionó un token de restablecimiento de contraseña.";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token_from_form = $_POST["token"]; 
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($new_password)) {
        $new_password_err = "Por favor, ingresa una nueva contraseña.";
    } elseif (strlen($new_password) < 3) {
        $new_password_err = "La contraseña debe tener al menos 3 caracteres.";
    }

    if (empty($confirm_password)) {
        $confirm_password_err = "Por favor, confirma la nueva contraseña.";
    } elseif ($new_password !== $confirm_password) {
        $confirm_password_err = "Las contraseñas no coinciden.";
    }

    if (empty($new_password_err) && empty($confirm_password_err)) {
        $sql = "SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expire_at > NOW()";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_token);
            $param_token = $token_from_form;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($user_id_from_db);
                    $stmt->fetch();

                    $update_sql = "UPDATE usuarios SET pwd = ?, reset_token = NULL, reset_token_expire_at = NULL WHERE id = ?";
                    if ($update_stmt = $conn->prepare($update_sql)) {
                        $update_stmt->bind_param("si", $new_password, $user_id_from_db); 
                        if ($update_stmt->execute()) {
                            $_SESSION['reset_message'] = "¡Tu contraseña ha sido restablecida exitosamente! Ya puedes iniciar sesión.";
                            header("location: Login.php");
                            exit;
                        } else {
                            $message = "Error al actualizar la contraseña. Por favor, inténtalo de nuevo.";
                        }
                        $update_stmt->close();
                    } else {
                        $message = "ERROR: No se pudo preparar la consulta para actualizar la contraseña.";
                    }
                } else {
                    $message = "El token de restablecimiento no es válido o ha expirado. Por favor, solicita uno nuevo.";
                }
            } else {
                $message = "¡Ups! Algo salió mal al verificar el token.";
            }
            $stmt->close();
        } else {
            $message = "ERROR: No se pudo preparar la consulta SQL para verificar el token.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
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
            margin-bottom: 25px;
        }

        .login-container label {
            display: block;
            margin-bottom: 8px;
            text-align: left;
            font-weight: bold;
        }

        .login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            color: black;
        }

        .login-container button[type="submit"],
        .login-container .button-secondary {
            background-color: #ffc800;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .login-container button[type="submit"]:hover,
        .login-container .button-secondary:hover {
            background-color: #e0b000;
        }

        .error-message {
            color: #ff6b6b;
            margin-top: -10px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        
        .success-message {
            color: #25d366; 
            margin-bottom: 15px;
            font-size: 1em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Restablecer Contraseña</h2>
        <?php 
        if (!empty($message)) {
            if (strpos($message, "exitosamente") !== false) {
                echo '<div class="success-message">' . $message . '</div>';
            } else {
                echo '<div class="error-message">' . $message . '</div>';
            }
        }
        if (empty($message) && !empty($token_from_url)) {
        ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_from_url); ?>">

                <label for="new_password">Nueva Contraseña</label>
                <input type="password" name="new_password" required>
                <?php if (!empty($new_password_err)) echo '<div class="error-message">' . $new_password_err . '</div>'; ?>

                <label for="confirm_password">Confirmar Contraseña</label>
                <input type="password" name="confirm_password" required>
                <?php if (!empty($confirm_password_err)) echo '<div class="error-message">' . $confirm_password_err . '</div>'; ?>

                <button type="submit">Cambiar Contraseña</button>
            </form>
        <?php 
        } else {
            echo '<p style="margin-top: 10px;"><a href="Login.php" class="button-secondary">Volver al Login</a></p>';
        }
        ?>
    </div>
</body>
</html>