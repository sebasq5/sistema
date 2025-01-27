<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../../css/registrar.css">
</head>
<body>
    <div class="register-container">
        <!-- Sección de la Imagen -->
        <div class="image-box">
            <img src="../../assets/logo.ico" alt="Logo">
        </div>

        <!-- Sección del Formulario -->
        <div class="register-box">
            <h1>Crea una cuenta</h1>
            <p>Bienvenido a nuestra página. <a href="cliente.php">Inicia sesión</a></p>
            <form method="POST" action="../../includes/register_handler.php">
                <div class="input-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="input-group">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" required>
                </div>
                <div class="input-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" pattern="\d{10}" required>
                </div>
                <div class="input-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" pattern="\d{10}" required>
                </div>
                <div class="input-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" required>
                </div>
                <button type="submit" class="btn">Registrar</button>
            </form>
        </div>
    </div>
</body>
</html>
