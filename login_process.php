<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($usuario) || empty($password)) {
        header("Location: ../index.php?error=vacios");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && ($password === $user['password'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nombre']     = $user['nombre_completo'];
        header("Location: ../dashboard.php");
        exit;
    } else {
        header("Location: ../index.php?error=invalid");
        exit;
    }
}
?>