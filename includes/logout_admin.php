<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Verificar si se accede correctamente al archivo
echo "Archivo logout_admin.php ejecutado.";
session_unset();
session_destroy();
header("Location: ../php/admin/admin_login.php");
exit;
?>
