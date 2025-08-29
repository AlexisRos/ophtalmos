<?php
require_once 'administracion/config_session.php';

require_once 'conexion/conexionbd.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$ficha = null;
$id_mostrado = ''; // Variable para almacenar el ID que se mostrará
$proximo_id = ''; // Variable para almacenar el próximo ID


// Verifica si el usuario ha iniciado sesión. Si no, lo redirige a la página de login.
if (!isset($_SESSION['user_id'])) {
    header('Location: administracion/login.php');
    exit();
}
// Escapa el nombre de usuario para prevenir ataques de Cross-Site Scripting (XSS)
$username = htmlspecialchars($_SESSION['username']);

// Obtener el próximo ID disponible buscando el máximo en la tabla
try {
    $stmt = $conn->prepare("SELECT MAX(id) FROM ficha");
    $stmt->execute();
    $ultimo_id = $stmt->fetchColumn();
    // Si la tabla está vacía, MAX(id) devuelve NULL, en ese caso el próximo ID es 1
    $proximo_id = ($ultimo_id === null) ? 1 : $ultimo_id + 1;
} catch (PDOException $e) {
    // Manejo de error si la consulta falla
    $proximo_id = 'N/A';
}

// Lógica para cargar ficha cuando la acción es 'editar' o 'buscar'
if (($action === 'editar' || $action === 'buscar') && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM ficha WHERE id = ?");
        $stmt->execute([$id]);
        $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ficha) {
            $message = "Error: No se encontró la ficha con el ID proporcionado.";
        }
        $id_mostrado = $id;
    } catch (PDOException $e) {
        $message = "Error al buscar/editar ficha: " . $e->getMessage();
    }
}

switch($action) {
    case 'guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO ficha (
                        fecha, nombre, edad, telefono, ocupacion, problemas_salud, medicamentos, ultimo_examen, ant_rx, 
                        od_esf, od_cyl, od_eje, od_av, od_add, od_av2, 
                        oi_esf, oi_cyl, oi_eje, oi_av, oi_add, oi_av2, 
                        tipo_lente, biomicroscopia, oftalmoscopia, proxima_cita, referido, observaciones
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['fecha'],
                    $_POST['nombre'],
                    $_POST['edad'],
                    $_POST['telefono'],
                    $_POST['ocupacion'],
                    $_POST['problemas_salud'],
                    $_POST['medicamentos'],
                    $_POST['ultimo_examen'],
                    $_POST['ant_rx'],
                    $_POST['od_esf'],
                    $_POST['od_cyl'],
                    $_POST['od_eje'],
                    $_POST['od_av'],
                    $_POST['od_add'],
                    $_POST['od_av2'],
                    $_POST['oi_esf'],
                    $_POST['oi_cyl'],
                    $_POST['oi_eje'],
                    $_POST['oi_av'],
                    $_POST['oi_add'],
                    $_POST['oi_av2'],
                    $_POST['tipo_lente'],
                    $_POST['biomicroscopia'],
                    $_POST['oftalmoscopia'],
                    $_POST['proxima_cita'],
                    $_POST['referido'],
                    $_POST['observaciones']
                ]);
                $message = "Datos guardados correctamente.";
                $id_mostrado = $conn->lastInsertId();
            } catch (PDOException $e) {
                $message = "Error al guardar: " . $e->getMessage();
            }
        }
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            try {
                $stmt = $conn->prepare("
                    UPDATE ficha SET 
                        fecha = ?, nombre = ?, edad = ?, telefono = ?, ocupacion = ?, problemas_salud = ?, 
                        medicamentos = ?, ultimo_examen = ?, ant_rx = ?, od_esf = ?, od_cyl = ?, 
                        od_eje = ?, od_av = ?, od_add = ?, od_av2 = ?, oi_esf = ?, oi_cyl = ?, 
                        oi_eje = ?, oi_av = ?, oi_add = ?, oi_av2 = ?, tipo_lente = ?, 
                        biomicroscopia = ?, oftalmoscopia = ?, proxima_cita = ?, referido = ?, 
                        observaciones = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['fecha'],
                    $_POST['nombre'],
                    $_POST['edad'],
                    $_POST['telefono'],
                    $_POST['ocupacion'],
                    $_POST['problemas_salud'],
                    $_POST['medicamentos'],
                    $_POST['ultimo_examen'],
                    $_POST['ant_rx'],
                    $_POST['od_esf'],
                    $_POST['od_cyl'],
                    $_POST['od_eje'],
                    $_POST['od_av'],
                    $_POST['od_add'],
                    $_POST['od_av2'],
                    $_POST['oi_esf'],
                    $_POST['oi_cyl'],
                    $_POST['oi_eje'],
                    $_POST['oi_av'],
                    $_POST['oi_add'],
                    $_POST['oi_av2'],
                    $_POST['tipo_lente'],
                    $_POST['biomicroscopia'],
                    $_POST['oftalmoscopia'],
                    $_POST['proxima_cita'],
                    $_POST['referido'],
                    $_POST['observaciones'],
                    $_POST['id']
                ]);
                $message = "Datos actualizados correctamente.";
                $id_mostrado = $_POST['id'];
            } catch (PDOException $e) {
                $message = "Error al actualizar: " . $e->getMessage();
            }
        }
        break;

    case 'eliminar':
        if (isset($_POST['id'])) {
            try {
                $id_eliminado = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM ficha WHERE id = ?");
                $stmt->execute([$id_eliminado]);
                $message = "Registro eliminado correctamente.";
                $id_mostrado = '';
            } catch (PDOException $e) {
                $message = "Error al eliminar: " . $e->getMessage();
            }
        }
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Oftalmológico</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/estilos2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .bottom-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-azul {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-azul:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-azul:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para posicionar el ID */
        .container {
            position: relative;
        }

        .id-display {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 1.2em;
            font-weight: bold;
            color: #007bff;
            background-color: #e9f5ff;
            padding: 8px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 10;
        }
                .header {
            display: flex;
            align-items: center;
            justify-content: space-between; /* Alinea los elementos a los extremos */
            gap: 15px;
            padding: 10px 20px;
            border-bottom: 1px solid #ccc;
        }

        /* Estilos para el nuevo menú de navegación */
        .main-nav {
            display: flex;
            gap: 20px;
        }
        
        .main-nav a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s;
        }

        .main-nav a:hover {
            color: #0056b3;
        }

        .id-display span {
            font-size: 1.1em;
            color: #333;
        }

        .formulario {
            padding-top: 60px;
        }
            /* ... otros estilos ... */
    
    .form-group textarea#observaciones {
        font-family: 'Courier New', Courier, monospace; /* Cambia a una fuente de tipo monoespacio */
        font-size: 14px; /* Ajusta el tamaño de la fuente */
        color: #333; /* Color del texto */
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f9f9f9;
        /* Puedes agregar más estilos aquí, como 'line-height' */
        line-height: 1.5;
    }z

    </style>
