<?php
session_start();
require_once 'db_config.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    if (!empty($nombre) && !empty($email) && !empty($usuario) && !empty($password)) {
        try {
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, telefono, usuario, password, rol) VALUES (?, ?, ?, ?, ?, 'paciente')");
            $stmt->execute([$nombre, $email, $telefono, $usuario, $passHash]);
            $mensaje = "¡Cuenta creada con éxito! Ya puedes iniciar sesión.";
        } catch (PDOException $e) {
            $error = "El usuario o correo electrónico ya se encuentra registrado.";
        }
    } else {
        $error = "Por favor completa todos los campos obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Pacientes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h2>Registro de Paciente</h2>

            <?php if ($mensaje): ?>
                <div class="alert"><?= $mensaje ?></div>
                <p style="text-align: center;"><a href="index.php" class="btn-link">Ir a Iniciar Sesión</a></p>
            <?php else: ?>
                <?php if ($error): ?>
                    <p style="color: red; margin-bottom: 15px;"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <input type="text" name="nombre" placeholder="Nombre Completo" required>
                    <input type="email" name="email" placeholder="Correo Electrónico" required>
                    <input type="text" name="telefono" placeholder="Número de Teléfono" required>
                    <input type="text" name="usuario" placeholder="Nombre de Usuario" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit">Crear Mi Cuenta</button>
                </form>

                <p style="margin-top: 15px; text-align: center; font-size: 14px;">
                    ¿Ya tienes cuenta? <a href="index.php">Inicia Sesión aquí</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>