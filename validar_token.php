<?php
require 'conexion.php';
session_start();

$token = $_POST['token'];
$id = $_SESSION['usuario_id'] ?? null;



$response = [];

if (!$id) {
    $response['estado'] = 'error';
    $response['mensaje'] = "Sesión no válida.";
} else {
    $stmt = $conn->prepare("SELECT token_2fa, token_expira FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($token_db, $expira);
    $stmt->fetch();

    if ($token_db === $token && strtotime($expira) > time()) {
        $response['estado'] = 'ok';
        $response['mensaje'] = "Token válido. Acceso concedido.";
    } else {
        $response['estado'] = 'error';
        $response['mensaje'] = "Token inválido o expirado.";
    }
}

echo json_encode($response);
