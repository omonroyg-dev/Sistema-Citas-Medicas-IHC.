<?php
// index.php - Portal del Paciente Completo
require_once 'db_config.php';
session_start();

$mensaje = "";

// Cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Registro de paciente
if (isset($_POST['registro'])) {
    $usuario = trim($_POST['usuario']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $alergias = trim($_POST['alergias']);
    $tipo_sangre = $_POST['tipo_sangre'];

    try {
        $stmt = $db->prepare("INSERT INTO usuarios (usuario, password, nombre, rol, direccion, alergias, tipo_sangre) 
                              VALUES (:usuario, :password, :nombre, 'paciente', :direccion, :alergias, :tipo_sangre)");
        $stmt->bindValue(':usuario', $usuario);
        $stmt->bindValue(':password', $password);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':direccion', $direccion);
        $stmt->bindValue(':alergias', $alergias);
        $stmt->bindValue(':tipo_sangre', $tipo_sangre);
        $stmt->execute();
        $mensaje = "<div class='alert success'>¡Registro exitoso! Ya puedes ingresar.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert error'>El usuario ya existe. Intenta con otro.</div>";
    }
}

// Login
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
            $mensaje = "<div class='alert success'>Bienvenido/a, " . htmlspecialchars($user['nombre']) . "</div>";
        }
    } else {
        $mensaje = "<div class='alert error'>Usuario o contraseña incorrectos.</div>";
    }
}

