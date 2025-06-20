<?php
require 'conexion.php';
session_start();

$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

$stmt = $conn->prepare("SELECT id, contrasena, intentos_fallidos, bloqueado_hasta, email FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->store_result();

$response = [];

if ($stmt->num_rows == 1) {
    $stmt->bind_result($id, $hash, $intentos, $bloqueado, $email);
    $stmt->fetch();

    if ($bloqueado && strtotime($bloqueado) > time()) {
        $response['estado'] = 'error';
        $response['mensaje'] = "Usuario bloqueado. Intenta más tarde.";
    } elseif (password_verify($contrasena, $hash)) {
        $token = bin2hex(random_bytes(3));
        $expira = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $stmtUpdate = $conn->prepare("UPDATE usuarios SET token_2fa=?, token_expira=?, intentos_fallidos=0, bloqueado_hasta=NULL WHERE id=?");
        $stmtUpdate->bind_param("ssi", $token, $expira, $id);
        $stmtUpdate->execute();

        mail($email, "Tu código 2FA", "Tu token de acceso es: $token");

        $_SESSION['usuario_id'] = $id;
        $response['estado'] = 'ok';
        $response['mensaje'] = "Se envió un token al correo.";

        
    } else {
        $intentos++;
        $bloqueado_hasta = $intentos >= 3 ? date("Y-m-d H:i:s", strtotime("+3 minutes")) : NULL;
        $stmtUpdate = $conn->prepare("UPDATE usuarios SET intentos_fallidos=?, bloqueado_hasta=? WHERE id=?");
        $stmtUpdate->bind_param("isi", $intentos, $bloqueado_hasta, $id);
        $stmtUpdate->execute();

        $response['estado'] = 'error';
        $response['mensaje'] = "Credenciales incorrectas.";
    }
} else {
    $response['estado'] = 'error';
    $response['mensaje'] = "Usuario no encontrado.";
}

echo json_encode($response);
