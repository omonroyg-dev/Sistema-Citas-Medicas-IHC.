<?php
// index.php - Portal del Paciente con Buscador, Reloj Tecnológico, Calendario y Cambio de Clave
require_once 'db_config.php';
session_start();

$mensaje = "";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Registro de Paciente
if (isset($_POST['registro'])) {
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
        $mensaje = "<div class='alert success'>Registro exitoso. Ya puedes ingresar.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert error'>El usuario ya existe. Intenta con otro.</div>";
    }
}

// Inicio de Sesión
if (isset($_POST['login'])) {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->bindValue(':usuario', $usuario);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['rol'] === 'admin') {
            $_SESSION['admin_auth'] = true;
            $_SESSION['admin_nombre'] = $user['nombre'];
            header("Location: admin.php");
            exit;
        } else {
            $_SESSION['paciente_id'] = $user['id'];
            $_SESSION['paciente_nombre'] = $user['nombre'];
            $_SESSION['paciente_sangre'] = $user['tipo_sangre'];
            $_SESSION['paciente_alergias'] = $user['alergias'];
            $mensaje = "<div class='alert success'>¡Bienvenido/a al Sistema Médico!</div>";
        }
    } else {
        $mensaje = "<div class='alert error'>Usuario o contraseña incorrectos.</div>";
    }
}