</head>
<body>
    <header class="header">
        <img src="img/Ophthalmos (1).png" alt="Logo">
        <h1>Formulario Oftalmológico</h1>
                <!-- Menú de navegación agregado -->
        <nav class="main-nav">
            <a href="formulario1.php">Formulario 1</a>
            <a href="formulario2.php">Formulario 2</a>
            <a href="administracion/inicio.php">Regresar a Inicio</a>
            <a href="administracion/logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <div class="container">
        <?php 
        // Si hay un ID de un registro existente, lo mostramos
        if ($id_mostrado): 
        ?>
            <div class="id-display">
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
            justify-content: flex-start;
            position: relative;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .header-left img {
            height: 100px; /* Logo más grande */
        }

        .header-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .header-center h1 {
            font-size: 2.5rem;
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
            max-width: 1500px;
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

        h2 {
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
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
            white-space: nowrap;
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
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <img src="../img/Ophthalmos (1).png" alt="Logo">
        </div>
        <div class="header-center">
            <h1>Gestión de Usuarios</h1>
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
 <span><?= htmlspecialchars($id_mostrado) ?></span>
            </div>
        <?php 
        // Si no, mostramos el próximo ID disponible
        else: 
        ?>
            <div class="id-display">
                 Formulario No: <span><?= htmlspecialchars($proximo_id) ?></span>
            </div>
        <?php endif; ?>

        <form class="formulario" method="POST" action="formulario2.php?action=<?= $ficha ? 'actualizar' : 'guardar' ?>" id="mainForm">
            <?php if ($message): ?>
                <div class="message <?= strpos($message, 'Error') === false ? 'success' : 'error' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if ($ficha): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($ficha['id']) ?>">
            <?php endif; ?>

            <div class="section-title">Datos Generales</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($ficha['fecha'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($ficha['nombre'] ?? '') ?>" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="edad">Edad</label>
                    <input type="number" id="edad" name="edad" value="<?= htmlspecialchars($ficha['edad'] ?? '') ?>" min="0" max="99" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($ficha['telefono'] ?? '') ?>" maxlength="20" required>
                </div>
                <div class="form-group">
                    <label for="ocupacion">Ocupación</label>
                    <input type="text" id="ocupacion" name="ocupacion" value="<?= htmlspecialchars($ficha['ocupacion'] ?? '') ?>" maxlength="25" required>
                </div>
                <div class="form-group">
                    <label for="problemas_salud">Ant. y problemas de salud</label>
                    <input type="text" id="problemas_salud" name="problemas_salud" value="<?= htmlspecialchars($ficha['problemas_salud'] ?? '') ?>" maxlength="50" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="medicamentos">Medicamentos</label>
                    <input type="text" id="medicamentos" name="medicamentos" value="<?= htmlspecialchars($ficha['medicamentos'] ?? '') ?>" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="ultimo_examen">Último examen de la vista</label>
                    <input type="date" id="ultimo_examen" name="ultimo_examen" value="<?= htmlspecialchars($ficha['ultimo_examen'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="ant_rx">Ant. Rx</label>
                    <input type="text" id="ant_rx" name="ant_rx" value="<?= htmlspecialchars($ficha['ant_rx'] ?? '') ?>" maxlength="50" required>
                </div>
            </div>
            
            <div class="section-title">Ojo Derecho (OD)</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="od_esf">ESF</label>
                    <input type="text" id="od_esf" name="od_esf" value="<?= htmlspecialchars($ficha['od_esf'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="od_cyl">CYL</label>
                    <input type="text" id="od_cyl" name="od_cyl" value="<?= htmlspecialchars($ficha['od_cyl'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="od_eje">EJE</label>
                    <input type="text" id="od_eje" name="od_eje" value="<?= htmlspecialchars($ficha['od_eje'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="od_av">A.V.</label>
                    <input type="text" id="od_av" name="od_av" value="<?= htmlspecialchars($ficha['od_av'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="od_add">ADD</label>
                    <input type="text" id="od_add" name="od_add" value="<?= htmlspecialchars($ficha['od_add'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="od_av2">A.V. (2)</label>
                    <input type="text" id="od_av2" name="od_av2" value="<?= htmlspecialchars($ficha['od_av2'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="section-title">Ojo Izquierdo (OI)</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="oi_esf">ESF</label>
                    <input type="text" id="oi_esf" name="oi_esf" value="<?= htmlspecialchars($ficha['oi_esf'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="oi_cyl">CYL</label>
                    <input type="text" id="oi_cyl" name="oi_cyl" value="<?= htmlspecialchars($ficha['oi_cyl'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="oi_eje">EJE</label>
                    <input type="text" id="oi_eje" name="oi_eje" value="<?= htmlspecialchars($ficha['oi_eje'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="oi_av">A.V.</label>
                    <input type="text" id="oi_av" name="oi_av" value="<?= htmlspecialchars($ficha['oi_av'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="oi_add">ADD</label>
                    <input type="text" id="oi_add" name="oi_add" value="<?= htmlspecialchars($ficha['oi_add'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="oi_av2">A.V. (2)</label>
                    <input type="text" id="oi_av2" name="oi_av2" value="<?= htmlspecialchars($ficha['oi_av2'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="section-title">Otros Datos</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_lente">Tipo de lente</label>
                    <input type="text" id="tipo_lente" name="tipo_lente" value="<?= htmlspecialchars($ficha['tipo_lente'] ?? '') ?>" maxlength="25" required>
                </div>
                <div class="form-group">
                    <label for="biomicroscopia">Biomicroscopia</label>
                    <input type="text" id="biomicroscopia" name="biomicroscopia" value="<?= htmlspecialchars($ficha['biomicroscopia'] ?? '') ?>" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="oftalmoscopia">Oftalmoscopia</label>
                    <input type="text" id="oftalmoscopia" name="oftalmoscopia" value="<?= htmlspecialchars($ficha['oftalmoscopia'] ?? '') ?>" maxlength="50" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="proxima_cita">Próxima cita</label>
                    <input type="date" id="proxima_cita" name="proxima_cita" value="<?= htmlspecialchars($ficha['proxima_cita'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="referido">Referido</label>
                    <input type="text" id="referido" name="referido" value="<?= htmlspecialchars($ficha['referido'] ?? '') ?>" maxlength="50" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" maxlength="100" rows="4" required style="resize: none;"><?= htmlspecialchars($ficha['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="bottom-actions">
                <button type="submit" class="btn-azul">Guardar / Actualizar</button>
                <button type="button" id="btn-buscar" class="btn-azul">Buscar</button>
                <button type="button" id="btn-editar" class="btn-azul">Editar</button>
                <button type="button" id="btn-eliminar" class="btn-azul">Eliminar</button>
            </div>
        </form>
    </div>
    
    <footer class="footer">
        <p>© 2025 Ophthalmos. Todos los derechos reservados.</p>
        <p>| <a href="https://www.bitscreativos.com" target="_blank">Desarrollado por BitsCreativos</a> |</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Maneja el botón de Buscar
            document.getElementById('btn-buscar').addEventListener('click', function() {
                const id = prompt('Por favor, ingresa el ID de la ficha a buscar:');
                if (id) {
                    window.location.href = `formulario2.php?action=buscar&id=${encodeURIComponent(id)}`;
                }
            });

            // Maneja el botón de Editar (carga el formulario para edición)
            document.getElementById('btn-editar').addEventListener('click', function() {
                const id = prompt('Por favor, ingresa el ID de la ficha a editar:');
                if (id) {
                    window.location.href = `formulario2.php?action=editar&id=${encodeURIComponent(id)}`;
                }
            });

            // Maneja el botón de Eliminar
            document.getElementById('btn-eliminar').addEventListener('click', function() {
                const id = prompt('Por favor, ingresa el ID de la ficha a eliminar:');
                if (id) {
                    if (confirm(`¿Estás seguro de que quieres eliminar la ficha con ID ${id}?`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'formulario2.php?action=eliminar';
                        
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        idInput.value = id;
                        
                        form.appendChild(idInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            });
        });
    </script>
</body>
</html>