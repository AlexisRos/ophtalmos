<?php
// Incluye tu archivo de conexión de la base de datos
require_once '../conexion/conexionbd.php';

$message = '';
$hay_superusuario = false;
$edit_user = null;

// 1. Verificar si ya existe al menos un superusuario
try {
    $stmt_check = $conn->query("SELECT COUNT(*) FROM usuarios WHERE es_superusuario = 1");
    if ($stmt_check->fetchColumn() > 0) {
        $hay_superusuario = true;
    }
} catch (PDOException $e) {
    $message = "Error al verificar superusuarios: " . $e->getMessage();
}

// 2. Lógica para manejar el registro, edición y eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'registrar':
            $usuario = trim($_POST['usuario']);
            $contrasena = $_POST['contrasena'];
            $correo = trim($_POST['correo']);
            $telefono = trim($_POST['telefono']);
            
            if (!empty($telefono) && (!preg_match('/^[0-9]+$/', $telefono) || strlen($telefono) > 15)) {
                $message = "Error: El teléfono debe contener solo números y no exceder los 15 dígitos.";
            } else {
                if ($hay_superusuario) {
                    $es_superusuario = 0;
                } else {
                    $es_superusuario = isset($_POST['es_superusuario']) ? 1 : 0;
                }

                $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
                if ($hash_contrasena === false) {
                    $message = "Error al hashear la contraseña.";
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasena, correo, telefono, es_superusuario) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$usuario, $hash_contrasena, $correo, $telefono, $es_superusuario]);
                        $message = "Usuario registrado exitosamente.";
                    } catch (PDOException $e) {
                        $message = "Error: " . $e->getMessage();
                    }
                }
            }
            break;

        case 'actualizar':
            $id = $_POST['id'];
            $usuario = trim($_POST['usuario']);
            $correo = trim($_POST['correo']);
            $telefono = trim($_POST['telefono']);
            $es_superusuario = isset($_POST['es_superusuario']) ? 1 : 0;

            if (!empty($telefono) && (!preg_match('/^[0-9]+$/', $telefono) || strlen($telefono) > 15)) {
                $message = "Error: El teléfono debe contener solo números y no exceder los 15 dígitos.";
            } else {
                try {
                    $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, correo = ?, telefono = ?, es_superusuario = ? WHERE id = ?");
                    $stmt->execute([$usuario, $correo, $telefono, $es_superusuario, $id]);
                    $message = "Usuario actualizado exitosamente.";
                } catch (PDOException $e) {
                    $message = "Error: " . $e->getMessage();
                }
            }
            break;

        case 'eliminar':
            $id = $_POST['id'];
            try {
                $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Usuario eliminado exitosamente.";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
            }
            break;
    }
}

// 3. Lógica para cargar un usuario para edición
if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
    try {
        $stmt_edit = $conn->prepare("SELECT id, usuario, correo, telefono, es_superusuario FROM usuarios WHERE id = ?");
        $stmt_edit->execute([$_GET['edit_id']]);
        $edit_user = $stmt_edit->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error al cargar el usuario para edición: " . $e->getMessage();
    }
}

