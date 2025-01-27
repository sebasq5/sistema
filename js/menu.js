// Obtén la URL de la página actual
const currentPage = window.location.pathname;

// Selecciona todos los enlaces del menú
const menuLinks = document.querySelectorAll(".menu a");

// Itera sobre los enlaces del menú
menuLinks.forEach(link => {
    // Verifica si el enlace coincide con la página actual
    if (link.href.includes(currentPage)) {
        link.classList.add("active");
    } else {
        link.classList.remove("active");
    }
});