// Agendar Cita Médica (cuando el paciente tiene sesión activa)
if (isset($_POST['agendar_cita']) && isset($_SESSION['paciente_id'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $especialidad = $_POST['especialidad'];
    $doctor = $_POST['doctor'];
    $paciente_nombre = $_SESSION['paciente_nombre'];
    $usuario_id = $_SESSION['paciente_id'];

    $stmt = $db->prepare("INSERT INTO citas (paciente_nombre, fecha, hora, especialidad, doctor, estado, usuario_id) 
                          VALUES (:nombre, :fecha, :hora, :especialidad, :doctor, 'Pendiente', :uid)");
    $stmt->bindValue(':nombre', $paciente_nombre);
    $stmt->bindValue(':fecha', $fecha);
    $stmt->bindValue(':hora', $hora);
    $stmt->bindValue(':especialidad', $especialidad);
    $stmt->bindValue(':doctor', $doctor);
    $stmt->bindValue(':uid', $usuario_id);
    $stmt->execute();
    $mensaje = "<div class='alert success'>¡Cita médica agendada correctamente!</div>";
}

// Consultar citas del paciente si inició sesión
$misCitas = [];
if (isset($_SESSION['paciente_id'])) {
    $stmt = $db->prepare("SELECT * FROM citas WHERE usuario_id = :uid ORDER BY fecha DESC");
    $stmt->bindValue(':uid', $_SESSION['paciente_id']);
    $stmt->execute();
    $misCitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal del Paciente</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #0f172a; color: white; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .container { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 30px; width: 100%; max-width: 550px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h2 { color: #38bdf8; text-align: center; margin-bottom: 20px; }
        h3 { color: #38bdf8; margin: 15px 0 10px 0; font-size: 16px; }
        .input-group { margin-bottom: 12px; }
        input, select { width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: white; font-size: 14px; outline: none; }
        button { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        button:hover { background: #1d4ed8; }
        .toggle-link { display: block; text-align: center; color: #38bdf8; font-size: 13px; margin-top: 15px; cursor: pointer; text-decoration: underline; }
        .alert { padding: 10px; border-radius: 6px; font-size: 13px; text-align: center; margin-bottom: 15px; }
        .success { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid #22c55e; }
        .error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
        .admin-link { display: block; text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px; text-decoration: none; }
        .admin-link:hover { color: #38bdf8; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #0f172a; border-radius: 8px; overflow: hidden; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #334155; font-size: 13px; }
        th { background: #334155; }

        /* Widget IA */
        #chat-ia { position: fixed; bottom: 20px; right: 20px; width: 300px; background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 15px; }
        #chat-logs { height: 120px; overflow-y: auto; background: #0f172a; padding: 8px; border-radius: 6px; font-size: 12px; color: #cbd5e1; margin-bottom: 8px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Portal del Paciente</h2>
    <?= $mensaje ?>

    <?php if (isset($_SESSION['paciente_id'])): ?>
        <!-- PANEL INTERNO DEL PACIENTE -->
        <div style="display:flex; justify-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #334155; padding-bottom:10px;">
            <div>Bienvenido/a, <strong><?= htmlspecialchars($_SESSION['paciente_nombre']) ?></strong></div>
            <a href="index.php?logout=1" style="background:#ef4444; color:white; padding:6px 12px; border-radius:6px; text-decoration:none; font-size:12px; font-weight:bold;">Cerrar Sesión</a>
        </div>

        <h3>📅 Agendar Nueva Cita Médica</h3>
        <form method="POST">
            <div class="input-group">
                <input type="date" name="fecha" required>
            </div>
            <div class="input-group">
                <input type="time" name="hora" required>
            </div>
            <div class="input-group">
                <select name="especialidad" required>
                    <option value="">Selecciona Especialidad</option>
                    <option value="Medicina General">Medicina General</option>
                    <option value="Pediatría">Pediatría</option>
                    <option value="Cardiología">Cardiología</option>
                    <option value="Odontología">Odontología</option>
                </select>
            </div>
            <div class="input-group">
                <input type="text" name="doctor" placeholder="Doctor preferido (Opcional)" value="Doctor de Turno">
            </div>
            <button type="submit" name="agendar_cita">Confirmar Cita</button>
        </form>

        <h3>📋 Mis Citas Programadas</h3>
        <table>
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
                    <tr><td colspan="5" style="text-align:center;">No tienes citas agendadas.</td></tr>
                <?php else: ?>
                    <?php foreach ($misCitas as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['fecha']) ?></td>
                        <td><?= htmlspecialchars($c['hora']) ?></td>
                        <td><?= htmlspecialchars($c['especialidad']) ?></td>
                        <td><?= htmlspecialchars($c['doctor']) ?></td>
                        <td><b><?= htmlspecialchars($c['estado']) ?></b></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- FORMULARIO DE LOGIN / REGISTRO -->
        <form id="form-login" method="POST">
            <div class="input-group">
                <input type="text" name="usuario" placeholder="Usuario Paciente" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" name="login">Ingresar</button>
            <span class="toggle-link" onclick="toggleForms(true)">¿No tienes cuenta? Regístrate aquí</span>
            <a href="admin.php" class="admin-link">🔒 Acceso para Administradores</a>
        </form>

        <form id="form-registro" method="POST" style="display: none;">
            <div class="input-group">
                <input type="text" name="nombre" placeholder="Nombre Completo" required>
            </div>
            <div class="input-group">
                <input type="text" name="usuario" placeholder="Nombre de Usuario" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <div class="input-group">
                <input type="text" name="direccion" placeholder="Dirección de Domicilio" required>
            </div>
            <div class="input-group">
                <input type="text" name="alergias" placeholder="Alergias (Ej: Penicilina, Ninguna)" required>
            </div>
            <div class="input-group">
                <select name="tipo_sangre" required>
                    <option value="">Tipo de Sangre</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>
            <button type="submit" name="registro">Registrarse</button>
            <span class="toggle-link" onclick="toggleForms(false)">¿Ya tienes cuenta? Inicia sesión</span>
        </form>
    <?php endif; ?>
</div>

<!-- Asistente IA -->
<div id="chat-ia">
    <div style="color: #38bdf8; font-weight: bold; margin-bottom: 8px; font-size: 13px;">🤖 Asistente Médico IA</div>
    <div id="chat-logs">¡Hola! ¿Tienes dudas sobre tu cita?</div>
    <input type="text" id="ia-prompt" placeholder="Haz tu pregunta..." style="margin-top: 5px; padding: 6px; font-size: 12px;">
    <button onclick="preguntarIA()" style="padding: 6px; font-size: 12px; margin-top: 6px;">Preguntar</button>
</div>

<script>
function toggleForms(showRegistro) {
    var login = document.getElementById('form-login');
    if (login) login.style.display = showRegistro ? 'none' : 'block';
    document.getElementById('form-registro').style.display = showRegistro ? 'block' : 'none';
}

function preguntarIA() {
    var prompt = document.getElementById('ia-prompt');
    var logs = document.getElementById('chat-logs');
    if (!prompt.value.trim()) return;

    logs.innerHTML += "<div><b>Tú:</b> " + prompt.value + "</div>";
    var txt = prompt.value.toLowerCase();
    prompt.value = '';

    setTimeout(function() {
        var resp = "Para atención personalizada, agenda una cita en el formulario.";
        if (txt.indexOf("horario") !== -1) resp = "Atendemos de Lunes a Viernes de 8:00 AM a 5:00 PM.";
        else if (txt.indexOf("sangre") !== -1 || txt.indexOf("alergia") !== -1) resp = "Tus datos médicos quedan registrados en tu ficha al crear tu cuenta.";
        logs.innerHTML += "<div style='color:#38bdf8;'><b>IA:</b> " + resp + "</div>";
        logs.scrollTop = logs.scrollHeight;
    }, 400);
}
</script>

</body>
</html>