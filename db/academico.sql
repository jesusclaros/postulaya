CREATE TABLE IF NOT EXISTS academico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel VARCHAR(100) NOT NULL
);

INSERT INTO academico (nivel) VALUES
('Doctorado'),
('Maestría'),
('Postgrado'),
('Ingeniería/Licenciatura'),
('Técnico'),
('Certificado (Escolar)');