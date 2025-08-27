<?php
require_once 'config_session.php';

if (!isset($_SESSION['user_id'])) {
    // Si la sesión no está iniciada, redirige a la página de inicio de sesión
    header('Location: login.php');
    exit();
}

// Escapa el nombre de usuario para prevenir ataques de Cross-Site Scripting (XSS)
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Inicio</title>
    <!-- Enlaza a tu hoja de estilos CSS -->
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        /* Variables CSS para una fácil personalización del tema */
        :root {
            --primary-color: #007bff;
            --background-color: #e0f7fa;
            --card-bg-color: #ffffff;
            --text-color: #2c3e50;
        }

        /* Estilos generales del cuerpo */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Estilos de la cabecera */
        header {
            background-color: var(--card-bg-color);
            padding: 15px 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            color: var(--text-color);
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            justify-content: space-between; /* Alinea los elementos a los extremos */
        }
        
        /* Estilos para el logo en la cabecera */
        header img {
            height: 50px;
        }

        /* Estilos para el título de la página */
        header h1 {
            font-size: 2rem;
            margin: 0;
            text-align: center;
            flex-grow: 1;
            color: var(--primary-color);
        }

        /* Contenedor principal para el contenido */
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* Estilos para la tarjeta de bienvenida */
        .welcome-card {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        /* Estilos para los títulos dentro de la tarjeta */
        .welcome-card h2 {
            color: var(--primary-color);
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        /* Estilos para el texto de bienvenida */
        .welcome-card p {
            font-size: 1.2em;
            color: var(--text-color);
            margin-top: 0;
        }

        /* Contenedor para los botones */
        .button-container {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Estilos generales para los botones */
        .btn {
            padding: 15px 30px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Efecto al pasar el cursor sobre los botones */
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        /* Estilos para el enlace de cerrar sesión */
        .logout-link {
            font-size: 1em;
            font-weight: 600;
            color: #d32f2f; /* Un color distintivo para cerrar sesión */
            text-decoration: none;
            transition: color 0.3s;
        }

        .logout-link:hover {
            color: #b71c1c;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-logo">
            <img src="../img/Ophthalmos (1).png" alt="Logo">
        </div>
        <h1>Página de Inicio</h1>
        <!-- Enlace para cerrar sesión añadido aquí -->
        <a href="logout.php" class="logout-link">Cerrar Sesión</a>
    </header>

    <div class="main-container">
        <div class="welcome-card">
            <h2>¡Bienvenido, <?= $username ?>!</h2>
            <p>¿Qué desea hacer hoy?</p>
            <div class="button-container">
                <!-- Rutas actualizadas para redirigir a los archivos en la carpeta principal del proyecto -->
                <a href="../formulario1.php" class="btn">Formulario 1</a>
                <a href="../formulario2.php" class="btn">Formulario 2</a>
            </div>
        </div>
    </div>
</body>
</html>
