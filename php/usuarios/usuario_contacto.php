<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto Acuario</title>
    <link rel="stylesheet" href="../../css/contacto_acuario.css">
    <script defer src="../../js/menu.js"></script>
    <script>
        // Validar la sesión al cargar la página
        checkSession();
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../../assets/logo.ico" alt="Logo AcuSystem">
            <span>AcuSystem</span>
        </div>
        <nav>
            <ul class="menu">
                <li><a href="usuario_reservar.php">Reservar</a></li>
                <li><a href="usuario_restaurante.php">Restaurante</a></li>
                <li><a href="usuario_resumen.php">Resumen</a></li>
                <li><a href="usuario_contacto.php">Contacto</a></li>
                <li><a href="../../includes/logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="container">
            <div class="image-box">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63814.606063645544!2d-78.0627246!3d-1.5208283!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91d3dd70ec2eeca7%3A0x5eaeb5367d8d6263!2sAcuario%20Finca%20Sarahi!5e0!3m2!1ses-419!2sec!4v1736168879103!5m2!1ses-419!2sec" 
                    width="100%" height="100%" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="form-box">
                <h1>Contacto <span class="highlight">ACUARIO</span></h1>
                <div class="contact-group">
                    <p><strong>Correo Electrónico:</strong> 
                        <a href="mailto:fincasarahi@gmail.com" class="email-link">fincasarahi@gmail.com</a>
                    </p>
                    <p><strong>WhatsApp:</strong> 
                        <a href="https://wa.me/593967110610" target="_blank" class="whatsapp-link">096 711 0610</a>
                    </p>
                </div>
                <div class="social-links">
                    <h2>Redes Sociales</h2>
                    <a href="https://www.facebook.com/fincasarahi" target="_blank">
                        <img src="../../assets/facebook.png" alt="Facebook" class="social-icon"> Facebook
                    </a>
                    <a href="https://www.instagram.com/fincasarahi/" target="_blank">
                        <img src="../../assets/instagram.png" alt="Instagram" class="social-icon"> Instagram
                    </a>
                    <a href="https://www.tiktok.com/@finca.sarahi" target="_blank">
                        <img src="../../assets/tiktok.png" alt="TikTok" class="social-icon"> TikTok
                    </a>
                </div>
            </div>
        </div>
    </main>
    <script src="../../js/auth.js"></script>
</body>
</html>
