<?php
session_start();
require_once 'db_config.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_email = trim($_POST['usuario_email']);
    $nueva_password = $_POST['nueva_password'];

    if (!empty($usuario_email) && !empty($nueva_password)) {
        // Buscar por usuario o por email
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE (usuario = ? OR email = ?) AND rol = 'paciente'");
        $stmt->execute([$usuario_email, $usuario_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $passHash = password_hash($nueva_password, PASSWORD_DEFAULT);
            $stmtUp = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmtUp->execute([$passHash, $user['id']]);
            $mensaje = "¡Contraseña actualizada con éxito! Ya puedes iniciar sesión.";
        } else {
            $error = "No se encontró ningún paciente con esos datos.";
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h2>Recuperar Contraseña</h2>

            <?php if ($mensaje): ?>
                <div class="alert"><?= $mensaje ?></div>
                <p style="text-align: center;"><a href="index.php">Ir al Login de Pacientes</a></p>
            <?php else: ?>
                <?php if ($error): ?>
                    <p style="color: #f87171; margin-bottom: 15px; text-align: center;"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <input type="text" name="usuario_email" placeholder="Usuario o Correo Registrado" required>
                    <input type="password" name="nueva_password" placeholder="Nueva Contraseña" required>
                    <button type="submit">Restablecer Contraseña</button>
                </form>

                <p style="margin-top: 20px; text-align: center; font-size: 14px;">
                    <a href="index.php">← Volver al Inicio de Sesión</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>