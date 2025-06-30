
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    genero ENUM('Hombre', 'Mujer') NOT NULL,
    primer_nombre VARCHAR(15) NOT NULL,
    segundo_nombre VARCHAR(15),
    primer_apellido VARCHAR(15) NOT NULL,
    segundo_apellido VARCHAR(15),
    tipo_documento ENUM('cedula', 'pasaporte') NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    edad INT NOT NULL,
    codigo_pais VARCHAR(5) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    provincia VARCHAR(50) NOT NULL,
    corregimiento VARCHAR(50) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    estado_civil VARCHAR(50) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);