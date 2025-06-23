<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: Login.php");
    exit;
}


$show_modal = false;
$modal_title = "";
$modal_body_content = "";
$modal_whatsapp_link = "";
$modal_class = ""; 

if (isset($_SESSION['contact_form_submitted']) && $_SESSION['contact_form_submitted'] === true) {
    $show_modal = true;
    unset($_SESSION['contact_form_submitted']); 

    if (isset($_SESSION['message_status']) && $_SESSION['message_status'] == 'success') {
        $modal_title = "¡Mensaje Enviado!";
        $modal_body_content = $_SESSION['message_text'];
        $modal_whatsapp_link = isset($_SESSION['whatsapp_link']) ? $_SESSION['whatsapp_link'] : '';
        $modal_class = "modal-success"; 
    } else {
        $modal_title = "Error al Enviar Mensaje";
        $modal_body_content = isset($_SESSION['message_text']) ? $_SESSION['message_text'] : 'Ocurrió un problema inesperado. Por favor, inténtalo de nuevo.';
        $modal_class = "modal-error"; 
    }

    unset($_SESSION['message_status']);
    unset($_SESSION['message_text']);
    unset($_SESSION['whatsapp_link']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización</title> <link rel="icon" type="image/x-icon" href="assets/favicon.png" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        /* Estilos para el modal (puedes moverlos a tu css/styles.css si prefieres) */
        .modal-content.modal-success {
            border: 2px solid #28a745; /* Borde verde */
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.5);
        }
        .modal-content.modal-error {
            border: 2px solid #dc3545; /* Borde rojo */
            box-shadow: 0 0 15px rgba(220, 53, 69, 0.5);
        }
        .modal-body p {
            font-size: 1.1em;
        }
        .btn-success { /* Ajusta el botón de WhatsApp */
            background-color: #25d366;
            border-color: #25d366;
            color: white;
        }
        .btn-success:hover {
            background-color: #1da851;
            border-color: #1da851;
        }
       
    </style>
</head>
<body id="page-top">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top"><img src="assets/img/team/logo.png" alt="..." /></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="fas fa-bars ms-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#services">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html">Regresar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="page-section" id="contact">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Contáctanos</h2>
                <h3 class="section-subheading text-muted">Inserta a continuación tus datos, escríbenos lo que necesitas, y nos pondremos en contacto contigo</h3>
            </div>

            

            <form id="contactForm" action="enviar_correo.php" method="POST">
               <div class="row align-items-stretch mb-5">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input class="form-control" id="name" name="name" type="text" placeholder="Tu Nombre *" data-sb-validations="required" />
                            <div class="invalid-feedback" data-sb-feedback="name:required">Un nombre es requerido.</div>
                        </div>
                        <div class="form-group">
                            <input class="form-control" id="email" name="email" type="email" placeholder="Tu Email *" data-sb-validations="required,email" />
                            <div class="invalid-feedback" data-sb-feedback="email:required">Tu correo es requerido.</div>
                            <div class="invalid-feedback" data-sb-feedback="email:email">El email no es válido.</div>
                        </div>
                        <div class="form-group mb-md-0">
                            <input class="form-control" id="phone" name="phone" type="tel" placeholder="Número de telefono*" data-sb-validations="required" />
                            <div class="invalid-feedback" data-sb-feedback="phone:required">Un número de teléfono es requerido.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group form-group-textarea mb-md-0">
                            <textarea class="form-control" id="message" name="message" placeholder="Escribe tu idea*" data-sb-validations="required"></textarea>
                            <div class="invalid-feedback" data-sb-feedback="message:required">Un mensaje es requerido.</div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button class="btn btn-primary btn-xl text-uppercase" id="submitButton" type="submit">
                        Enviar Mensaje
                    </button>
                </div>
            </form>
        </div>
    </section>

    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content <?php echo $modal_class; ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel"><?php echo $modal_title; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo $modal_body_content; ?></p>
                    <?php if (!empty($modal_whatsapp_link)): ?>
                        <p class="text-center mt-3">
                            <a href="<?php echo htmlspecialchars($modal_whatsapp_link); ?>" target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp"></i> Chatear por WhatsApp
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($show_modal): ?>
                var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                messageModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>