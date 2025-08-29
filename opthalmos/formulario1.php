<?php

require_once 'administracion/config_session.php';
// Incluir el archivo de conexión a la base de datos
require_once 'conexion/conexionbd.php';

// Definir variables y valores predeterminados
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$paciente = null;
$id_mostrado = '';
$proximo_id = '';


// Verifica si el usuario ha iniciado sesión. Si no, lo redirige a la página de login.
if (!isset($_SESSION['user_id'])) {
    header('Location: administracion/login.php');
    exit();
}

// Escapa el nombre de usuario para prevenir ataques de Cross-Site Scripting (XSS)
$username = htmlspecialchars($_SESSION['username']);

// Obtener el próximo ID disponible (usando la función MAX para evitar errores con IDs eliminados)
try {
    $stmt = $conn->prepare("SELECT MAX(id) FROM paciente");
    $stmt->execute();
    $ultimo_id = $stmt->fetchColumn();
    $proximo_id = ($ultimo_id === null) ? 1 : $ultimo_id + 1;
} catch (PDOException $e) {
    $proximo_id = 'N/A';
}

// Lógica para cargar paciente (para editar o buscar)
if (($action === 'editar' || $action === 'buscar') && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM paciente WHERE id = ?");
        $stmt->execute([$id]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$paciente) {
            $message = "Error: No se encontró la ficha con el ID proporcionado.";
        }
        $id_mostrado = $id;
    } catch (PDOException $e) {
        $message = "Error al buscar/editar ficha: " . $e->getMessage();
    }
}

