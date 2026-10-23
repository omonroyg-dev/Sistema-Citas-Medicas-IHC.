<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config/db.php';

// Obtener lista de pacientes
$stmt = $pdo->query("SELECT * FROM pacientes ORDER BY id DESC");
$pacientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pacientes | Clínica Monroy</title>
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
                <li class="nav-item"><a href="pacientes.php" class="nav-link active"><i class="fa-solid fa-user-injured me-2"></i> Pacientes</a></li>
                <li class="nav-item"><a href="doctores.php" class="nav-link"><i class="fa-solid fa-user-doctor me-2"></i> Doctores</a></li>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0"><i class="fa-solid fa-users text-primary me-2"></i> Listado de Pacientes</h3>
                <button onclick="window.print()" class="btn btn-light border btn-sm fw-semibold"><i class="fa-solid fa-print me-1"></i> Imprimir Lista</button>
            </div>

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>#</th>
                                <th>Cédula</th>
                                <th>Nombre Completo</th>
                                <th>Teléfono</th>
                                <th>Fecha de Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pacientes) > 0): ?>
                                <?php foreach ($pacientes as $index => $p): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($p['cedula']); ?></td>
                                    <td class="text-primary fw-semibold"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($p['telefono']); ?></td>
                                    <td><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($p['creado_en'])); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay pacientes registrados en el sistema.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>