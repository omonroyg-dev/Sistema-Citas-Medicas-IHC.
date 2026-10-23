<?php
require_once '../config/db.php';

if (isset($_GET['id']) && isset($_GET['cedula'])) {
    $cita_id = (int)$_GET['id'];
    $cedula  = trim($_GET['cedula']);

    // Eliminar la cita solicitada
    $stmt = $pdo->prepare("DELETE FROM citas WHERE id = ?");
    $stmt->execute([$cita_id]);

    header("Location: ../cita_online.php?buscar_cedula=" . urlencode($cedula));
    exit;
}
?>