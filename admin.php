<?php
// admin.php - Panel Administrativo Avanzado
require_once 'db_config.php';
session_start();

$mensaje = "";

if (!isset($_SESSION['admin_auth'])) {
    if (isset($_POST['admin_login'])) {
        $usuario = trim($_POST['usuario']);
        $password = $_POST['password'];

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND rol = 'admin'");
        $stmt->bindValue(':usuario', $usuario);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_auth'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            header("Location: admin.php");
            exit;
        } else {
            $error_login = "Credenciales administrativas incorrectas.";
        }
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_auth']);
    unset($_SESSION['admin_nombre']);
    unset($_SESSION['admin_id']);
    header("Location: admin.php");
    exit;
}

if (isset($_SESSION['admin_auth'])) {

    // Cambiar clave de Admin
    if (isset($_POST['cambiar_pass_admin'])) {
        $pass_actual = $_POST['pass_actual'];
        $pass_nueva = password_hash($_POST['pass_nueva'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("SELECT password FROM usuarios WHERE usuario = 'admin'");
        $stmt->execute();
        $pwdDB = $stmt->fetchColumn();

        if ($pwdDB && password_verify($pass_actual, $pwdDB)) {
            $up = $db->prepare("UPDATE usuarios SET password = :pass WHERE usuario = 'admin'");
            $up->bindValue(':pass', $pass_nueva);
            $up->execute();
            $mensaje = "<div class='alert success'>Contraseña de Administrador actualizada correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'>La contraseña actual es incorrecta.</div>";
        }
    }

    // Cambiar estado de cita
    if (isset($_POST['cambiar_estado'])) {
        $id_cita = $_POST['id_cita'];
        $nuevo_estado = $_POST['estado'];
        $stmt = $db->prepare("UPDATE citas SET estado = :estado WHERE id = :id");
        $stmt->bindValue(':estado', $nuevo_estado);
        $stmt->bindValue(':id', $id_cita);
        $stmt->execute();
        $mensaje = "<div class='alert success'>Estado de cita actualizado.</div>";
    }

    // Registrar Paciente desde Admin
    if (isset($_POST['crear_paciente'])) {
        $usuario = trim($_POST['usuario']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $nombre = trim($_POST['nombre']);
        $direccion = trim($_POST['direccion']);
        $alergias = trim($_POST['alergias']);
        $tipo_sangre = $_POST['tipo_sangre'];

        try {
            $stmt = $db->prepare("INSERT INTO usuarios (usuario, password, nombre, rol, direccion, alergias, tipo_sangre) VALUES (:usuario, :password, :nombre, 'paciente', :direccion, :alergias, :tipo_sangre)");
            $stmt->bindValue(':usuario', $usuario);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':direccion', $direccion);
            $stmt->bindValue(':alergias', $alergias);
            $stmt->bindValue(':tipo_sangre', $tipo_sangre);
            $stmt->execute();
            $mensaje = "<div class='alert success'>Nuevo paciente guardado exitosamente.</div>";
        } catch (PDOException $e) {
            $mensaje = "<div class='alert error'>El nombre de usuario ya existe.</div>";
        }
    }

    // Editar datos de paciente
    if (isset($_POST['editar_paciente'])) {
        $id_usuario = $_POST['id_usuario'];
        $nombre = trim($_POST['nombre']);
        $direccion = trim($_POST['direccion']);
        $alergias = trim($_POST['alergias']);
        $tipo_sangre = $_POST['tipo_sangre'];

        $stmt = $db->prepare("UPDATE usuarios SET nombre = :nombre, direccion = :direccion, alergias = :alergias, tipo_sangre = :tipo_sangre WHERE id = :id AND rol = 'paciente'");
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':direccion', $direccion);
        $stmt->bindValue(':alergias', $alergias);
        $stmt->bindValue(':tipo_sangre', $tipo_sangre);
        $stmt->bindValue(':id', $id_usuario);
        $stmt->execute();
        $mensaje = "<div class='alert success'>Datos del paciente actualizados.</div>";
    }

    // Eliminar Paciente
    if (isset($_POST['eliminar_paciente'])) {
        $id_usuario = $_POST['id_usuario'];
        $db->prepare("DELETE FROM citas WHERE usuario_id = :id")->execute([':id' => $id_usuario]);
        $db->prepare("DELETE FROM usuarios WHERE id = :id AND rol = 'paciente'")->execute([':id' => $id_usuario]);
        $mensaje = "<div class='alert success'>Paciente y registros asociados eliminados.</div>";
    }

    // Datos y Listas
    $totalPacientes = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'paciente'")->fetchColumn();
    $totalCitas = $db->query("SELECT COUNT(*) FROM citas")->fetchColumn();
    $citasPendientes = $db->query("SELECT COUNT(*) FROM citas WHERE estado = 'Pendiente'")->fetchColumn();

    $pacientes = $db->query("SELECT * FROM usuarios WHERE rol = 'paciente' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $citas = $db->query("SELECT * FROM citas ORDER BY fecha DESC, hora DESC")->fetchAll(PDO::FETCH_ASSOC);

    $fechasTodasCitas = [];
    foreach ($citas as $c) {
        $fechasTodasCitas[] = $c['fecha'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Control Central</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { 
            background: linear-gradient(135deg, rgba(6, 18, 36, 0.92), rgba(10, 25, 47, 0.95)), 
                        url('https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
            color: #f8fafc; min-height: 100vh; padding: 30px 20px; 
        }
        .admin-container { max-width: 1100px; margin: 0 auto; background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(20px); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 24px; padding: 35px; box-shadow: 0 0 50px rgba(14, 165, 233, 0.3); }
        .header-bar { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(56, 189, 248, 0.3); padding-bottom: 18px; margin-bottom: 25px; }
        h2 { color: #38bdf8; font-size: 24px; text-transform: uppercase; font-weight: 800; }
        
        .reloj-tech-admin { background: rgba(3, 7, 18, 0.9); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 12px; padding: 10px; text-align: center; margin-bottom: 25px; }
        .reloj-hora-admin { font-size: 20px; font-weight: 900; color: #34d399; letter-spacing: 2px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 30px; }
        .stat-card { background: rgba(3, 7, 18, 0.85); border: 1px solid rgba(56, 189, 248, 0.25); padding: 20px; border-radius: 16px; text-align: center; }
        .stat-card h4 { color: #94a3b8; font-size: 12px; text-transform: uppercase; margin-bottom: 8px; }
        .stat-card span { font-size: 28px; font-weight: 800; color: #38bdf8; }

        .section-title { color: #34d399; font-size: 15px; text-transform: uppercase; margin: 30px 0 15px 0; border-left: 4px solid #34d399; padding-left: 10px; display: flex; justify-content: space-between; align-items: center; }
        
        .search-box { margin-bottom: 15px; }
        .search-box input { width: 100%; padding: 10px 14px; background: rgba(3, 7, 18, 0.9); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 8px; color: #f8fafc; font-size: 13px; outline: none; }

        table { width: 100%; border-collapse: collapse; background: rgba(3, 7, 18, 0.8); border-radius: 12px; overflow: hidden; margin-bottom: 25px; }
        th, td { padding: 12px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        th { background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-weight: 700; text-transform: uppercase; font-size: 11px; }
        input, select { padding: 10px 14px; background: rgba(3, 7, 18, 0.9); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; color: #f8fafc; font-size: 13px; outline: none; }
        button { padding: 10px 16px; background: #0284c7; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; text-transform: uppercase; font-size: 12px; }
        button:hover { background: #0369a1; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-success { background: #059669; }
        .btn-success:hover { background: #047857; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; margin-bottom: 15px; }
        .alert { padding: 12px; border-radius: 10px; font-size: 13px; text-align: center; margin-bottom: 20px; }
        .success { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }

        .calendar-box-admin { background: rgba(3, 7, 18, 0.85); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 16px; padding: 18px; margin-bottom: 25px; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .calendar-header button { width: auto; padding: 6px 14px; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; }
        .cal-day-head { color: #94a3b8; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .cal-day { padding: 8px 4px; font-size: 12px; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #cbd5e1; }
        .cal-day.has-cita { background: rgba(52, 211, 153, 0.25); border: 1px solid #34d399; color: #34d399; font-weight: bold; }
    </style>
</head>
<body>

<div class="admin-container">
    <?php if (!isset($_SESSION['admin_auth'])): ?>
        <h2 style="text-align: center; margin-bottom: 20px;">Acceso Administrativo</h2>
        <?php if (isset($error_login)): ?>
            <div class="alert error"><?= $error_login ?></div>
        <?php endif; ?>
        <form method="POST" style="max-width: 380px; margin: 0 auto;">
            <div style="margin-bottom: 14px;"><input type="text" name="usuario" placeholder="Usuario Admin" required style="width:100%;"></div>
            <div style="margin-bottom: 14px;"><input type="password" name="password" placeholder="Contraseña" required style="width:100%;"></div>
            <button type="submit" name="admin_login" style="width: 100%;">Ingresar al Sistema</button>
            <a href="index.php" style="display: block; text-align: center; color: #38bdf8; margin-top: 15px; font-size: 13px; text-decoration: none;">← Volver al Portal de Pacientes</a>
        </form>
    <?php else: ?>
        <div class="header-bar">
            <div>
                <h2>Control Administrativo Central</h2>
                <span style="color: #94a3b8; font-size: 13px;">Sesión Activa: <strong style="color: #38bdf8;"><?= htmlspecialchars($_SESSION['admin_nombre']) ?></strong></span>
            </div>
            <div>
                <a href="index.php" style="color: #38bdf8; text-decoration: none; font-size: 13px; margin-right: 15px; font-weight: 700;">🌐 Ir al Portal</a>
                <a href="admin.php?logout=1" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700;">Cerrar Sesión</a>
            </div>
        </div>

        <div class="reloj-tech-admin">
            <div class="reloj-hora-admin" id="reloj-admin">⏱️ 00:00:00 AM</div>
        </div>

        <?= $mensaje ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Pacientes</h4>
                <span><?= $totalPacientes ?></span>
            </div>
            <div class="stat-card">
                <h4>Total Citas Agendadas</h4>
                <span><?= $totalCitas ?></span>
            </div>
            <div class="stat-card">
                <h4>Citas Pendientes</h4>
                <span style="color: #f59e0b;"><?= $citasPendientes ?></span>
            </div>
        </div>

        <div class="section-title">📅 Vista Global de Citas en Calendario</div>
        <div class="calendar-box-admin">
            <div class="calendar-header">
                <button type="button" onclick="cambiarMesAdmin(-1)">← Ant</button>
                <h4 id="cal-titulo-admin" style="color:#38bdf8;">Mes Año</h4>
                <button type="button" onclick="cambiarMesAdmin(1)">Sig →</button>
            </div>
            <div class="calendar-grid">
                <div class="cal-day-head">Dom</div><div class="cal-day-head">Lun</div><div class="cal-day-head">Mar</div><div class="cal-day-head">Mié</div><div class="cal-day-head">Jue</div><div class="cal-day-head">Vie</div><div class="cal-day-head">Sáb</div>
            </div>
            <div class="calendar-grid" id="cal-dias-admin" style="margin-top: 6px;"></div>
        </div>

        <div class="section-title">➕ Registrar Nuevo Paciente</div>
        <form method="POST" style="background: rgba(3, 7, 18, 0.6); padding: 20px; border-radius: 16px; border: 1px solid rgba(56, 189, 248, 0.2); margin-bottom: 25px;">
            <div class="form-grid">
                <input type="text" name="nombre" placeholder="Nombre Completo" required>
                <input type="text" name="usuario" placeholder="Nombre de Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="text" name="direccion" placeholder="Dirección Domicilio" required>
                <input type="text" name="alergias" placeholder="Alergias Médicas" required>
                <select name="tipo_sangre" required>
                    <option value="">Tipo de Sangre</option>
                    <option value="O+">O+</option><option value="O-">O-</option>
                    <option value="A+">A+</option><option value="A-">A-</option>
                    <option value="B+">B+</option><option value="B-">B-</option>
                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                </select>
            </div>
            <button type="submit" name="crear_paciente" class="btn-success">Guardar Paciente</button>
        </form>

        <div class="section-title">👥 Pacientes Registrados (Editar / Eliminar)</div>
        <div class="search-box">
            <input type="text" id="buscar_pacientes" onkeyup="filtrarTabla('buscar_pacientes', 'tabla_pacientes')" placeholder="🔍 Buscar paciente por nombre, usuario, dirección o tipo de sangre...">
        </div>
        <table id="tabla_pacientes">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Dirección</th>
                    <th>Alergias</th>
                    <th>Sangre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pacientes as $p): ?>
                <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($p['usuario']) ?></td>
                    <td><?= htmlspecialchars($p['direccion'] ?: 'N/R') ?></td>
                    <td><?= htmlspecialchars($p['alergias'] ?: 'Ninguna') ?></td>
                    <td><span style="color: #34d399; font-weight: bold;"><?= htmlspecialchars($p['tipo_sangre'] ?: 'N/R') ?></span></td>
                    <td>
                        <button type="button" onclick="toggleEdit(<?= $p['id'] ?>)" style="padding: 5px 10px; font-size: 11px;">Editar</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este paciente y sus citas?');">
                            <input type="hidden" name="id_usuario" value="<?= $p['id'] ?>">
                            <button type="submit" name="eliminar_paciente" class="btn-danger" style="padding: 5px 10px; font-size: 11px;">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <tr id="edit-row-<?= $p['id'] ?>" style="display: none; background: rgba(14, 165, 233, 0.1);">
                    <td colspan="7">
                        <form method="POST" class="form-grid" style="padding: 10px;">
                            <input type="hidden" name="id_usuario" value="<?= $p['id'] ?>">
                            <input type="text" name="nombre" value="<?= htmlspecialchars($p['nombre']) ?>" required>
                            <input type="text" name="direccion" value="<?= htmlspecialchars($p['direccion']) ?>" required>
                            <input type="text" name="alergias" value="<?= htmlspecialchars($p['alergias']) ?>" required>
                            <select name="tipo_sangre" required>
                                <option value="<?= htmlspecialchars($p['tipo_sangre']) ?>" selected><?= htmlspecialchars($p['tipo_sangre']) ?></option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            </select>
                            <button type="submit" name="editar_paciente" class="btn-success">Guardar Cambios</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="section-title">📋 Control e Historial de Citas Médicas</div>
        <div class="search-box">
            <input type="text" id="buscar_citas" onkeyup="filtrarTabla('buscar_citas', 'tabla_citas_admin')" placeholder="🔍 Buscar cita por fecha, paciente, especialidad, doctor o estado...">
        </div>
        <table id="tabla_citas_admin">
            <thead>
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Doctor</th>
                    <th>Estado Actual</th>
                    <th>Cambiar Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['fecha']) ?> - <?= htmlspecialchars($c['hora']) ?></td>
                    <td><strong><?= htmlspecialchars($c['paciente_nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($c['especialidad']) ?></td>
                    <td><?= htmlspecialchars($c['doctor']) ?></td>
                    <td><span style="color: <?= $c['estado'] === 'Completada' ? '#34d399' : '#f59e0b' ?>; font-weight: bold;"><?= htmlspecialchars($c['estado']) ?></span></td>
                    <td>
                        <form method="POST" style="display:flex; gap:6px;">
                            <input type="hidden" name="id_cita" value="<?= $c['id'] ?>">
                            <select name="estado" style="padding: 4px; font-size: 11px;">
                                <option value="Pendiente" <?= $c['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="Completada" <?= $c['estado'] === 'Completada' ? 'selected' : '' ?>>Completada</option>
                                <option value="Cancelada" <?= $c['estado'] === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                            <button type="submit" name="cambiar_estado" style="padding: 4px 8px; font-size: 11px;">OK</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="section-title">🔐 Cambiar Contraseña del Administrador</div>
        <form method="POST" style="background: rgba(3, 7, 18, 0.6); padding: 20px; border-radius: 16px; border: 1px solid rgba(56, 189, 248, 0.2); max-width: 500px;">
            <div class="form-grid">
                <input type="password" name="pass_actual" placeholder="Contraseña Actual Admin" required>
                <input type="password" name="pass_nueva" placeholder="Nueva Contraseña" required>
            </div>
            <button type="submit" name="cambiar_pass_admin" style="background:#0284c7;">Actualizar Clave Administrador</button>
        </form>
    <?php endif; ?>
</div>

<script>
// Reloj Admin
function actualizarRelojAdmin() {
    var ahora = new Date();
    var h = ahora.getHours();
    var m = ahora.getMinutes();
    var s = ahora.getSeconds();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12; h = h ? h : 12;
    h = h < 10 ? '0'+h : h;
    m = m < 10 ? '0'+m : m;
    s = s < 10 ? '0'+s : s;

    var elem = document.getElementById('reloj-admin');
    if (elem) elem.innerText = '⚡ ' + h + ':' + m + ':' + s + ' ' + ampm;
}
setInterval(actualizarRelojAdmin, 1000);
actualizarRelojAdmin();

// Buscador
function filtrarTabla(inputId, tablaId) {
    var input = document.getElementById(inputId);
    var filter = input.value.toLowerCase();
    var table = document.getElementById(tablaId);
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var textoFila = tr[i].innerText.toLowerCase();
        if (textoFila.indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}

function toggleEdit(id) {
    var row = document.getElementById('edit-row-' + id);
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}

// Calendario
var citasGlobales = <?= json_encode($fechasTodasCitas) ?>;
var fechaActual = new Date();
var mesActual = fechaActual.getMonth();
var anioActual = fechaActual.getFullYear();
var nombresMeses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

function renderizarCalendarioAdmin() {
    var contenedorDias = document.getElementById('cal-dias-admin');
    var titulo = document.getElementById('cal-titulo-admin');
    if (!contenedorDias || !titulo) return;

    contenedorDias.innerHTML = '';
    titulo.innerText = nombresMeses[mesActual] + ' ' + anioActual;

    var primerDiaMes = new Date(anioActual, mesActual, 1).getDay();
    var totalDiasMes = new Date(anioActual, mesActual + 1, 0).getDate();

    for (var i = 0; i < primerDiaMes; i++) {
        contenedorDias.innerHTML += '<div class="cal-day" style="background:transparent;"></div>';
    }

    for (var dia = 1; dia <= totalDiasMes; dia++) {
        var mm = (mesActual + 1) < 10 ? '0' + (mesActual + 1) : (mesActual + 1);
        var dd = dia < 10 ? '0' + dia : dia;
        var fechaStr = anioActual + '-' + mm + '-' + dd;

        var tieneCita = citasGlobales.includes(fechaStr) ? ' has-cita' : '';
        contenedorDias.innerHTML += '<div class="cal-day' + tieneCita + '">' + dia + '</div>';
    }
}

function cambiarMesAdmin(delta) {
    mesActual += delta;
    if (mesActual < 0) { mesActual = 11; anioActual--; } 
    else if (mesActual > 11) { mesActual = 0; anioActual++; }
    renderizarCalendarioAdmin();
}

document.addEventListener('DOMContentLoaded', function() {
    renderizarCalendarioAdmin();
});
</script>

</body>
</html>