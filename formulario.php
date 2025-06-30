<?php
include 'conexion.php';

$mensaje = "";
$mostrarModalExito = false;
$mostrarModalError = false;
$mensajeError = "";

function calcularEdad($fecha_nacimiento) {
    $fecha = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    return $hoy->diff($fecha)->y;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolectar datos
    $genero = $_POST['genero'] ?? null;
    $primer_nombre = $_POST['primer_nombre'] ?? '';
    $segundo_nombre = $_POST['segundo_nombre'] ?? '';
    $primer_apellido = $_POST['primer_apellido'] ?? '';
    $segundo_apellido = $_POST['segundo_apellido'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $codigo_pais = $_POST['codigo_pais'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $corregimiento = $_POST['corregimiento'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $estado_civil = $_POST['estado_civil'] ?? '';

    if (!$genero || !$primer_nombre || !$primer_apellido || !$tipo_documento || !$documento || !$fecha_nacimiento) {
        $mensajeError = "Faltan campos obligatorios.";
        $mostrarModalError = true;
    } else {
        // Verificar duplicado
        $verificar = $conn->prepare("SELECT id FROM usuarios WHERE documento = ?");
        $verificar->bind_param("s", $documento);
        $verificar->execute();
        $verificar->store_result();

        if ($verificar->num_rows > 0) {
            $mensajeError = "Ya existe una postulación con este número de documento.";
            $mostrarModalError = true;
        } else {
            $edad = calcularEdad($fecha_nacimiento);

            $stmt = $conn->prepare("INSERT INTO usuarios 
                (genero, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, tipo_documento, documento, fecha_nacimiento, edad, codigo_pais, telefono, correo, provincia, corregimiento, direccion, estado_civil)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssisssssss", 
                $genero, $primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido,
                $tipo_documento, $documento, $fecha_nacimiento, $edad,
                $codigo_pais, $telefono, $correo, $provincia, $corregimiento, $direccion, $estado_civil);

            if ($stmt->execute()) {
                // Subir PDF
                $niveles = $_POST['nivel_academico'] ?? [];
                $archivos = $_FILES['archivo_pdf'] ?? null;

                if ($archivos && isset($archivos['name']) && is_array($archivos['name'])) {
                    $carpeta_destino = __DIR__ . '/pdf/';
                    if (!file_exists($carpeta_destino)) {
                        mkdir($carpeta_destino, 0777, true);
                    }

                    for ($i = 0; $i < count($archivos['name']); $i++) {
                        $nombre_original = $archivos['name'][$i];
                        $tmp_name = $archivos['tmp_name'][$i];
                        $nivel = $niveles[$i] ?? '';

                        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                        if ($extension !== 'pdf') continue;

                        $nuevo_nombre = $documento . ' ' . $nivel . '.pdf';
                        $ruta_destino = $carpeta_destino . $nuevo_nombre;

                        move_uploaded_file($tmp_name, $ruta_destino);
                    }
                }

                $mostrarModalExito = true;
                $_POST = [];
                $_FILES = [];
            } else {
                $mensajeError = "Error al guardar datos: " . $stmt->error;
                $mostrarModalError = true;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>POSTULAYA - POSTULARSE</title>
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

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

    <!-- Modal éxito -->
    <div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="modalExitoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="modalExitoLabel">Postulación enviada</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            ¡Tu postulación fue enviada correctamente!
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" data-bs-dismiss="modal">Cerrar</button>
        </div>
        </div>
    </div>
    </div>

    <!-- Modal error -->
    <div class="modal fade" id="modalError" tabindex="-1" aria-labelledby="modalErrorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="modalErrorLabel">Error</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <?= htmlspecialchars($mensajeError) ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
        </div>
        </div>
    </div>
    </div>

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


    <!-- Navbar Start -->
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
    <!-- Navbar End -->


    <!-- Header Start -->
    <div class="container-fluid page-header">
        <div class="container">
            <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 400px">
                <h3 class="display-4 text-white text-uppercase">Postularme</h3>
                <div class="d-inline-flex text-white">
                    <p class="m-0 text-uppercase"><a class="text-white" href="">Inicio</a></p>
                    <i class="fa fa-angle-double-right pt-1 px-3"></i>
                    <p class="m-0 text-uppercase">Postularme</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->


<!-- Postulación Start -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="text-center mb-3 pb-3">
            <h5 class="text-primary text-uppercase" style="letter-spacing: 5px;">Postularme</h5>
            <h3>Llena todos los campos para completar tu postulación</h3>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="contact-form bg-white p-4">
                <form id="formularioPostulacion" action="" method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <!-- Género -->
                            <div class="form-group col-md-12">
                                <label>Seleccione su Género:</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="genero" id="genero_hombre" value="Hombre" required onchange="actualizarEstadoCivil()">
                                    <label class="form-check-label" for="genero_hombre">Hombre</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="genero" id="genero_mujer" value="Mujer" onchange="actualizarEstadoCivil()">
                                    <label class="form-check-label" for="genero_mujer">Mujer</label>
                                </div>
                            </div>

                            <!-- Nombres, apellidos -->
                            <div class="form-group col-md-6">
                                <label>Primer Nombre</label>
                                <input type="text" name="primer_nombre" class="form-control solo-letras" required maxlength="15">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Segundo Nombre</label>
                                <input type="text" name="segundo_nombre" class="form-control solo-letras" maxlength="15">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Primer Apellido</label>
                                <input type="text" name="primer_apellido" class="form-control solo-letras" required maxlength="15">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Segundo Apellido</label>
                                <input type="text" name="segundo_apellido" class="form-control solo-letras" maxlength="15">
                            </div>

                            <!-- Tipo de Documento -->
                            <div class="form-group col-md-6">
                                <label>Tipo de Documento</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo_documento" id="tipo_cedula" value="cedula" onchange="cambiarValidacionDocumento()" required>
                                    <label class="form-check-label" for="tipo_cedula">Cédula</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo_documento" id="tipo_pasaporte" value="pasaporte" onchange="cambiarValidacionDocumento()">
                                    <label class="form-check-label" for="tipo_pasaporte">Pasaporte</label>
                                </div>
                            </div>

                            <!-- Campo de Documento -->
                            <div class="form-group col-md-6">
                                <label id="label_documento">Número de Documento</label>
                                
                                <div class="input-group">
                                    <!-- Select de provincias -->
                                    <select id="cedula_provincia" class="form-control" style="display: none; max-width: 200px;" onchange="colocarPrefijoCedula()">
                                        <option value="">Seleccione</option>
                                        <option value="1">1 - Bocas del Toro</option>
                                        <option value="2">2 - Coclé</option>
                                        <option value="3">3 - Colón</option>
                                        <option value="4">4 - Chiriquí</option>
                                        <option value="5">5 - Darién</option>
                                        <option value="6">6 - Herrera</option>
                                        <option value="7">7 - Los Santos</option>
                                        <option value="8">8 - Panamá Oeste</option>
                                        <option value="9">9 - Panamá</option>
                                        <option value="10">10 - Veraguas</option>
                                        <option value="11">11 - Comarca Guna Yala</option>
                                        <option value="12">12 - Comarca Emberá-Wounaan</option>
                                        <option value="13">13 - Comarca Ngäbe-Buglé</option>
                                    </select>

                                    <!-- Input para el documento -->
                                    <input type="text" name="documento" id="documento" class="form-control" disabled required maxlength="20">
                                </div>
                            </div>
                            
                            <!-- Fecha de Nacimiento -->
                            <div class="form-group col-md-6">
                                <label>Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required onchange="calcularEdad()">
                            </div>

                            <!-- Edad (solo lectura) -->
                            <div class="form-group col-md-6">
                                <label>Edad</label>
                                <input type="text" id="edad" name="edad" class="form-control" readonly>
                            </div>
                            
                            <!-- Teléfono, Correo -->
                            <div class="form-group col-md-6">
                                <label>Número Telefónico</label>
                                <div class="input-group">
                                    <!-- Select de código internacional -->
                                    <select id="codigo_pais" name="codigo_pais" class="form-control" style="max-width: 130px;" required>
                                        <option value="+507">+507 (Panamá)</option>
                                        <option value="+1">+1 (EE.UU./Canadá)</option>
                                        <option value="+52">+52 (México)</option>
                                        <option value="+506">+506 (Costa Rica)</option>
                                        <option value="+593">+593 (Ecuador)</option>
                                        <option value="+51">+51 (Perú)</option>
                                        <option value="+54">+54 (Argentina)</option>
                                        <option value="+55">+55 (Brasil)</option>
                                        <option value="+34">+34 (España)</option>
                                        <option value="+44">+44 (Reino Unido)</option>
                                        <!-- Agrega más países si lo deseas -->
                                    </select>

                                    <!-- Solo números -->
                                    <input type="tel" id="telefono" name="telefono" class="form-control" placeholder="Ej: 61234567" required maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Correo Electrónico</label>
                                <input type="email" name="correo" id="correo" class="form-control" placeholder="Ej: nombre@dominio.com" required>
                                <small id="correo_error" class="form-text text-danger" style="display: none;">Correo inválido. Ej: nombre@dominio.com</small>
                            </div>

                            <!-- Provincia -->
                            <div class="form-group col-md-6">
                                <label>Provincia</label>
                                <select name="provincia" id="provincia" class="form-control" onchange="cargarCorregimientos()" required>
                                    <option value="">Seleccione</option>
                                    <option value="Bocas del Toro">Bocas del Toro</option>
                                    <option value="Coclé">Coclé</option>
                                    <option value="Colón">Colón</option>
                                    <option value="Chiriquí">Chiriquí</option>
                                    <option value="Darién">Darién</option>
                                    <option value="Herrera">Herrera</option>
                                    <option value="Los Santos">Los Santos</option>
                                    <option value="Panamá Oeste">Panamá Oeste</option>
                                    <option value="Panamá">Panamá</option>
                                    <option value="Veraguas">Veraguas</option>
                                    <option value="Comarca Guna Yala">Comarca Guna Yala</option>
                                    <option value="Comarca Emberá-Wounaan">Comarca Emberá-Wounaan</option>
                                    <option value="Comarca Ngäbe-Buglé">Comarca Ngäbe-Buglé</option>
                                </select>
                            </div>

                            <!-- Corregimiento (usando el mismo ID que el script) -->
                            <div class="form-group col-md-6">
                                <label>Corregimiento</label>
                                <select name="corregimiento" id="corregimiento" class="form-control" required>
                                    <option value="">Seleccione una provincia primero</option>
                                </select>
                            </div>


                            <div class="form-group col-md-6">
                                <label>Dirección de residencia</label>
                                <input type="text" name="direccion" class="form-control" required>
                            </div>

                            <!-- Estado Civil -->
                            <div class="form-group col-md-6">
                                <label>Estado Civil</label>
                                <select name="estado_civil" id="estado_civil" class="form-control" required>
                                    <option value="">Seleccione</option>
                                </select>
                            </div>

                            <!-- Contenedor dinámico de documentos -->
                            <div id="contenedorDocumentos">
                                <div class="form-group row grupo-documento">
                                    <div class="col-md-6">
                                        <label>Nivel Académico</label>
                                        <select name="nivel_academico[]" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            <?php
                                            $conn = new mysqli("localhost", "root", "", "postulaya");
                                            $result = $conn->query("SELECT * FROM academico");
                                            while($row = $result->fetch_assoc()) {
                                                echo '<option value="'.$row['nivel'].'">'.$row['nivel'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Subir Documento (Solo PDF)</label>
                                        <input type="file" name="archivo_pdf[]" class="form-control" accept=".pdf" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón para añadir más documentos -->
                            <div class="form-group col-md-12 text-right">
                                <button type="button" class="btn btn-secondary" onclick="agregarDocumento()">Agregar otro documento</button>
                            </div>


                            <!-- Botón Enviar -->
                            <div class="form-group col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">Enviar Postulación</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Postulación End -->

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

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- Bootstrap 5 JS: necesario para el modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script src="js/scripts.js"></script>

    <!-- Script para Edad y Estado Civil -->
    <script>
    function calcularEdad() {
        const fechaNacimiento = document.getElementById('fecha_nacimiento').value;
        if (fechaNacimiento) {
            const hoy = new Date();
            const nacimiento = new Date(fechaNacimiento);
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const m = hoy.getMonth() - nacimiento.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }
            document.getElementById('edad').value = edad;
        }
    }

    function actualizarEstadoCivil() {
        const estadoCivil = document.getElementById('estado_civil');
        const genero = document.querySelector('input[name="genero"]:checked')?.value;

        // Limpiar opciones
        estadoCivil.innerHTML = '<option value="">Seleccione</option>';

        if (genero === 'Hombre') {
            estadoCivil.innerHTML += '<option value="Soltero">Soltero</option>';
            estadoCivil.innerHTML += '<option value="Casado">Casado</option>';
            estadoCivil.innerHTML += '<option value="Unido">Unido</option>';
        } else if (genero === 'Mujer') {
            estadoCivil.innerHTML += '<option value="Soltera">Soltera</option>';
            estadoCivil.innerHTML += '<option value="Casada">Casada</option>';
            estadoCivil.innerHTML += '<option value="Unida">Unida</option>';
        }
    }
    </script>

    <script>
    const corregimientosPorProvincia = {
        "Panamá": ["Bella Vista", "San Francisco", "Parque Lefevre", "Juan Díaz", "Las Mañanitas"],
        "Panamá Oeste": ["Arraiján", "La Chorrera", "Capira", "Chame", "San Carlos"],
        "Colón": ["Cristóbal", "Cativá", "Sabanitas", "Buena Vista"],
        "Chiriquí": ["David", "Boquete", "Dolega", "Bugaba"],
        "Coclé": ["Penonomé", "Natá", "Aguadulce"],
        "Veraguas": ["Santiago", "Atalaya", "Soná"],
        "Los Santos": ["Las Tablas", "Guararé", "Pedasí"],
        "Herrera": ["Chitré", "Parita", "Pesé"],
        "Bocas del Toro": ["Changuinola", "Almirante", "Bastimentos"],
        "Darién": ["La Palma", "Garachiné", "Yaviza"],
        "Comarca Guna Yala": ["Ailigandí", "Narganá", "Puerto Obaldía"],
        "Comarca Ngäbe-Buglé": ["Soloy", "Hato Chamí", "San Félix"],
        "Comarca Emberá-Wounaan": ["Lajas Blancas", "Manené", "Boca de Sábalo"]
    };

    function cargarCorregimientos() {
        const provincia = document.getElementById("provincia").value;
        const corregimientoSelect = document.getElementById("corregimiento");
        corregimientoSelect.innerHTML = '<option value="">Seleccione</option>';

        if (provincia && corregimientosPorProvincia[provincia]) {
            corregimientosPorProvincia[provincia].forEach(correg => {
                const option = document.createElement("option");
                option.value = correg;
                option.text = correg;
                corregimientoSelect.add(option);
            });
        } else {
            corregimientoSelect.innerHTML = '<option value="">Seleccione una provincia primero</option>';
        }
    }
    </script>

    <script>
        function cambiarValidacionDocumento() {
            const tipoCedula = document.getElementById('tipo_cedula').checked;
            const input = document.getElementById('documento');
            const label = document.getElementById('label_documento');
            const select = document.getElementById('cedula_provincia');

            input.disabled = false;
            input.value = '';
            select.value = '';
            select.style.display = tipoCedula ? 'block' : 'none';

            // Limpieza de eventos
            input.oninput = null;
            input.onkeydown = null;
            input.onblur = null;

            if (tipoCedula) {
                label.innerText = 'Cédula';
                input.placeholder = 'Ej: 8-1067-980';

                input.addEventListener('input', validarEntradaCedula);
                input.addEventListener('keydown', prevenirBorradoPrefijo);
            } else {
                label.innerText = 'ID de Pasaporte';
                input.placeholder = 'Ej: A12345678';

                select.style.display = 'none';

                input.oninput = function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
                };
                input.onblur = function () {
                    this.value = this.value.toUpperCase();
                };
                input.removeEventListener('input', validarEntradaCedula);
                input.removeEventListener('keydown', prevenirBorradoPrefijo);
            }
        }

        function colocarPrefijoCedula() {
            const select = document.getElementById('cedula_provincia');
            const input = document.getElementById('documento');

            if (select.value !== '') {
                input.value = `${select.value}-`;
            } else {
                input.value = '';
            }
        }

        // Evita eliminar el prefijo (ej: 8-)
        function prevenirBorradoPrefijo(e) {
            const input = e.target;
            const prefijo = input.value.split('-')[0] + '-';

            // No permitir borrar antes del guion
            if ((input.selectionStart <= prefijo.length) && 
                (e.key === 'Backspace' || e.key === 'Delete')) {
                e.preventDefault();
            }
        }

        // Solo permitir números y guiones después del prefijo
        function validarEntradaCedula(e) {
            const input = e.target;
            const prefijo = input.value.split('-')[0] + '-';
            let resto = input.value.slice(prefijo.length);

            // Limpiar caracteres no permitidos
            resto = resto.replace(/[^0-9\-]/g, '');

            // Reconstruir el valor
            input.value = prefijo + resto;
        }
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
            const camposTexto = document.querySelectorAll(".solo-letras");

            camposTexto.forEach(function (campo) {
                campo.addEventListener("input", function () {
                    // Permite solo letras con tildes, ñ y espacios
                    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
                });

                campo.addEventListener("blur", function () {
                    // Convierte a mayúsculas al salir del campo
                    this.value = this.value.toUpperCase();
                });
            });
        });
    </script>

    <script>
    function agregarDocumento() {
        const contenedor = document.getElementById("contenedorDocumentos");

        // Crear el nuevo grupo de campos
        const grupo = document.createElement("div");
        grupo.className = "form-group row grupo-documento";

        grupo.innerHTML = `
            <div class="col-md-6">
                <label>Nivel Académico</label>
                <select name="nivel_academico[]" class="form-control" required>
                    <option value="">Seleccione</option>
                    <?php
                    $conn = new mysqli("localhost", "root", "", "postulaya");
                    $result = $conn->query("SELECT * FROM academico");
                    while($row = $result->fetch_assoc()) {
                        echo '<option value="'.$row['nivel'].'">'.$row['nivel'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-5">
                <label>Subir Documento (Solo PDF)</label>
                <input type="file" name="archivo_pdf[]" class="form-control" accept=".pdf" required>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-block" onclick="eliminarDocumento(this)">X</button>
            </div>
        `;

        contenedor.appendChild(grupo);
    }

    function eliminarDocumento(boton) {
        const grupo = boton.closest(".grupo-documento");
        if (grupo) {
            grupo.remove();
        }
    }
    </script>

    <script>
        // Limitar la fecha máxima a mayores de 18
        window.onload = function () {
            const hoy = new Date();
            const anio = hoy.getFullYear() - 18;
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const dia = String(hoy.getDate()).padStart(2, '0');
            const fechaMax = `${anio}-${mes}-${dia}`;
            document.getElementById("fecha_nacimiento").max = fechaMax;
        };

        // Calcular edad automáticamente al seleccionar fecha
        function calcularEdad() {
            const inputNacimiento = document.getElementById("fecha_nacimiento").value;
            const campoEdad = document.getElementById("edad");

            if (!inputNacimiento) {
                campoEdad.value = '';
                return;
            }

            const nacimiento = new Date(inputNacimiento);
            const hoy = new Date();
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();

            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }

            campoEdad.value = edad + " años";
        }
    </script>

    <script>
        document.getElementById("correo").addEventListener("blur", function (e) {
            const correo = e.target;
            const mensaje = document.getElementById("correo_error");
            const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!regexCorreo.test(correo.value)) {
                mensaje.style.display = "block";
                correo.focus(); // Mantiene el foco si es inválido
            } else {
                mensaje.style.display = "none";
            }
        });

        // Para ocultar el mensaje mientras escribe
        document.getElementById("correo").addEventListener("input", function () {
            const mensaje = document.getElementById("correo_error");
            mensaje.style.display = "none";
        });
    </script>

    <!-- Script para abrir automáticamente el modal según resultado -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        <?php if ($mostrarModalExito): ?>
            var modalExito = new bootstrap.Modal(document.getElementById('modalExito'), {backdrop: true, keyboard: true});
            modalExito.show();
        <?php elseif ($mostrarModalError): ?>
            var modalError = new bootstrap.Modal(document.getElementById('modalError'), {backdrop: true, keyboard: true});
            modalError.show();
        <?php endif; ?>
        });
    </script>

</body>
</html>