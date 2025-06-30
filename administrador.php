<?php
session_start();
include 'conexion.php';  // Conexión a la base de datos

function obtenerNivelOrden($nombreNivel) {
    $niveles = [
        'Doctorado' => 1,
        'Maestría' => 2,
        'Postgrado' => 3,
        'Ingeniería/Licenciatura' => 4,
        'Técnico' => 5,
        'Certificado (Escolar)' => 6,
    ];
    return $niveles[$nombreNivel] ?? 999;
}

$datos = null;
$documentos = [];
$error = "";

if (isset($_POST['buscar_cedula'])) {
    $cedula = trim($_POST['buscar_cedula']);

    if ($cedula !== '') {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE documento = ?");
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $datos = $resultado->fetch_assoc();

            // Buscar archivos PDF con nombre que empiece con la cédula + espacio
            $ruta = __DIR__ . '/pdf';
            $archivos = glob("$ruta/{$cedula} *.pdf");

            foreach ($archivos as $archivo) {
                $nombreArchivo = basename($archivo);
                preg_match('/^.+ (.+)\.pdf$/', $nombreArchivo, $matches);
                $nivel = $matches[1] ?? 'Otro';
                $orden = obtenerNivelOrden($nivel);
                $documentos[] = [
                    'ruta' => 'pdf/' . $nombreArchivo,
                    'nivel' => $nivel,
                    'orden' => $orden
                ];
            }

            usort($documentos, function($a, $b) {
                return $a['orden'] - $b['orden'];
            });

            $_SESSION['datos'] = $datos;
            $_SESSION['documentos'] = $documentos;
        } else {
            $_SESSION['error'] = "No se encontró ningún postulante con ese documento.";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Recuperar datos de sesión para mostrar tras redirect
if (isset($_SESSION['datos'])) {
    $datos = $_SESSION['datos'];
    $documentos = $_SESSION['documentos'] ?? [];
    unset($_SESSION['datos'], $_SESSION['documentos']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>POSTULAYA - ADMINISTRADOR</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> 

    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Librerias Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Top bar Inicio -->
    <div class="container-fluid bg-light pt-3 d-none d-lg-block">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 text-center text-lg-left mb-2 mb-lg-0">
                    <div class="d-inline-flex align-items-center">
                        <p><i class="fa fa-envelope mr-2"></i>postulaya@gmail.com</p>
                        <p class="text-body px-3">|</p>
                        <p><i class="fa fa-phone-alt mr-2"></i>+507 6009-1234</p>
                    </div>
                </div>
                <div class="col-lg-6 text-center text-lg-right">
                    <div class="d-inline-flex align-items-center">
                        <a class="text-primary px-3" href="">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a class="text-primary px-3" href="">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a class="text-primary px-3" href="">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a class="text-primary px-3" href="">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a class="text-primary pl-3" href="">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Top bar Fin -->

    <!-- Navbar inicio -->
    <div class="container-fluid position-relative nav-bar p-0">
        <div class="container-lg position-relative p-0 px-lg-3" style="z-index: 9;">
            <nav class="navbar navbar-expand-lg bg-light navbar-light shadow-lg py-3 py-lg-0 pl-3 pl-lg-5">
                <a href="index.html" class="navbar-brand">
                    <h1 class="m-0 text-primary"><span class="text-dark">POSTULA</span>YA</h1>
                </a>
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-between px-3" id="navbarCollapse">
                    <div class="navbar-nav ml-auto py-0">
                        <a href="index.html" class="nav-item nav-link">Inicio</a>

                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Navbar fin -->

    <!-- Header inicio -->
    <div class="container-fluid page-header">
        <div class="container">
            <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 400px">
                <h3 class="display-4 text-white text-uppercase">Administración</h3>
                <div class="d-inline-flex text-white">
                    <p class="m-0 text-uppercase"><a class="text-white" href="index.html">Inicio</a></p>
                    <i class="fa fa-angle-double-right pt-1 px-3"></i>
                    <p class="m-0 text-uppercase">Administración</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Header Fin -->

    <!-- Buscar postulante inicio -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center mb-3 pb-3">
                <h5 class="text-primary text-uppercase" style="letter-spacing: 5px;">Buscar postulante</h5>
                <h3>Ingresa la cédula o el pasaporte para buscar al postulante</h3>
            </div>
            <form method="POST">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <input 
                            type="text" 
                            name="buscar_cedula" 
                            class="form-control mb-3 p-3" 
                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9\-]/g, '')" 
                            placeholder="Cédula o pasaporte" 
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="col-md-2 text-center">
                        <button type="submit" class="btn btn-primary w-100 py-3">Buscar</button>
                    </div>
                </div>
            </form>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-4 text-center"> <?= htmlspecialchars($error) ?> </div>
            <?php endif; ?>

            <?php if ($datos): ?>
                <div class="card mt-5">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Datos del Postulante</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($datos as $campo => $valor): ?>
                            <?php if ($campo !== 'id'): ?>
                                <p><strong><?= ucfirst(str_replace('_', ' ', $campo)) ?>:</strong> <?= htmlspecialchars($valor) ?></p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (count($documentos) > 0): ?>
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Documentos Adjuntos</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($documentos as $index => $doc): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><strong><?= htmlspecialchars($doc['nivel']) ?></strong></span>
                                        <button 
                                            class="btn btn-outline-primary btn-sm" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#pdfViewer<?= $index ?>" 
                                            aria-expanded="false" 
                                            aria-controls="pdfViewer<?= $index ?>"
                                        >
                                            Ver / Cerrar PDF
                                        </button>
                                    </div>
                                    <div class="collapse mt-2" id="pdfViewer<?= $index ?>">
                                        <iframe src="<?= htmlspecialchars($doc['ruta']) ?>" width="100%" height="500px" style="border: 1px solid #ccc;"></iframe>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- Buscar postulante fin -->

    <!-- Footer Inicio -->
    <div class="container-fluid bg-dark text-white-50 py-5 px-sm-3 px-lg-5" style="margin-top: 90px;">
        <div class="row pt-5">

            <div class="col-lg-3 col-md-6 mb-5">
                <h5 class="text-white text-uppercase mb-4" style="letter-spacing: 5px;">Contactanos en</h5>
                <p><i class="fa fa-map-marker-alt mr-2"></i>Calle 74 Este, San Francisco, Ciudad de Panamá, Panamá</p>
                <p><i class="fa fa-phone-alt mr-2"></i>+507 6009-1234</p>
                <p><i class="fa fa-envelope mr-2"></i>postulaya@gmail.com</p>
            </div>

            <div class="col-lg-3 col-md-6 mb-5">
                <a href="" class="navbar-brand">
                    <h1 class="text-primary"><span class="text-white">POSTULA</span>YA</h1>
                </a>
                <p>Conectamos talento con oportunidades laborales reales. Sube tus diplomas y postúlate a nuestras vacantes disponibles.</p>
                <h6 class="text-white text-uppercase mt-4 mb-3" style="letter-spacing: 5px;">Siguenos en</h6>
                <div class="d-flex justify-content-start">
                    <a class="btn btn-outline-primary btn-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-outline-primary btn-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-outline-primary btn-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a class="btn btn-outline-primary btn-square" href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-white border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .1) !important;">
        <div class="row">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
                <p class="m-0 text-white-50">Copyright &copy; <a href="#"></a></a>
                </p>
            </div>
        </div>
    </div>
    <!-- Footer Fin -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-angle-double-up"></i></a>

    <!-- JavaScript Librerias -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

</body>
</html>