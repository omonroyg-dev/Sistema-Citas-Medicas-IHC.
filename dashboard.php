<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/db.php';

// Consultas dinámicas
$totalPacientes  = $pdo->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();
$citasHoy        = $pdo->query("SELECT COUNT(*) FROM citas WHERE fecha = CURDATE()")->fetchColumn();
$citasPendientes = $pdo->query("SELECT COUNT(*) FROM citas WHERE estado = 'Pendiente'")->fetchColumn();

// Citas programadas para hoy
$stmtCitas = $pdo->query("
    SELECT c.hora, c.doctor, c.motivo, c.estado, p.cedula, p.nombre 
    FROM citas c 
    JOIN pacientes p ON c.paciente_id = p.id 
    WHERE c.fecha = CURDATE() 
    ORDER BY c.hora ASC
");
$citasDelDia = $stmtCitas->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Clínica Monroy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #0f172a; color: #94a3b8; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; border-radius: 8px; margin-bottom: 4px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #1e293b; color: #ffffff; }
        .kpi-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 sidebar p-3 d-flex flex-column">
            <div class="d-flex align-items-center mb-4 text-white">
                <i class="fa-solid fa-hospital text-info fs-3 me-2"></i>
                <span class="fs-5 fw-bold">Clínica Monroy</span>
            </div>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-line me-2"></i> Panel</a></li>
                <li class="nav-item"><a href="pacientes.php" class="nav-link"><i class="fa-solid fa-user-injured me-2"></i> Pacientes</a></li>
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
                <div>
                    <h3 class="fw-bold text-dark mb-0">Panel Administrativo</h3>
                    <small class="text-muted">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></small>
                </div>
                <a href="cita_online.php" target="_blank" class="btn btn-success fw-bold btn-sm">
                    <i class="fa-solid fa-globe me-1"></i> Portal Citas Web
                </a>
            </div>

            <!-- KPI Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card kpi-card p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase small fw-bold">Total Pacientes</span>
                                <h2 class="fw-bold text-dark mt-1 mb-0"><?php echo $totalPacientes; ?></h2>
                            </div>
                            <div class="bg-primary-subtle text-primary p-3 rounded-3"><i class="fa-solid fa-users fs-3"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi-card p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase small fw-bold">Citas para Hoy</span>
                                <h2 class="fw-bold text-dark mt-1 mb-0"><?php echo $citasHoy; ?></h2>
                            </div>
                            <div class="bg-warning-subtle text-warning p-3 rounded-3"><i class="fa-solid fa-calendar-day fs-3"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi-card p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase small fw-bold">Citas Pendientes</span>
                                <h2 class="fw-bold text-dark mt-1 mb-0"><?php echo $citasPendientes; ?></h2>
                            </div>
                            <div class="bg-danger-subtle text-danger p-3 rounded-3"><i class="fa-solid fa-clock fs-3"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Citas del Día -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-calendar-check text-primary me-2"></i>Citas Programadas para Hoy</h6>
                    <button onclick="window.print()" class="btn btn-light btn-sm text-secondary border fw-semibold"><i class="fa-solid fa-print me-1"></i> Imprimir</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Hora</th>
                                <th>Documento</th>
                                <th>Paciente</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($citasDelDia) > 0): ?>
                                <?php foreach ($citasDelDia as $c): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo date('h:i A', strtotime($c['hora'])); ?></td>
                                    <td><?php echo htmlspecialchars($c['cedula']); ?></td>
                                    <td class="fw-semibold text-primary"><?php echo htmlspecialchars($c['nombre']); ?></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($c['motivo'] ?: 'Consulta General'); ?></small></td>
                                    <td><span class="badge bg-warning text-dark px-2 py-1"><?php echo htmlspecialchars($c['estado']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay citas programadas para el día de hoy.</td>
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