<?php
require_once 'config_session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Íconos de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .dashboard-card {
            border-radius: 15px;
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .brand-logo {
            text-align: center;
            margin: 40px 0 20px;
        }
        .brand-logo img {
            max-width: 220px; /* tamaño más grande del logo */
        }
    </style>
</head>
<body>
    <!-- Navbar minimal -->
    <nav class="navbar navbar-light bg-white shadow-sm px-4">
        <div class="ms-auto d-flex align-items-center">
            <span class="me-3 text-muted">👤 <?= $username ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </a>
        </div>
    </nav>

    <!-- Logo grande arriba -->
    <div class="brand-logo">
        <img src="../img/Ophthalmos (1).png" alt="Logo Ophthalmos">
    </div>

    <!-- Contenido principal -->
    <div class="container my-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary">Bienvenido, <?= $username ?> 👋</h2>
            <p class="text-muted">Seleccione una opción para continuar</p>
        </div>

        <div class="row justify-content-center g-4">
            <!-- Tarjeta Formulario 1 -->
            <div class="col-md-4">
                <div class="card shadow dashboard-card">
                    <div class="card-body text-center">
                        <i class="bi bi-clipboard2-pulse text-primary display-4"></i>
                        <h5 class="card-title mt-3">Formulario 1</h5>
                        <p class="card-text text-muted">Registrar datos de pacientes</p>
                        <a href="../formulario1.php" class="btn btn-primary w-100">Ir al Formulario 1</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Formulario 2 -->
            <div class="col-md-4">
                <div class="card shadow dashboard-card">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-medical text-success display-4"></i>
                        <h5 class="card-title mt-3">Formulario 2</h5>
                        <p class="card-text text-muted">Gestionar fichas clínicas</p>
                        <a href="../formulario2.php" class="btn btn-success w-100">Ir al Formulario 2</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
