<?php session_start(); if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Farmacia | Clínica Monroy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { background:#f8fafc; } .sidebar { min-height:100vh; background:#0f172a; color:#94a3b8; } .sidebar .nav-link { color:#94a3b8; }</style>
</head>
<body>
<div class="container-fluid"><div class="row">
<nav class="col-md-3 col-lg-2 sidebar p-3 text-white">
    <div class="fw-bold fs-5 mb-4"><i class="fa-solid fa-hospital text-info me-2"></i>Clínica Monroy</div>
    <ul class="nav flex-column"><li class="nav-item"><a href="dashboard.php" class="nav-link text-white"><i class="fa-solid fa-arrow-left me-2"></i> Volver al Panel</a></li></ul>
</nav>
<main class="col-md-9 col-lg-10 p-5 text-center">
    <i class="fa-solid fa-prescription-bottle-medical fs-1 text-primary mb-3"></i>
    <h3 class="fw-bold">Módulo de Farmacia e Inventario</h3>
    <p class="text-muted">Gestión de insumos y medicamentos en preparación para Clínica Monroy.</p>
</main>
</div></div>
</body>
</html>