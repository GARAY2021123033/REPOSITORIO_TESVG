<?php
require 'conexion.php';

$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

$stmt = $conn->prepare("SELECT id, contrasena, intentos_fallidos, bloqueado_hasta, email FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($id, $hash, $intentos, $bloqueado, $email);
    $stmt->fetch();

    if ($bloqueado && strtotime($bloqueado) > time()) {
        die("Usuario bloqueado. Intenta más tarde.");
    }

    if (password_verify($contrasena, $hash)) {
        // Generar y enviar token
        $token = bin2hex(random_bytes(3));
        $expira = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $stmtUpdate = $conn->prepare("UPDATE usuarios SET token_2fa=?, token_expira=?, intentos_fallidos=0, bloqueado_hasta=NULL WHERE id=?");
        $stmtUpdate->bind_param("ssi", $token, $expira, $id);
        $stmtUpdate->execute();

        // Simulación de envío (cambiar por mail real)
        echo "Enviando token al correo: $email";

        mail($email, "Tu código 2FA", "Tu token de acceso es: $token");

        echo "Se envió un token al correo.";
        // Redirigir a verificación de token
    } else {
        $intentos++;
        $bloqueado_hasta = $intentos >= 3 ? date("Y-m-d H:i:s", strtotime("+3 minutes")) : NULL;
        $stmtUpdate = $conn->prepare("UPDATE usuarios SET intentos_fallidos=?, bloqueado_hasta=? WHERE id=?");
        $stmtUpdate->bind_param("isi", $intentos, $bloqueado_hasta, $id);
        $stmtUpdate->execute();

        die("Credenciales incorrectas.");
    }
} else {
    die("Usuario no encontrado.");
}