// Lógica para las acciones del formulario (guardar, actualizar, eliminar)
switch($action) {
    case 'guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $fotoPath = '';
                // Obtener el proximo ID ANTES de insertar para nombrar la foto
                $stmt = $conn->prepare("SELECT MAX(id) FROM paciente");
                $stmt->execute();
                $lastId = $stmt->fetchColumn();
                $newId = ($lastId === null) ? 1 : $lastId + 1;

                // Procesar la foto si se ha capturado
                if (isset($_POST['foto_base64']) && !empty($_POST['foto_base64'])) {
                    $uploadDir = 'fotos/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Usamos el $newId para nombrar el archivo como "id_5.jpg"
                    $fotoPath = $uploadDir . 'id_' . $newId . '.jpg';
                    
                    $imagenBase64 = $_POST['foto_base64'];
                    $imagenBase64 = preg_replace('/^data:image\/(jpeg|png);base64,/', '', $imagenBase64);
                    $imagenBase64 = str_replace(' ', '+', $imagenBase64);
                    $imagenDecodificada = base64_decode($imagenBase64);
                    
                    if ($imagenDecodificada !== false) {
                        file_put_contents($fotoPath, $imagenDecodificada);
                    }
                }
                
                // Preparar la consulta SQL para la inserción
                $stmt = $conn->prepare("
                    INSERT INTO paciente (
                        fecha, nombre, dpi, muniydepto, fechanacimiento, sexo, 
                        direccion, od, oi, ou, visioncolores, campovisual, campovisual2,
                        contraste, pruebae1, pruebae2, contralod, centraloi, 
                        usaanteojos, usalentescontacto, perifericood, perifericooi, 
                        tipolicencia, foto
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $usaanteojos_db = $_POST['usaanteojos'] === 'si' ? 1 : 0;
                $usalentescontacto_db = $_POST['usalentescontacto'] === 'si' ? 1 : 0;

                // Ejecutar la inserción con los datos del formulario
                $stmt->execute([
                    $_POST['fecha'],
                    $_POST['nombre'],
                    $_POST['dpi'],
                    $_POST['muniydepto'],
                    $_POST['fechanacimiento'],
                    $_POST['sexo'],
                    $_POST['direccion'],
                    $_POST['od'],
                    $_POST['oi'],
                    $_POST['ou'],
                    $_POST['visioncolores'],
                    $_POST['campovisual'],
                    $_POST['campovisual2'],
                    $_POST['contraste'],
                    $_POST['pruebae1'],
                    $_POST['pruebae2'],
                    $_POST['contralod'],
                    $_POST['centraloi'],
                    $usaanteojos_db,
                    $usalentescontacto_db,
                    $_POST['perifericood'],
                    $_POST['perifericooi'],
                    $_POST['tipolicencia'],
                    $fotoPath // Se guarda la ruta, no el archivo binario
                ]);
                $message = "Datos guardados correctamente" . (!empty($fotoPath) ? " y foto guardada" : "");
                $id_mostrado = $conn->lastInsertId();
            } catch (PDOException $e) {
                $message = "Error al guardar: " . $e->getMessage();
            }
        }
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            try {
                $id = $_POST['id'];
                
                // Lógica para actualizar la foto si se capturó una nueva
                if (isset($_POST['foto_base64']) && !empty($_POST['foto_base64'])) {
                    $stmt = $conn->prepare("SELECT foto FROM paciente WHERE id = ?");
                    $stmt->execute([$id]);
                    $pacienteActual = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Eliminar la foto anterior para no dejar archivos basura
                    if ($pacienteActual && !empty($pacienteActual['foto']) && file_exists($pacienteActual['foto'])) {
                        unlink($pacienteActual['foto']);
                    }

                    $uploadDir = 'fotos/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fotoPath = $uploadDir . 'id_' . $id . '.jpg';
                    
                    $imagenBase64 = $_POST['foto_base64'];
                    $imagenBase64 = preg_replace('/^data:image\/(jpeg|png);base64,/', '', $imagenBase64);
                    $imagenBase64 = str_replace(' ', '+', $imagenBase64);
                    $imagenDecodificada = base64_decode($imagenBase64);

                    if ($imagenDecodificada !== false) {
                        file_put_contents($fotoPath, $imagenDecodificada);
                    }

                    $updateFoto = ", foto = ?";
                    $fotoParam = $fotoPath;
                } else {
                    $updateFoto = "";
                    $fotoParam = null;
                }
                
                // Preparar la consulta SQL para la actualización
                $sql = "
                    UPDATE paciente SET 
                        fecha = ?, nombre = ?, dpi = ?, muniydepto = ?, 
                        fechanacimiento = ?, sexo = ?, direccion = ?, od = ?, 
                        oi = ?, ou = ?, visioncolores = ?, campovisual = ?, 
                        campovisual2 = ?, contraste = ?, pruebae1 = ?, pruebae2 = ?, 
                        contralod = ?, centraloi = ?, usaanteojos = ?, 
                        usalentescontacto = ?, perifericood = ?, perifericooi = ?, 
                        tipolicencia = ?
                        $updateFoto
                    WHERE id = ?
                ";

                $stmt = $conn->prepare($sql);
                
                $usaanteojos_db = $_POST['usaanteojos'] === 'si' ? 1 : 0;
                $usalentescontacto_db = $_POST['usalentescontacto'] === 'si' ? 1 : 0;
                
                $params = [
                    $_POST['fecha'],
                    $_POST['nombre'],
                    $_POST['dpi'],
                    $_POST['muniydepto'],
                    $_POST['fechanacimiento'],
                    $_POST['sexo'],
                    $_POST['direccion'],
                    $_POST['od'],
                    $_POST['oi'],
                    $_POST['ou'],
                    $_POST['visioncolores'],
                    $_POST['campovisual'],
                    $_POST['campovisual2'],
                    $_POST['contraste'],
                    $_POST['pruebae1'],
                    $_POST['pruebae2'],
                    $_POST['contralod'],
                    $_POST['centraloi'],
                    $usaanteojos_db,
                    $usalentescontacto_db,
                    $_POST['perifericood'],
                    $_POST['perifericooi'],
                    $_POST['tipolicencia']
                ];
                
                if ($fotoParam !== null) {
                    $params[] = $fotoParam;
                }
                $params[] = $id;

                $stmt->execute($params);

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
                
                $stmt = $conn->prepare("SELECT foto FROM paciente WHERE id = ?");
                $stmt->execute([$id_eliminado]);
                $pacienteFoto = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("DELETE FROM paciente WHERE id = ?");
                $stmt->execute([$id_eliminado]);
                $message = "Registro eliminado correctamente.";
                $id_mostrado = '';

                // También eliminamos el archivo de la foto del servidor
                if ($pacienteFoto && !empty($pacienteFoto['foto']) && file_exists($pacienteFoto['foto'])) {
                    unlink($pacienteFoto['foto']);
                    $message .= " y foto eliminada.";
                }

            } catch (PDOException $e) {
                $message = "Error al eliminar: " . $e->getMessage();
            }
        }
        break;
}

