<?php
require_once 'conexion/conexionbd.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$ficha = null;
$id_mostrado = ''; // Variable para almacenar el ID que se mostrará
$proximo_id = ''; // Variable para almacenar el próximo ID

// Iniciar la sesión para acceder a las variables de sesión
session_start();

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
                ID de Ficha: <span><?= htmlspecialchars($id_mostrado) ?></span>
            </div>
        <?php 
        // Si no, mostramos el próximo ID disponible
        else: 
        ?>
            <div class="id-display">
                Próximo ID: <span><?= htmlspecialchars($proximo_id) ?></span>
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