// Cambio de Contraseña de Paciente
if (isset($_POST['cambiar_pass']) && isset($_SESSION['paciente_id'])) {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = password_hash($_POST['pass_nueva'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = :id");
    $stmt->bindValue(':id', $_SESSION['paciente_id']);
    $stmt->execute();
    $pwdDB = $stmt->fetchColumn();

    if ($pwdDB && password_verify($pass_actual, $pwdDB)) {
        $up = $db->prepare("UPDATE usuarios SET password = :pass WHERE id = :id");
        $up->bindValue(':pass', $pass_nueva);
        $up->bindValue(':id', $_SESSION['paciente_id']);
        $up->execute();
        $mensaje = "<div class='alert success'>Contraseña actualizada con éxito.</div>";
    } else {
        $mensaje = "<div class='alert error'>La contraseña actual es incorrecta.</div>";
    }
}

// Recuperación de Contraseña Olvidada
if (isset($_POST['recuperar'])) {
    $usuario = trim($_POST['usuario_recuperar']);
    $direccion = trim($_POST['direccion_verificar']);
    $tipo_sangre = $_POST['sangre_verificar'];
    $nueva_clave = password_hash($_POST['nueva_password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND rol = 'paciente'");
    $stmt->bindValue(':usuario', $usuario);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $dirCoincide = (strtolower(trim($user['direccion'] ?? '')) === strtolower($direccion));
        $sangreCoincide = (($user['tipo_sangre'] ?? '') === $tipo_sangre);

        if ($dirCoincide && $sangreCoincide) {
            $update = $db->prepare("UPDATE usuarios SET password = :pass WHERE id = :id");
            $update->bindValue(':pass', $nueva_clave);
            $update->bindValue(':id', $user['id']);
            $update->execute();
            $mensaje = "<div class='alert success'>Identidad verificada. Contraseña restablecida.</div>";
        } else {
            $mensaje = "<div class='alert error'>Los datos de verificación no coinciden.</div>";
        }
    } else {
        $mensaje = "<div class='alert error'>El usuario ingresado no existe.</div>";
    }
}

// Agendar Cita
if (isset($_POST['agendar_cita']) && isset($_SESSION['paciente_id'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $especialidad = $_POST['especialidad'];
    $doctor = $_POST['doctor'];
    $paciente_nombre = $_SESSION['paciente_nombre'];
    $usuario_id = $_SESSION['paciente_id'];

    $stmt = $db->prepare("INSERT INTO citas (paciente_nombre, fecha, hora, especialidad, doctor, estado, usuario_id) VALUES (:nombre, :fecha, :hora, :especialidad, :doctor, 'Pendiente', :uid)");
    $stmt->bindValue(':nombre', $paciente_nombre);
    $stmt->bindValue(':fecha', $fecha);
    $stmt->bindValue(':hora', $hora);
    $stmt->bindValue(':especialidad', $especialidad);
    $stmt->bindValue(':doctor', $doctor);
    $stmt->bindValue(':uid', $usuario_id);
    $stmt->execute();
    $mensaje = "<div class='alert success'>Cita médica agendada con éxito.</div>";
}

$misCitas = [];
$fechasCitas = [];
if (isset($_SESSION['paciente_id'])) {
    $stmt = $db->prepare("SELECT * FROM citas WHERE usuario_id = :uid ORDER BY fecha DESC");
    $stmt->bindValue(':uid', $_SESSION['paciente_id']);
    $stmt->execute();
    $misCitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($misCitas as $c) {
        $fechasCitas[] = $c['fecha'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Paciente - IHC System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { 
            background: linear-gradient(135deg, rgba(6, 18, 36, 0.88), rgba(10, 25, 47, 0.92)), 
                        url('https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
            color: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 25px; overflow-x: hidden; position: relative; 
        }
        .container { position: relative; z-index: 10; background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(20px); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 24px; padding: 35px; width: 100%; max-width: 650px; box-shadow: 0 0 45px rgba(14, 165, 233, 0.35), 0 25px 50px rgba(0, 0, 0, 0.9); }
        h2 { font-size: 26px; font-weight: 800; color: #38bdf8; text-align: center; margin-bottom: 6px; letter-spacing: 1px; text-transform: uppercase; }
        
        .reloj-tech { background: rgba(3, 7, 18, 0.9); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 14px; padding: 12px; text-align: center; margin-bottom: 22px; box-shadow: inset 0 0 15px rgba(56, 189, 248, 0.2); }
        .reloj-hora { font-size: 22px; font-weight: 900; color: #34d399; letter-spacing: 2px; text-shadow: 0 0 12px rgba(52, 211, 153, 0.6); }
        .reloj-fecha { font-size: 11px; color: #38bdf8; text-transform: uppercase; letter-spacing: 1px; margin-top: 3px; }

        h3 { color: #34d399; margin: 22px 0 12px 0; font-size: 14px; text-transform: uppercase; display: flex; align-items: center; justify-content: space-between; }
        
        .search-box { position: relative; margin-bottom: 15px; }
        .search-box input { width: 100%; padding: 10px 14px 10px 38px; background: rgba(3, 7, 18, 0.9); border: 1px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #f8fafc; font-size: 13px; outline: none; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #38bdf8; }

        .dashboard-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px; }
        .card-item { background: rgba(3, 7, 18, 0.75); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 14px; padding: 14px; text-align: center; }
        .card-item small { color: #94a3b8; font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 4px; }
        .card-item strong { color: #38bdf8; font-size: 16px; font-weight: 700; }

        .calendar-box { background: rgba(3, 7, 18, 0.85); border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 16px; padding: 18px; margin-bottom: 22px; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .calendar-header button { width: auto; padding: 6px 14px; margin-top: 0; font-size: 12px; }
        .calendar-header h4 { color: #38bdf8; font-size: 15px; font-weight: 700; text-transform: capitalize; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; }
        .cal-day-head { color: #94a3b8; font-size: 11px; font-weight: 700; text-transform: uppercase; padding-bottom: 6px; }
        .cal-day { padding: 10px 4px; font-size: 12px; border-radius: 8px; background: rgba(15, 23, 42, 0.6); color: #cbd5e1; position: relative; }
        .cal-day.empty { background: transparent; }
        .cal-day.today { border: 1px solid #38bdf8; font-weight: bold; color: #38bdf8; }
        .cal-day.has-cita { background: rgba(52, 211, 153, 0.25); border: 1px solid #34d399; color: #34d399; font-weight: bold; cursor: pointer; }

        .input-group { margin-bottom: 14px; }
        input, select { width: 100%; padding: 12px 16px; background: rgba(3, 7, 18, 0.85); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; color: #f8fafc; font-size: 14px; outline: none; }
        input:focus, select:focus { border-color: #38bdf8; box-shadow: 0 0 15px rgba(56, 189, 248, 0.5); }
        button { width: 100%; padding: 13px; background: linear-gradient(135deg, #0284c7, #0d9488); color: #ffffff; border: none; border-radius: 12px; font-size: 13px; font-weight: 800; cursor: pointer; margin-top: 10px; text-transform: uppercase; box-shadow: 0 0 15px rgba(2, 132, 199, 0.4); }
        button:hover { background: linear-gradient(135deg, #0369a1, #0f766e); }
        .toggle-link { display: block; text-align: center; color: #38bdf8; font-size: 13px; margin-top: 14px; cursor: pointer; }
        .alert { padding: 12px; border-radius: 10px; font-size: 13px; text-align: center; margin-bottom: 20px; }
        .success { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
        .admin-link { display: block; text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; background: rgba(3, 7, 18, 0.8); border-radius: 12px; overflow: hidden; }
        th, td { padding: 10px 12px; text-align: left; font-size: 13px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        th { background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-weight: 700; text-transform: uppercase; font-size: 11px; }
        #chat-ia { position: fixed; bottom: 25px; right: 25px; width: 310px; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 18px; padding: 16px; z-index: 100; box-shadow: 0 0 20px rgba(56, 189, 248, 0.2); }
        #chat-logs { height: 110px; overflow-y: auto; background: rgba(3, 7, 18, 0.7); padding: 10px; border-radius: 10px; font-size: 12px; color: #cbd5e1; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Portal del Paciente</h2>
    
    <div class="reloj-tech">
        <div class="reloj-hora" id="reloj-hora">00:00:00 AM</div>
        <div class="reloj-fecha" id="reloj-fecha">Cargando fecha del sistema...</div>
    </div>

    <?= $mensaje ?>

    <?php if (isset($_SESSION['paciente_id'])): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid rgba(56,189,248,0.3); padding-bottom:12px;">
            <div>Bienvenido/a, <strong style="color:#38bdf8;"><?= htmlspecialchars($_SESSION['paciente_nombre']) ?></strong></div>
            <a href="index.php?logout=1" style="background:rgba(239,68,68,0.2); color:#f87171; border: 1px solid rgba(239,68,68,0.4); padding:6px 14px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">Cerrar Sesión</a>
        </div>

        <div class="dashboard-cards">
            <div class="card-item">
                <small>Mis Citas Totales</small>
                <strong><?= count($misCitas) ?> Registrada(s)</strong>
            </div>
            <div class="card-item">
                <small>Ficha Clínica</small>
                <strong style="color: #34d399;"><?= htmlspecialchars($_SESSION['paciente_sangre'] ?? 'O+') ?></strong>
            </div>
        </div>

        <h3>📅 Calendario de Citas Medicas</h3>
        <div class="calendar-box">
            <div class="calendar-header">
                <button type="button" onclick="cambiarMes(-1)">← Ant</button>
                <h4 id="cal-titulo">Mes Año</h4>
                <button type="button" onclick="cambiarMes(1)">Sig →</button>
            </div>
            <div class="calendar-grid">
                <div class="cal-day-head">Dom</div>
                <div class="cal-day-head">Lun</div>
                <div class="cal-day-head">Mar</div>
                <div class="cal-day-head">Mié</div>
                <div class="cal-day-head">Jue</div>
                <div class="cal-day-head">Vie</div>
                <div class="cal-day-head">Sáb</div>
            </div>
            <div class="calendar-grid" id="cal-dias" style="margin-top: 6px;"></div>
        </div>

        <h3>📅 Agendar Nueva Cita Médica</h3>
        <form method="POST">
            <div class="input-group"><input type="date" name="fecha" id="fecha_input" required></div>
            <div class="input-group"><input type="time" name="hora" required></div>
            <div class="input-group">
                <select name="especialidad" required>
                    <option value="">Selecciona Especialidad</option>
                    <option value="Medicina General">Medicina General</option>
                    <option value="Pediatría">Pediatría</option>
                    <option value="Cardiología">Cardiología</option>
                    <option value="Odontología">Odontología</option>
                </select>
            </div>
            <div class="input-group"><input type="text" name="doctor" placeholder="Doctor preferido (Opcional)" value="Doctor de Turno"></div>
            <button type="submit" name="agendar_cita">Confirmar Cita Médica</button>
        </form>

        <h3><span>📋 Mis Citas Programadas</span></h3>
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="buscar_cita_paciente" onkeyup="filtrarTablaCitas()" placeholder="Buscar por fecha, especialidad, doctor o estado...">
        </div>

        <table id="tabla_citas_paciente">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Especialidad</th>
                    <th>Doctor</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($misCitas)): ?>
                    <tr><td colspan="5" style="text-align:center; color:#94a3b8;">No tienes citas agendadas aún.</td></tr>
                <?php else: ?>
                    <?php foreach ($misCitas as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['fecha']) ?></td>
                        <td><?= htmlspecialchars($c['hora']) ?></td>
                        <td><?= htmlspecialchars($c['especialidad']) ?></td>
                        <td><?= htmlspecialchars($c['doctor']) ?></td>
                        <td><span style="color: #38bdf8; font-weight:bold;"><?= htmlspecialchars($c['estado']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 style="margin-top: 30px;">🔐 Cambiar Mi Contraseña</h3>
        <form method="POST" style="background: rgba(3, 7, 18, 0.6); padding: 18px; border-radius: 14px; border: 1px solid rgba(56, 189, 248, 0.2);">
            <div class="input-group"><input type="password" name="pass_actual" placeholder="Contraseña Actual" required></div>
            <div class="input-group"><input type="password" name="pass_nueva" placeholder="Nueva Contraseña" required></div>
            <button type="submit" name="cambiar_pass" style="background: #0284c7;">Actualizar Mi Clave</button>
        </form>

    <?php else: ?>
        <form id="form-login" method="POST">
            <div class="input-group"><input type="text" name="usuario" placeholder="Usuario Paciente" required></div>
            <div class="input-group"><input type="password" name="password" placeholder="Contraseña" required></div>
            <button type="submit" name="login">Iniciar Sesión</button>
            
            <span class="toggle-link" onclick="mostrarVista('registro')">¿No tienes cuenta? Regístrate aquí</span>
            <span class="toggle-link" onclick="mostrarVista('recuperar')" style="color:#f59e0b;">¿Olvidaste tu contraseña? Restablécela aquí</span>
            
            <a href="admin.php" class="admin-link">🔒 Acceso para Administradores</a>
        </form>

        <form id="form-registro" method="POST" style="display: none;">
            <div class="input-group"><input type="text" name="nombre" placeholder="Nombre Completo" required></div>
            <div class="input-group"><input type="text" name="usuario" placeholder="Nombre de Usuario" required></div>
            <div class="input-group"><input type="password" name="password" placeholder="Contraseña" required></div>
            <div class="input-group"><input type="text" name="direccion" placeholder="Dirección de Domicilio" required></div>
            <div class="input-group"><input type="text" name="alergias" placeholder="Alergias (Ej: Penicilina, Ninguna)" required></div>
            <div class="input-group">
                <select name="tipo_sangre" required>
                    <option value="">Tipo de Sangre</option>
                    <option value="O+">O+</option><option value="O-">O-</option>
                    <option value="A+">A+</option><option value="A-">A-</option>
                    <option value="B+">B+</option><option value="B-">B-</option>
                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                </select>
            </div>
            <button type="submit" name="registro">Crear Mi Cuenta</button>
            <span class="toggle-link" onclick="mostrarVista('login')">¿Ya tienes cuenta? Inicia sesión</span>
        </form>

        <form id="form-recuperar" method="POST" style="display: none;">
            <p style="font-size:12px; color:#94a3b8; text-align:center; margin-bottom:14px;">Confirma tu usuario y tus datos registrados.</p>
            <div class="input-group"><input type="text" name="usuario_recuperar" placeholder="Tu Usuario Registrado" required></div>
            <div class="input-group"><input type="text" name="direccion_verificar" placeholder="Tu Dirección de Domicilio Registrada" required></div>
            <div class="input-group">
                <select name="sangre_verificar" required>
                    <option value="">Confirma tu Tipo de Sangre</option>
                    <option value="O+">O+</option><option value="O-">O-</option>
                    <option value="A+">A+</option><option value="A-">A-</option>
                    <option value="B+">B+</option><option value="B-">B-</option>
                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                </select>
            </div>
            <div class="input-group"><input type="password" name="nueva_password" placeholder="Nueva Contraseña" required></div>
            <button type="submit" name="recuperar" style="background:#d97706;">Verificar y Restablecer</button>
            <span class="toggle-link" onclick="mostrarVista('login')">← Volver a Iniciar Sesión</span>
        </form>
    <?php endif; ?>
</div>

<div id="chat-ia">
    <div style="color: #38bdf8; font-weight: 700; margin-bottom: 8px; font-size: 13px;">🤖 Asistente Médico IA</div>
    <div id="chat-logs">¡Hola! ¿Tienes dudas sobre tu cita o tu cuenta?</div>
    <input type="text" id="ia-prompt" placeholder="Escribe tu consulta..." style="margin-top: 5px; padding: 8px; font-size: 12px;">
    <button onclick="preguntarIA()" style="padding: 8px; font-size: 12px; margin-top: 6px;">Consultar IA</button>
</div>

<script>
// Reloj Digital
function actualizarRelojTech() {
    var ahora = new Date();
    var h = ahora.getHours();
    var m = ahora.getMinutes();
    var s = ahora.getSeconds();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12; h = h ? h : 12;
    h = h < 10 ? '0'+h : h;
    m = m < 10 ? '0'+m : m;
    s = s < 10 ? '0'+s : s;

    var opcionesFecha = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    var fechaTexto = ahora.toLocaleDateString('es-ES', opcionesFecha);

    document.getElementById('reloj-hora').innerText = '⚡ ' + h + ':' + m + ':' + s + ' ' + ampm;
    document.getElementById('reloj-fecha').innerText = fechaTexto;
}
setInterval(actualizarRelojTech, 1000);
actualizarRelojTech();

// Buscador
function filtrarTablaCitas() {
    var input = document.getElementById("buscar_cita_paciente");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("tabla_citas_paciente");
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

// Calendario
var citasAgendadas = <?= json_encode($fechasCitas) ?>;
var fechaActual = new Date();
var mesActual = fechaActual.getMonth();
var anioActual = fechaActual.getFullYear();
var nombresMeses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

function renderizarCalendario() {
    var contenedorDias = document.getElementById('cal-dias');
    var titulo = document.getElementById('cal-titulo');
    if (!contenedorDias || !titulo) return;

    contenedorDias.innerHTML = '';
    titulo.innerText = nombresMeses[mesActual] + ' ' + anioActual;

    var primerDiaMes = new Date(anioActual, mesActual, 1).getDay();
    var totalDiasMes = new Date(anioActual, mesActual + 1, 0).getDate();
    var hoy = new Date();

    for (var i = 0; i < primerDiaMes; i++) {
        contenedorDias.innerHTML += '<div class="cal-day empty"></div>';
    }

    for (var dia = 1; dia <= totalDiasMes; dia++) {
        var mm = (mesActual + 1) < 10 ? '0' + (mesActual + 1) : (mesActual + 1);
        var dd = dia < 10 ? '0' + dia : dia;
        var fechaStr = anioActual + '-' + mm + '-' + dd;

        var esHoy = (dia === hoy.getDate() && mesActual === hoy.getMonth() && anioActual === hoy.getFullYear()) ? ' today' : '';
        var tieneCita = citasAgendadas.includes(fechaStr) ? ' has-cita' : '';

        var clickAttr = tieneCita ? 'onclick="seleccionarFecha(\'' + fechaStr + '\')"' : '';
        contenedorDias.innerHTML += '<div class="cal-day' + esHoy + tieneCita + '" ' + clickAttr + '>' + dia + '</div>';
    }
}

function cambiarMes(delta) {
    mesActual += delta;
    if (mesActual < 0) { mesActual = 11; anioActual--; } 
    else if (mesActual > 11) { mesActual = 0; anioActual++; }
    renderizarCalendario();
}

function seleccionarFecha(fechaStr) {
    var input = document.getElementById('fecha_input');
    if (input) {
        input.value = fechaStr;
        alert('📅 Cita encontrada en la fecha: ' + fechaStr);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    renderizarCalendario();
});

function mostrarVista(vista) {
    var login = document.getElementById('form-login');
    var registro = document.getElementById('form-registro');
    var recuperar = document.getElementById('form-recuperar');

    if (login) login.style.display = 'none';
    if (registro) registro.style.display = 'none';
    if (recuperar) recuperar.style.display = 'none';

    if (vista === 'login' && login) login.style.display = 'block';
    if (vista === 'registro' && registro) registro.style.display = 'block';
    if (vista === 'recuperar' && recuperar) recuperar.style.display = 'block';
}

function preguntarIA() {
    var prompt = document.getElementById('ia-prompt');
    var logs = document.getElementById('chat-logs');
    if (!prompt.value.trim()) return;

    logs.innerHTML += "<div><b>Tú:</b> " + prompt.value + "</div>";
    var txt = prompt.value.toLowerCase();
    prompt.value = '';

    setTimeout(function() {
        var resp = "Para atención personalizada, agenda una cita en el formulario superior.";
        if (txt.indexOf("horario") !== -1) resp = "Atendemos de Lunes a Viernes de 8:00 AM a 5:00 PM.";
        else if (txt.indexOf("contraseña") !== -1 || txt.indexOf("clave") !== -1) resp = "Puedes restablecer tu clave en la sección inferior.";
        else if (txt.indexOf("sangre") !== -1 || txt.indexOf("alergia") !== -1) resp = "Tus datos médicos se almacenan de forma segura en tu ficha.";
        logs.innerHTML += "<div style='color:#38bdf8;'><b>IA:</b> " + resp + "</div>";
        logs.scrollTop = logs.scrollHeight;
    }, 350);
}
</script>

</body>
</html>