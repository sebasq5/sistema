function openOrderForm(plato) {
    document.getElementById("plato").value = plato;
    document.getElementById("order-modal").style.display = "flex"; // Cambiado de block a flex para centrar
}

function closeOrderForm() {
    document.getElementById("order-modal").style.display = "none";
}
