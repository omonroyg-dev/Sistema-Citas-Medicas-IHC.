<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula   = trim($_POST['cedula'] ?? '');
    $nombre   = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $doctor   = trim($_POST['doctor'] ?? '');
    $fecha    = trim($_POST['fecha'] ?? '');
    $hora     = trim($_POST['hora'] ?? '');
    $motivo   = trim($_POST['motivo'] ?? '');

    if (empty($cedula) || empty($nombre) || empty($telefono) || empty($doctor) || empty($fecha) || empty($hora)) {
        header("Location: ../cita_online.php?status=error_vacios");
        exit;
    }

    try {
        // Verificar o registrar el paciente
        $stmt = $pdo->prepare("SELECT id FROM pacientes WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $paciente = $stmt->fetch();

        if ($paciente) {
            $paciente_id = $paciente['id'];
            // Actualizar el teléfono por si cambió
            $stmtUp = $pdo->prepare("UPDATE pacientes SET nombre = ?, telefono = ? WHERE id = ?");
            $stmtUp->execute([$nombre, $telefono, $paciente_id]);
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO pacientes (cedula, nombre, telefono) VALUES (?, ?, ?)");
            $stmtIns->execute([$cedula, $nombre, $telefono]);
            $paciente_id = $pdo->lastInsertId();
        }

        // Insertar la cita médica
        $stmtCita = $pdo->prepare("INSERT INTO citas (paciente_id, doctor, fecha, hora, motivo, estado) VALUES (?, ?, ?, ?, ?, 'Pendiente')");
        $stmtCita->execute([$paciente_id, $doctor, $fecha, $hora, $motivo]);

        header("Location: ../cita_online.php?status=success");
        exit;
    } catch (\PDOException $e) {
        header("Location: ../cita_online.php?status=error_db");
        exit;
    }
}
?>