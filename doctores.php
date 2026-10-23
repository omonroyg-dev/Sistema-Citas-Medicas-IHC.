<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Doctores | Clínica Monroy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #0f172a; color: #94a3b8; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; border-radius: 8px; margin-bottom: 4px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #1e293b; color: #ffffff; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 sidebar p-3 d-flex flex-column">
            <div class="d-flex align-items-center mb-4 text-white">
                <i class="fa-solid fa-hospital text-info fs-3 me-2"></i>
                <span class="fs-5 fw-bold">Clínica Monroy</span>
            </div>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line me-2"></i> Panel</a></li>
                <li class="nav-item"><a href="pacientes.php" class="nav-link"><i class="fa-solid fa-user-injured me-2"></i> Pacientes</a></li>
                <li class="nav-item"><a href="doctores.php" class="nav-link active"><i class="fa-solid fa-user-doctor me-2"></i> Doctores</a></li>
                <li class="nav-item"><a href="citas.php" class="nav-link"><i class="fa-solid fa-calendar-days me-2"></i> Citas</a></li>
                <li class="nav-item"><a href="farmacia.php" class="nav-link"><i class="fa-solid fa-prescription-bottle-medical me-2"></i> Farmacia</a></li>
                <li class="nav-item"><a href="laboratorio.php" class="nav-link"><i class="fa-solid fa-vial me-2"></i> Laboratorio</a></li>
                <li class="nav-item"><a href="facturacion.php" class="nav-link"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Caja</a></li>
            </ul>
            <hr>
            <div>
                <a href="index.php" class="btn btn-outline-danger btn-sm w-100"><i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar Sesión</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 col-lg-10 p-4">
            <h3 class="fw-bold text-dark mb-4"><i class="fa-solid fa-user-doctor text-primary me-2"></i> Staff Médico - Clínica Monroy</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 text-center">
                        <i class="fa-solid fa-user-doctor fs-1 text-primary mb-2"></i>
                        <h5 class="fw-bold mb-1">Dr. Wilson Tipantasi</h5>
                        <p class="text-muted small mb-0">Medicina General</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 text-center">
                        <i class="fa-solid fa-user-doctor fs-1 text-info mb-2"></i>
                        <h5 class="fw-bold mb-1">Dra. Aixa Gallardo</h5>
                        <p class="text-muted small mb-0">Pediatría & Neonatología</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 text-center">
                        <i class="fa-solid fa-user-doctor fs-1 text-success mb-2"></i>
                        <h5 class="fw-bold mb-1">Dr. Melany Espinal</h5>
                        <p class="text-muted small mb-0">Cardiología Externa</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>