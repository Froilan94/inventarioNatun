<?php
require_once 'auth/roles.php';

requireRoles([
    'admin_super'
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Inventarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
        }
        .card-dashboard {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .card-dashboard:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        }
        .icon-box {
            font-size: 3rem;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark px-4">
    <span class="navbar-brand">📦 Sistema de Inventarios</span>
    <div class="text-white">
        <?= $_SESSION['user_name'] ?> |
        <strong><?= $_SESSION['role_name'] ?></strong>
        <a href="logout.php" class="btn btn-sm btn-outline-warning ms-3">Salir</a>
    </div>
</nav>

<!-- CONTENIDO -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Panel General de Inventarios</h2>
    <p class="text-center text-muted mb-5">
        Selecciona el módulo que deseas administrar
    </p>

    <div class="row g-4">

        <!-- Medicamentos -->
        <div class="col-md-4">
            <a href="indexmedicamentos.php" class="text-decoration-none">
                <div class="card card-dashboard text-center p-4">
                    <div class="icon-box text-primary mb-3">
                        <i class="bi bi-capsule"></i>
                    </div>
                    <h5 class="card-title">Medicamentos</h5>
                    <p class="text-muted">
                        Gestión, altas, bajas y consultas de medicamentos
                    </p>
                </div>
            </a>
        </div>

        <!-- Artesanías -->
        <div class="col-md-4">
            <a href="indexartesanias.php" class="text-decoration-none">
                <div class="card card-dashboard text-center p-4">
                    <div class="icon-box text-success mb-3">
                        <i class="bi bi-palette"></i>
                    </div>
                    <h5 class="card-title">Artesanías</h5>
                    <p class="text-muted">
                        Control de inventario de artesanías
                    </p>
                </div>
            </a>
        </div>

        <!-- Materia Prima -->
        <div class="col-md-4">
            <a href="indexmateria_prima.php" class="text-decoration-none">
                <div class="card card-dashboard text-center p-4">
                    <div class="icon-box text-warning mb-3">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h5 class="card-title">Materia Prima</h5>
                    <p class="text-muted">
                        Administración de insumos y materia prima
                    </p>
                </div>
            </a>
        </div>

                <!-- Gestionar Usuarios -->
        <div class="col-md-4">
            <a href="views/operador_super/indexusuarios.php" class="text-decoration-none">
                <div class="card card-dashboard text-center p-4">
                    <div class="icon-box text-success mb-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="card-title">Gestionar Usuarios</h5>
                    <p class="text-muted">
                        Agregar, modificar y eliminar Usuarios
                    </p>
                </div>
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
