<?php
// Iniciar la sesión
session_start();
// Requerir el archivo de conexión a la base de datos
require_once '../conexion/conexionbd.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $contrasena = $_POST['contrasena'];

    try {
        $stmt = $conn->prepare("SELECT id, contrasena, es_superusuario FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($contrasena, $user['contrasena'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $usuario;
            $_SESSION['es_superusuario'] = $user['es_superusuario'];

            if ($user['es_superusuario'] == 1) {
                header("Location: usuarios.php");
                exit();
            } else {
                header("Location: inicio.php");
                exit();
            }
        } else {
            $message = "Usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #e0f7fa; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }
        .login-container { 
            background-color: #ffffff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.15); 
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
        }
        .logo-container {
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 150px; /* tamaño ajustable */
        }
        h1 { 
            color: #007bff; 
            margin-bottom: 20px; 
        }
        .form-group { 
            margin-bottom: 20px; 
            text-align: left; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #bdc3c7; 
            border-radius: 6px; 
            box-sizing: border-box; 
        }
        .btn-azul { 
            width: 100%; 
            padding: 12px; 
            background-color: #007bff; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold; 
            transition: background-color 0.3s; 
        }
        .btn-azul:hover { 
            background-color: #2980b9; 
        }
        .message { 
            margin-top: 15px; 
            color: #dc3545; 
            font-weight: bold; 
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo arriba -->
        <div class="logo-container">
            <img src="../img/Ophthalmos (1).png" alt="Logo Ophthalmos">
        </div>

        <h1>Iniciar Sesión</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            <button type="submit" class="btn-azul">Ingresar</button>
        </form>
    </div>
</body>
</html>
