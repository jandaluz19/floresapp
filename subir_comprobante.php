<?php
session_start();
require_once 'config.php';

if (!isset($_GET['pedido_id'])) {
    die("Pedido no válido");
}

$pedido_id = $_GET['pedido_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_FILES['comprobante']['name'])) {

        $ext = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = "comp_" . $pedido_id . "_" . time() . "." . $ext;
        $destino = "comprobantes/" . $nombre_archivo;

        move_uploaded_file($_FILES['comprobante']['tmp_name'], $destino);

        $conn = conectarDB();
        $stmt = $conn->prepare("UPDATE pedidos SET comprobante=?, estado_pago='pendiente' WHERE id=?");
        $stmt->execute([$nombre_archivo, $pedido_id]);

        echo "<h2>Comprobante enviado correctamente. Espera la validación del administrador.</h2>";
        exit;
    }
}
?>

<h2>Subir Comprobante de Pago</h2>

<form method="POST" enctype="multipart/form-data">
    <label>Selecciona imagen del comprobante:</label>
    <input type="file" name="comprobante" required>
    <button type="submit">Enviar comprobante</button>
</form>
