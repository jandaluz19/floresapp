<?php
session_start();
require_once '../config.php';
requerirAdmin();

$conn = conectarDB();
$mensaje = '';
$tipo_mensaje = 'error';

// Verificar que se recibió el ID del pedido
if (!isset($_POST['pedido_id']) && !isset($_GET['id'])) {
    header('Location: pedidos.php');
    exit;
}

$pedido_id = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : (int)$_GET['id'];

// ============================================
// PROCESAR VALIDACIÓN DE PAGO
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validar_pago'])) {
    try {
        // Obtener información del pedido
        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmt->execute([$pedido_id]);
        $pedido = $stmt->fetch();
        
        if (!$pedido) {
            throw new Exception("Pedido no encontrado");
        }
        
        $estado_anterior = $pedido['estado'];
        $total_pedido = $pedido['total'];
        
        // Actualizar estado a "pagado"
        $stmt_update = $conn->prepare("UPDATE pedidos SET estado = 'pagado' WHERE id = ?");
        $stmt_update->execute([$pedido_id]);
        
        // ============================================
        // ACTUALIZAR VENTAS TOTALES
        // ============================================
        if ($estado_anterior != 'pagado') {
            // Crear tabla si no existe
            try {
                $conn->exec("
                    CREATE TABLE IF NOT EXISTS estadisticas_ventas (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        fecha DATE NOT NULL UNIQUE,
                        total_ventas DECIMAL(10,2) DEFAULT 0,
                        num_pedidos INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } catch(PDOException $e) {
                // Tabla ya existe
            }
            
            // Insertar o actualizar estadísticas
            $stmt_stats = $conn->prepare("
                INSERT INTO estadisticas_ventas (fecha, total_ventas, num_pedidos) 
                VALUES (CURDATE(), ?, 1)
                ON DUPLICATE KEY UPDATE 
                total_ventas = total_ventas + VALUES(total_ventas), 
                num_pedidos = num_pedidos + 1
            ");
            $stmt_stats->execute([$total_pedido]);
            
            $mensaje = "✓ Pago validado correctamente. Se agregó S/ " . number_format($total_pedido, 2) . " a las ventas totales.";
            $tipo_mensaje = 'success';
        } else {
            $mensaje = "✓ El pedido ya estaba marcado como pagado.";
            $tipo_mensaje = 'success';
        }
        
        // Redirigir después de 2 segundos
        header("refresh:2;url=pedidos.php");
        
    } catch(Exception $e) {
        $mensaje = "✗ Error al validar el pago: " . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Obtener información del pedido para mostrar
$stmt = $conn->prepare("
    SELECT p.*, u.nombre as usuario_nombre, u.email 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}

// Obtener detalles del pedido
$stmt_detalles = $conn->prepare("
    SELECT pd.*, f.nombre as flor_nombre 
    FROM pedido_detalles pd 
    JOIN flores f ON pd.flor_id = f.id 
    WHERE pd.pedido_id = ?
");
$stmt_detalles->execute([$pedido_id]);
$detalles = $stmt_detalles->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Pago - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 2rem;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .mensaje {
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .mensaje.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .info-section {
            margin-bottom: 2rem;
        }
        
        .info-section h3 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .badge.pendiente { background: #fff3cd; color: #856404; }
        .badge.pagado { background: #d4edda; color: #155724; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .total-row {
            font-weight: bold;
            font-size: 1.2rem;
            background: #f8f9fa;
        }
        
        .total-row td {
            color: #667eea;
        }
        
        .comprobante-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
        
        .comprobante-img {
            max-width: 100%;
            max-height: 500px;
            margin-top: 1rem;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .alert-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Validar Pago del Pedido</h1>
            <p>Pedido: <?php echo htmlspecialchars($pedido['numero_pedido']); ?></p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje == 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <!-- Información del Pedido -->
        <div class="card">
            <div class="info-section">
                <h3>📋 Información del Pedido</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Cliente:</span>
                        <span class="info-value"><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($pedido['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha del Pedido:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado Actual:</span>
                        <span class="badge <?php echo $pedido['estado']; ?>">
                            <?php echo strtoupper($pedido['estado']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Detalles del Pedido -->
            <div class="info-section">
                <h3>🛒 Productos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align: center;">Cantidad</th>
                            <th style="text-align: right;">Precio Unit.</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $detalle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detalle['flor_nombre']); ?></td>
                                <td style="text-align: center;"><?php echo $detalle['cantidad']; ?></td>
                                <td style="text-align: right;">S/ <?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                                <td style="text-align: right;">S/ <?php echo number_format($detalle['precio_unitario'] * $detalle['cantidad'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right;"><strong>TOTAL A PAGAR:</strong></td>
                            <td style="text-align: right;"><strong>S/ <?php echo number_format($pedido['total'], 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Comprobante de Pago -->
            <?php if (!empty($pedido['comprobante_pago'])): ?>
            <div class="comprobante-section">
                <h3 style="color: #667eea; text-align: center;">📷 Comprobante de Pago Adjunto</h3>
                <img src="../<?php echo htmlspecialchars($pedido['comprobante_pago']); ?>" 
                     alt="Comprobante de Pago" 
                     class="comprobante-img">
            </div>
            <?php else: ?>
            <div class="alert-warning">
                ⚠️ Este pedido no tiene comprobante de pago adjunto
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Acciones -->
        <div class="actions">
            <a href="pedidos.php" class="btn btn-secondary">
                ← Volver a Pedidos
            </a>
            
            <?php if ($pedido['estado'] != 'pagado'): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
                    <button type="submit" name="validar_pago" class="btn btn-success"
                            onclick="return confirm('¿Confirmar validación del pago?\n\nSe agregará S/ <?php echo number_format($pedido['total'], 2); ?> a las ventas totales.')">
                        ✓ VALIDAR PAGO
                    </button>
                </form>
            <?php else: ?>
                <div class="alert-warning" style="margin: 0; padding: 1rem;">
                    ✓ Este pedido ya fue validado como pagado
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>