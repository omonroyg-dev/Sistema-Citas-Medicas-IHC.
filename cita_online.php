<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Pacientes | Clínica Monroy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.90)), url('fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .form-card {
            max-width: 750px;
            background: rgba(30, 41, 59, 0.92);
            backdrop-filter: blur(12px);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .header-banner {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
        }
        .nav-tabs .nav-link { color: #94a3b8; border: none; font-weight: 600; }
        .nav-tabs .nav-link.active { color: #0284c7; background: #0f172a; border-radius: 8px; }
        .form-control, .form-select {
            background-color: #0f172a;
            border: 1px solid #334155;
            color: #ffffff;
            padding: 10px 12px;
        }
        .form-control:focus, .form-select:focus {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.25);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">

    <div class="form-card w-100 shadow-2xl my-4">
        <div class="header-banner p-4 text-center text-white">
            <i class="fa-solid fa-hospital-user fs-1 text-white mb-2"></i>
            <h3 class="fw-bold mb-1">Clínica de Especialidades Monroy</h3>
            <p class="mb-0 small text-light opacity-75">Portal de Pacientes - Reserva & Consulta de Citas</p>
        </div>

        <div class="p-4">
            <!-- Menú de pestañas para el Paciente -->
            <ul class="nav nav-tabs nav-justified mb-4 p-1 bg-dark rounded-3" id="patientTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="agendar-tab" data-bs-toggle="tab" data-bs-target="#agendar" type="button" role="tab">
                        <i class="fa-solid fa-calendar-plus me-1"></i> Agendar Cita
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="consultar-tab" data-bs-toggle="tab" data-bs-target="#consultar" type="button" role="tab">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Consultar / Cancelar Cita
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="patientTabsContent">
                
                <!-- Pestaña 1: Agendar Cita -->
                <div class="tab-pane fade show active" id="agendar" role="tabpanel">
                    <form action="php/guardar_cita.php" method="POST">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-light">Cédula / Documento *</label>
                                <input type="text" name="cedula" class="form-control" maxlength="10" required placeholder="Ej: 1712345678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-light">Nombre Completo *</label>
                                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Juan Pérez">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-light">Teléfono *</label>
                                <input type="tel" name="telefono" class="form-control" required placeholder="Ej: 0991234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-light">Especialista / Doctor *</label>
                                <select name="doctor" class="form-select" required>
                                    <option value="">-- Seleccionar Doctor --</option>
                                    <option value="Dr. Wilson Monroy - Medicina General">Dr. Wilson Monroy (Medicina General)</option>
                                    <option value="Dra. Carmen Monroy - Pediatría">Dra. Carmen Monroy (Pediatría)</option>
                                    <option value="Dr. Carlos Mendoza - Cardiología">Dr. Carlos Mendoza (Cardiología)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-light">Fecha *</label>
                                <input type="date" name="fecha" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-light">Hora *</label>
                                <input type="time" name="hora" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-light">Motivo de la Consulta</label>
                            <textarea name="motivo" rows="2" class="form-control" placeholder="Síntomas o consulta..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Volver al Login</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fa-solid fa-check me-1"></i> Reservar Cita</button>
                        </div>
                    </form>
                </div>

                <!-- Pestaña 2: Consultar Citas por Cédula -->
                <div class="tab-pane fade" id="consultar" role="tabpanel">
                    <form action="cita_online.php" method="GET" class="mb-4">
                        <label class="form-label small fw-semibold text-light">Ingrese su Cédula de Identidad:</label>
                        <div class="input-group">
                            <input type="text" name="buscar_cedula" class="form-control" placeholder="Ej: 1712345678" required>
                            <button type="submit" class="btn btn-info text-white fw-bold"><i class="fa-solid fa-search me-1"></i> Buscar Mi Cita</button>
                        </div>
                    </form>

                    <?php
                    if (isset($_GET['buscar_cedula'])) {
                        require_once 'config/db.php';
                        $cedula_busqueda = trim($_GET['buscar_cedula']);
                        
                        $stmt = $pdo->prepare("
                            SELECT c.id, c.fecha, c.hora, c.doctor, c.estado, p.nombre 
                            FROM citas c 
                            JOIN pacientes p ON c.paciente_id = p.id 
                            WHERE p.cedula = ? 
                            ORDER BY c.fecha DESC
                        ");
                        $stmt->execute([$cedula_busqueda]);
                        $misCitas = $stmt->fetchAll();

                        if (count($misCitas) > 0) {
                            echo '<div class="table-responsive"><table class="table table-dark table-hover align-middle small">';
                            echo '<thead><tr><th>Fecha</th><th>Hora</th><th>Doctor</th><th>Estado</th><th>Acción</th></tr></thead><tbody>';
                            foreach ($misCitas as $mc) {
                                echo '<tr>';
                                echo '<td>'.date('d/m/Y', strtotime($mc['fecha'])).'</td>';
                                echo '<td>'.date('h:i A', strtotime($mc['hora'])).'</td>';
                                echo '<td>'.htmlspecialchars($mc['doctor']).'</td>';
                                echo '<td><span class="badge bg-warning text-dark">'.htmlspecialchars($mc['estado']).'</span></td>';
                                echo '<td>
                                        <a href="php/cancelar_cita.php?id='.$mc['id'].'&cedula='.$cedula_busqueda.'" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Seguro que desea cancelar esta cita?\')">
                                            <i class="fa-solid fa-trash me-1"></i> Cancelar
                                        </a>
                                      </td>';
                                echo '</tr>';
                            }
                            echo '</tbody></table></div>';
                        } else {
                            echo '<div class="alert alert-warning text-center small">No se encontraron citas registradas con la cédula ingresada.</div>';
                        }
                    }
                    ?>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>