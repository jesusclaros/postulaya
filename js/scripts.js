function calcularEdad() {
    const fechaNac = document.getElementById("fecha_nacimiento").value;
    if (!fechaNac) return;

    const hoy = new Date();
    const nacimiento = new Date(fechaNac);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const m = hoy.getMonth() - nacimiento.getMonth();

    if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }

    document.getElementById("edad").value = edad;
}