// Cargar los datos del paciente después de una operación
if ($id_mostrado && $action != 'eliminar') {
    try {
        $stmt = $conn->prepare("SELECT * FROM paciente WHERE id = ?");
        $stmt->execute([$id_mostrado]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error al recargar datos: " . $e->getMessage();
    }
}

// Obtener la ruta de la foto cargada para mostrarla
$foto_cargada = null;
if ($paciente && !empty($paciente['foto'])) {
    $foto_cargada = $paciente['foto'];
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
        .captured-photos img {
            width: 100%;
            max-width: 320px;
            height: auto;
            margin-bottom: 10px;
            border: 2px solid #ccc;
            border-radius: 5px;
        }
        #cam, #foto {
            border: 2px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }
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

        /* Actualización del estilo del encabezado para agregar el menú */
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
        
        .header .id-display {
            margin-left: auto;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .section-title .id-display {
            font-size: 1em;
            font-weight: bold;
            color: #007bff;
            background-color: #e9f5ff;
            padding: 5px 10px;
            border-radius: 5px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .id-display span {
            font-size: 1.1em;
            color: #333;
        }

    </style>
</head>
<body>
    <header class="header">
        <!-- Contenedor para el logo y el título para mantener su agrupación -->
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="img/Ophthalmos (1).png" alt="Logo">
            <h1>Formulario Oftalmológico</h1>
        </div>
        <!-- Menú de navegación agregado -->
        <nav class="main-nav">
            <a href="formulario1.php">Formulario 1</a>
            <a href="formulario2.php">Formulario 2</a>
            <a href="administracion/inicio.php">Regresar a Inicio</a>
            <a href="administracion/logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <div class="container">
        <form class="formulario" method="POST" action="formulario1.php?action=<?= $paciente ? 'actualizar' : 'guardar' ?>" enctype="multipart/form-data" id="mainForm">
            <?php if ($message): ?>
                <div class="message <?= strpos($message, 'Error') === false ? 'success' : 'error' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <?php if ($paciente): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($paciente['id']) ?>">
            <?php endif; ?>

            <input type="hidden" id="foto_base64" name="foto_base64" value="">
            
            <div class="section-title">
                Datos Personales
                <?php 
                if ($id_mostrado): 
                ?>
                    <div class="id-display">
                         Formulario No: <span><?= htmlspecialchars($id_mostrado) ?></span>
                    </div>
                <?php 
                else: 
                ?>
                    <div class="id-display">
                        Formulario No: <span><?= htmlspecialchars($proximo_id) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($paciente['fecha'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($paciente['nombre'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="dpi">DPI</label>
                    <input type="text" id="dpi" name="dpi" value="<?= htmlspecialchars($paciente['dpi'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="muniydepto">Municipio y Departamento</label>
                    <input type="text" id="muniydepto" name="muniydepto" value="<?= htmlspecialchars($paciente['muniydepto'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="fechanacimiento">Fecha de Nacimiento</label>
                    <input type="date" id="fechanacimiento" name="fechanacimiento" value="<?= htmlspecialchars($paciente['fechanacimiento'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" required>
                        <option value="M" <?= ($paciente['sexo'] ?? '') == 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($paciente['sexo'] ?? '') == 'F' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($paciente['direccion'] ?? '') ?>" required>
                </div>
            </div>

            <div class="section-title">Resultados Clínicos</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="od">OD (Ojo Derecho)</label>
                    <input type="text" id="od" name="od" value="<?= htmlspecialchars($paciente['od'] ?? '20/20') ?>" required>
                </div>
                <div class="form-group">
                    <label for="oi">OI (Ojo Izquierdo)</label>
                    <input type="text" id="oi" name="oi" value="<?= htmlspecialchars($paciente['oi'] ?? '20/20') ?>" required>
                </div>
                <div class="form-group">
                    <label for="ou">OU (Ambos Ojos)</label>
                    <input type="text" id="ou" name="ou" value="<?= htmlspecialchars($paciente['ou'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="visioncolores">Visión de Colores</label>
                    <input type="text" id="visioncolores" name="visioncolores" value="<?= htmlspecialchars($paciente['visioncolores'] ?? 'Normal') ?>" required>
                </div>
                <div class="form-group">
                    <label for="campovisual">Campo Visual</label>
                    <input type="text" id="campovisual" name="campovisual" value="<?= htmlspecialchars($paciente['campovisual'] ?? 'Normal') ?>" required>
                </div>
                <div class="form-group">
                    <label for="campovisual2">Campo Visual 2</label>
                    <input type="text" id="campovisual2" name="campovisual2" value="<?= htmlspecialchars($paciente['campovisual2'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contraste">Contraste</label>
                    <input type="text" id="contraste" name="contraste" value="<?= htmlspecialchars($paciente['contraste'] ?? 'Normal') ?>" required>
                </div>
                <div class="form-group">
                    <label for="pruebae1">Prueba Estereopsis 1</label>
                    <input type="text" id="pruebae1" name="pruebae1" value="<?= htmlspecialchars($paciente['pruebae1'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="pruebae2">Prueba Estereopsis 2</label>
                    <input type="text" id="pruebae2" name="pruebae2" value="<?= htmlspecialchars($paciente['pruebae2'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contralod">Contraste OD</label>
                    <input type="text" id="contralod" name="contralod" value="<?= htmlspecialchars($paciente['contralod'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="centraloi">Central OI</label>
                    <input type="text" id="centraloi" name="centraloi" value="<?= htmlspecialchars($paciente['centraloi'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="usaanteojos">Usa Anteojos</label>
                    <select id="usaanteojos" name="usaanteojos" required>
                        <option value="si" <?= (isset($paciente['usaanteojos']) && $paciente['usaanteojos'] == 1) ? 'selected' : '' ?>>Sí</option>
                        <option value="no" <?= (isset($paciente['usaanteojos']) && $paciente['usaanteojos'] == 0) ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="usalentescontacto">Usa Lentes de Contacto</label>
                    <select id="usalentescontacto" name="usalentescontacto" required>
                        <option value="si" <?= (isset($paciente['usalentescontacto']) && $paciente['usalentescontacto'] == 1) ? 'selected' : '' ?>>Sí</option>
                        <option value="no" <?= (isset($paciente['usalentescontacto']) && $paciente['usalentescontacto'] == 0) ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="perifericood">Periférico OD</label>
                    <input type="text" id="perifericood" name="perifericood" value="<?= htmlspecialchars($paciente['perifericood'] ?? '90 grados') ?>" required>
                </div>
                <div class="form-group">
                    <label for="perifericooi">Periférico OI</label>
                    <input type="text" id="perifericooi" name="perifericooi" value="<?= htmlspecialchars($paciente['perifericooi'] ?? '90 grados') ?>" required>
                </div>
                <div class="form-group">
                    <label for="tipolicencia">Tipo de Licencia</label>
                    <select id="tipolicencia" name="tipolicencia" required>
                        <option value="A" <?= ($paciente['tipolicencia'] ?? '') == 'A' ? 'selected' : '' ?>>A</option>
                        <option value="B" <?= ($paciente['tipolicencia'] ?? '') == 'B' ? 'selected' : '' ?>>B</option>
                        <option value="C" <?= ($paciente['tipolicencia'] ?? '') == 'C' ? 'selected' : '' ?>>C</option>
                        <option value="E" <?= ($paciente['tipolicencia'] ?? '') == 'E' ? 'selected' : '' ?>>E</option>
                        <option value="M" <?= ($paciente['tipolicencia'] ?? '') == 'M' ? 'selected' : '' ?>>M</option>
                        <option value="M,B" <?= ($paciente['tipolicencia'] ?? '') == 'M,B' ? 'selected' : '' ?>>M,B</option>
                        <option value="siapt" <?= ($paciente['tipolicencia'] ?? '') == 'siapt' ? 'selected' : '' ?>>Paciente apto para todo tipo</option>
                        <option value="noapt" <?= ($paciente['tipolicencia'] ?? '') == 'noapt' ? 'selected' : '' ?>>Paciente NO apto</option>
                    </select>
                </div>
            </div>

            <div class="bottom-actions">
                <button type="button" onclick="guardarFormulario()" class="btn-azul">Guardar / Actualizar</button>
                <button type="button" id="btn-buscar" class="btn-azul">Buscar</button>
                <button type="button" id="btn-editar" class="btn-azul">Editar</button>
                <button type="button" id="btn-eliminar" class="btn-azul">Eliminar</button>
            </div>
        </form>

        <div class="sidebar">
            <div class="sidebar-section">
                <h3>PreView</h3>
                <video id="cam" width="320" height="240" autoplay></video>
                <canvas id="foto" width="320" height="240" style="display: none;"></canvas>
                <button type="button" onclick="capturar()">
                    <i class="fa fa-camera"></i> Tomar Foto
                </button>
            </div>
            
            <div class="sidebar-section">
                <h3>Foto Capturada</h3>
                <div id="captured-photos" class="captured-photos">
                    <?php if ($foto_cargada): ?>
                        <img src="<?= htmlspecialchars($foto_cargada) ?>" alt="Foto del paciente">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>© 2025 Ophthalmos. Todos los derechos reservados.</p>
        <p>| <a href="https://www.bitscreativos.com" target="_blank">Desarrollado por BitsCreativos</a> |</p>
    </footer>

    <script>
        let stream;
        let capturedPhoto = '';
        let formAction = '<?= $paciente ? 'actualizar' : 'guardar' ?>';

        document.addEventListener('DOMContentLoaded', function() {
            inicializarCamera();

            document.getElementById('btn-buscar').addEventListener('click', function() {
                // Reemplazamos alert() con un mensaje de ventana modal o un campo de texto
                const id = prompt('Por favor, ingresa el ID de la ficha a buscar:');
                if (id) {
                    window.location.href = `formulario1.php?action=buscar&id=${encodeURIComponent(id)}`;
                }
            });

            document.getElementById('btn-editar').addEventListener('click', function() {
                // Reemplazamos alert() con un mensaje de ventana modal o un campo de texto
                const id = prompt('Por favor, ingresa el ID de la ficha a editar:');
                if (id) {
                    window.location.href = `formulario1.php?action=editar&id=${encodeURIComponent(id)}`;
                }
            });

            document.getElementById('btn-eliminar').addEventListener('click', function() {
                // Reemplazamos confirm() con una ventana modal personalizada
                const id = prompt('Por favor, ingresa el ID de la ficha a eliminar:');
                if (id) {
                    // Aquí se usaría una ventana modal personalizada en lugar de confirm()
                    if (window.confirm(`¿Estás seguro de que quieres eliminar la ficha con ID ${id}?`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'formulario1.php?action=eliminar';
                        
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

        function inicializarCamera() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(videoStream => {
                    stream = videoStream;
                    document.getElementById('cam').srcObject = stream;
                })
                .catch(error => {
                    console.error("Error al acceder a la cámara: ", error);
                    // Reemplazamos alert() con un mensaje de ventana modal
                    const messageBox = document.createElement('div');
                    messageBox.className = 'message-box';
                    messageBox.innerHTML = `
                        <div class="message-content">
                            <p>No se pudo acceder a la cámara. Por favor, verifica los permisos.</p>
                            <button onclick="this.parentElement.parentElement.remove()">Aceptar</button>
                        </div>
                    `;
                    document.body.appendChild(messageBox);
                });
        }

        function capturar() {
            const canvas = document.getElementById('foto');
            const context = canvas.getContext('2d');
            const video = document.getElementById('cam');
            
            context.drawImage(video, 0, 0, 320, 240);
            
            const imagenBase64 = canvas.toDataURL('image/jpeg');
            capturedPhoto = imagenBase64;
            
            const capturedPhotos = document.getElementById('captured-photos');
            capturedPhotos.innerHTML = '';
            
            const imgElement = document.createElement('img');
            imgElement.src = imagenBase64;
            imgElement.alt = "Foto del paciente";
            capturedPhotos.appendChild(imgElement);
            
            document.getElementById('foto_base64').value = imagenBase64;
            
            console.log('Foto capturada correctamente');
        }

        function guardarFormulario() {
            document.getElementById('mainForm').submit();
        }

        window.addEventListener('beforeunload', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>
