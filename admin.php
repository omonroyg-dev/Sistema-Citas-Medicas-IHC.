<?php
// admin.php - Login y Panel de Administración
require_once 'db_config.php';
session_start();

$mensaje = "";

// Cierre de sesión admin
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_auth']);
    header("Location: admin.php");
    exit;
}

// Login exclusivo para Administrador
if (isset($_POST['login_admin'])) {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND rol = 'admin'");
    $stmt->bindValue(':usuario', $usuario);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_auth'] = true;
        $_SESSION['admin_nombre'] = $admin['nombre'];
    } else {
        $mensaje = "<div class='alert error'>Credenciales de administrador incorrectas.</div>";
    }
}

// Acciones del Panel si está autenticado
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    
    // Cambiar estado de cita
    if (isset($_POST['cambiar_estado'])) {
        $stmt = $db->prepare("UPDATE citas SET estado = :estado WHERE id = :id");
        $stmt->bindValue(':estado', $_POST['estado']);
        $stmt->bindValue(':id', $_POST['cita_id']);
        $stmt->execute();
        $mensaje = "<div class='alert success'>Estado de la cita actualizado.</div>";
    }

    // Restablecer contraseña de paciente
    if (isset($_POST['cambiar_password'])) {
        $nueva_clave = password_hash($_POST['nueva_clave'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE usuarios SET password = :pass WHERE id = :id");
        $stmt->bindValue(':pass', $nueva_clave);
        $stmt->bindValue(':id', $_POST['user_id']);
        $stmt->execute();
        $mensaje = "<div class='alert success'>Contraseña restablecida correctamente.</div>";
    }

    // Cargar estadísticas y tablas
    $totalCitas = $db->query("SELECT COUNT(*) FROM citas")->fetchColumn();
    $citasPendientes = $db->query("SELECT COUNT(*) FROM citas WHERE estado = 'Pendiente'")->fetchColumn();
    $citasCompletadas = $db->query("SELECT COUNT(*) FROM citas WHERE estado = 'Completada'")->fetchColumn();
    $totalPacientes = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'paciente'")->fetchColumn();

    $citas = $db->query("SELECT * FROM citas ORDER BY fecha DESC")->fetchAll(PDO::FETCH_ASSOC);
    $pacientes = $db->query("SELECT * FROM usuarios WHERE rol = 'paciente'")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #0f172a; color: #f8fafc; padding: 30px; }
        
        /* Formulario Login Admin */
        .login-box { max-width: 380px; margin: 80px auto; background: #1e293b; padding: 30px; border-radius: 12px; border: 1px solid #334155; }
        .login-box h2 { text-align: center; color: #38bdf8; margin-bottom: 20px; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 10px; margin-bottom: 12px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px; }
        button { width: 100%; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
        
        /* Dashboard Admin */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 15px; }
        .header h1 { color: #38bdf8; font-size: 26px; }
        .btn-logout { background: #ef4444; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 13px; }
        .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 15px; text-align: center; }
        .card h3 { font-size: 13px; color: #94a3b8; }
        .card .num { font-size: 26px; font-weight: bold; color: #38bdf8; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 8px; overflow: hidden; margin-bottom: 30px; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #334155; font-size: 13px; }
        th { background: #334155; }
        .alert { padding: 10px; border-radius: 6px; font-size: 13px; text-align: center; margin-bottom: 15px; }
        .success { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid #22c55e; }
        .error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['admin_auth'])): ?>
    <!-- VISTA DE LOGIN EXCLUSIVA DE ADMIN -->
    <div class="login-box">
        <h2>Acceso Administrativo</h2>
        <?= $mensaje ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario Admin" required>
            <input type="password" name="password" placeholder="Contraseña Admin" required>
            <button type="submit" name="login_admin">Iniciar Sesión</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="color: #38bdf8; font-size: 12px; text-decoration: underline;">← Volver al Portal de Pacientes</a>
        </p>
    </div>

<?php else: ?>
    <!-- VISTA DEL PANEL DE CONTROL -->
    <div class="header">
        <h1>Panel de Administración</h1>
        <a href="admin.php?logout=1" class="btn-logout">Cerrar Sesión Admin</a>
    </div>

    <?= $mensaje ?>

    <div class="metrics">
        <div class="card"><h3>PACIENTES</h3><div class="num"><?= $totalPacientes ?></div></div>
        <div class="card"><h3>TOTAL CITAS</h3><div class="num"><?= $totalCitas ?></div></div>
        <div class="card"><h3>PENDIENTES</h3><div class="num"><?= $citasPendientes ?></div></div>
        <div class="card"><h3>COMPLETADAS</h3><div class="num"><?= $citasCompletadas ?></div></div>
    </div>

    <h2 style="color:#38bdf8; margin-bottom:12px; font-size: 18px;">📅 Citas Programadas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Paciente</th><th>Fecha</th><th>Hora</th><th>Especialidad</th><th>Doctor</th><th>Estado</th><th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($citas)): ?>
                <tr><td colspan="8" style="text-align:center;">No hay citas registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['paciente_nombre']) ?></td>
                    <td><?= htmlspecialchars($c['fecha']) ?></td>
                    <td><?= htmlspecialchars($c['hora']) ?></td>
                    <td><?= htmlspecialchars($c['especialidad']) ?></td>
                    <td><?= htmlspecialchars($c['doctor']) ?></td>
                    <td><b><?= htmlspecialchars($c['estado']) ?></b></td>
                    <td>
                        <form method="POST" style="display:flex; gap:4px;">
                            <input type="hidden" name="cita_id" value="<?= $c['id'] ?>">
                            <select name="estado" style="margin:0; padding:4px;">
                                <option value="Pendiente" <?= $c['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="Completada" <?= $c['estado'] === 'Completada' ? 'selected' : '' ?>>Completada</option>
                                <option value="Cancelada" <?= $c['estado'] === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                            <button type="submit" name="cambiar_estado" style="padding:4px 8px; width:auto;">OK</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2 style="color:#38bdf8; margin-bottom:12px; font-size: 18px;">👨‍⚕️ Registro de Pacientes y Contraseñas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Nombre</th><th>Usuario</th><th>Dirección</th><th>Alergias</th><th>Sangre</th><th>Restablecer Clave</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pacientes)): ?>
                <tr><td colspan="7" style="text-align:center;">No hay pacientes registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($pacientes as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['usuario']) ?></td>
                    <td><?= htmlspecialchars($p['direccion'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($p['alergias'] ?? 'N/A') ?></td>
                    <td><b><?= htmlspecialchars($p['tipo_sangre'] ?? 'N/A') ?></b></td>
                    <td>
                        <form method="POST" style="display:flex; gap:4px;">
                            <input type="hidden" name="user_id" value="<?= $p['id'] ?>">
                            <input type="text" name="nueva_clave" placeholder="Nueva clave" required style="margin:0; padding:4px;">
                            <button type="submit" name="cambiar_password" style="padding:4px 8px; width:auto;">Guardar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>