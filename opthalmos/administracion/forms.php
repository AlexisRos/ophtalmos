<?php
session_start();

// Si el usuario no está loggeado, redirigir a login.php
// Asumiendo que forms.php está en la carpeta 'administracion',
// el enlace a login.php debe ser relativo.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Formularios</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-color: #007bff;
            --background-color: #e0f7fa;
            --card-bg-color: #ffffff;
            --text-color: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .navbar {
            background-color: var(--card-bg-color);
            padding: 15px 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .navbar a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            margin-left: 20px;
            transition: color 0.3s;
        }

        .navbar a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .card {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            text-align: center;
            margin-top: 20px;
        }

        .card h2 {
            color: var(--primary-color);
            font-size: 2em;
            margin-bottom: 20px;
        }

        .button-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
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

        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-links">
        <a href="../inicio.php">Inicio</a>
        <a href="forms.php">Formularios</a>
    </div>
    <div class="user-info">
        <span>Hola, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
        <a href="logout.php">Cerrar Sesión</a>
    </div>
</div>

<div class="main-container">
    <div class="card">
        <h2>Selecciona un formulario</h2>
        <div class="button-container">
            <a href="../formulario1.php" class="btn">Formulario 1</a>
            <a href="../formulario2.php" class="btn">Formulario 2</a>
        </div>
    </div>
</div>

</body>
</html>
