<?php
// ================================================================
// CONFIGURACIÓN DE SESIÓN CON TIEMPO DE EXPIRACIÓN
// ================================================================

// Establecer el tiempo de vida del cookie de sesión en 30 minutos (1800 segundos).
// Este tiempo define cuánto tiempo durará el cookie en el navegador del usuario.
$session_lifetime = 1800; // 30 minutos * 60 segundos/minuto

// Configura los parámetros de la cookie de sesión.
// 'lifetime' es el tiempo de vida de la cookie.
// 'path' es la ruta en el servidor en la que el cookie de sesión estará disponible.
// 'httponly' evita que JavaScript acceda al cookie, mejorando la seguridad contra ataques XSS.
// 'secure' asegura que el cookie solo se envíe sobre conexiones HTTPS.
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'httponly' => true,
    'secure' => true
]);

// Configurar el tiempo de vida del recolector de basura de la sesión.
// Esto asegura que los archivos de sesión en el servidor se eliminen después de 30 minutos.
ini_set('session.gc_maxlifetime', $session_lifetime);

// Iniciar o reanudar la sesión existente.
session_start();

// ================================================================
// VERIFICACIÓN DEL TIEMPO DE INACTIVIDAD
// ================================================================

// Comprobar si la variable de tiempo de la última actividad existe.
if (isset($_SESSION['last_activity'])) {
    // Calcular el tiempo de inactividad.
    $inactive_time = time() - $_SESSION['last_activity'];
    
    // Si el tiempo de inactividad es mayor que el tiempo de vida de la sesión...
    if ($inactive_time > $session_lifetime) {
        // Redirigir al usuario a la página de cierre de sesión.
        header('Location: logout.php');
        exit();
    }
}

// Si la sesión está activa, actualizar el tiempo de la última actividad.
$_SESSION['last_activity'] = time();

// ================================================================
// NOTA IMPORTANTE:
// El archivo 'logout.php' debe existir y destruir la sesión.
// Por ejemplo:
// <?php
// session_start();
// session_destroy();
// header('Location: login.php');
// exit();
// ?>