// 4. Lógica para cargar todos los usuarios para la tabla
try {
    $stmt = $conn->query("SELECT id, usuario, correo, telefono, es_superusuario FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuarios = [];
    $message = "Error al cargar usuarios: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #ecf0f1;
            --background-color: #e0f7fa;
            --card-bg-color: #ffffff;
            --text-color: #2c3e50;
            --border-color: #bdc3c7;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

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
            justify-content: space-between; /* Alinear elementos al inicio y al final */
        }
        
        header img {
            height: 50px;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        header h1 {
            font-size: 2rem;
            margin: 0;
            color: var(--primary-color);
        }
        
        .main-container {
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }

        .content-card {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            padding: 30px;
            width: 100%;
            max-width: 1500px; /* Tamaño del cuadro aumentado */
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 40px;
        }

        @media (max-width: 900px) {
            .content-card {
                flex-direction: column;
            }
        }

        h1, h2 {
            text-align: left;
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .form-section {
            flex: 1 1 45%;
            min-width: 300px;
        }

        .user-list-section {
            flex: 1 1 50%;
            min-width: 300px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group input[type="email"],
        .form-group input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
            outline: none;
        }

        .btn-azul {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-azul:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
        }
        
        thead th {
            text-align: left;
            color: var(--text-color);
            padding: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        tbody tr {
            background-color: #f9f9f9;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        td {
            padding: 15px;
            vertical-align: middle;
            border-top: 1px solid var(--border-color);
        }
        
        /* Contenedor flexible para los botones de acción */
        .action-buttons {
            display: flex;
            gap: 5px; /* Pequeño espacio entre los botones */
        }
        
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
            white-space: nowrap; /* Evita que el texto se rompa en varias líneas */
        }
        
        .btn-edit {
            background-color: var(--success-color);
            color: white;
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }
        
        .message-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 400px;
            z-index: 1000;
        }
        
        .message {
            padding: 10px;
            font-size: 0.9em;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 10px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .message.error {
            background-color: #ffebee;
            color: #c0392b;
            border: 1px solid #c0392b;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .btn-back {
            background-color: #34495e;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #2c3e50;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <div class="header-logo">
                <img src="../img/Ophthalmos (1).png" alt="Logo">
            </div>
            <h1>Gestión de Usuarios</h1>
        </div>
        <div>
            <a href="login.php" class="btn-back">Inicio de Sesión</a>
        </div>
    </header>

    <div class="message-container">
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>

    <div class="main-container">
        <div class="content-card">
            <div class="form-section">
                <h2><?= $edit_user ? 'Editar Usuario' : 'Registrar Nuevo Usuario' ?></h2>
                <form action="usuarios.php" method="POST">
                    <input type="hidden" name="action" value="<?= $edit_user ? 'actualizar' : 'registrar' ?>">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_user['id']) ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="usuario">Usuario:</label>
                        <input type="text" id="usuario" name="usuario" value="<?= $edit_user ? htmlspecialchars($edit_user['usuario']) : '' ?>" required>
                    </div>
                    <?php if (!$edit_user): ?>
                    <div class="form-group">
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" value="<?= $edit_user ? htmlspecialchars($edit_user['correo']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="tel" id="telefono" name="telefono" value="<?= $edit_user ? htmlspecialchars($edit_user['telefono']) : '' ?>">
                    </div>
                    <!-- El checkbox de superusuario se muestra solo si NO hay_superusuario o si se está editando a un superusuario existente -->
                    <?php if (!$hay_superusuario || ($edit_user && $edit_user['es_superusuario'])): ?>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="es_superusuario" value="1" <?= ($edit_user && $edit_user['es_superusuario']) ? 'checked' : '' ?>>
                            Superusuario
                        </label>
                    </div>
                    <?php endif; ?>
                    <button type="submit" class="btn-azul"><?= $edit_user ? 'Actualizar Usuario' : 'Registrar Usuario' ?></button>
                    <?php if ($edit_user): ?>
                    <a href="usuarios.php" class="btn-azul" style="display: block; text-align: center; margin-top: 10px;">Cancelar Edición</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="user-list-section">
                <h2>Lista de Usuarios</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Superusuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['usuario']) ?></td>
                            <td><?= htmlspecialchars($user['correo']) ?></td>
                            <td><?= htmlspecialchars($user['telefono']) ?></td>
                            <td><?= $user['es_superusuario'] ? 'Sí' : 'No' ?></td>
                            <td>
                                <!-- Contenedor para los botones de acción para que no se salgan del espacio -->
                                <div class="action-buttons">
                                    <a href="?edit_id=<?= htmlspecialchars($user['id']) ?>" class="btn-action btn-edit">Editar</a>
                                    <form action="usuarios.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
                                        <button type="submit" class="btn-action btn-delete" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
