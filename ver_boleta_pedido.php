<?php
session_start();
require_once 'config.php';

$conn = conectarDB();

// Verificar que se haya enviado un ID
if (!isset($_GET['id'])) {
    header('Location: mis_pedidos.php');
    exit;
}

$pedido_id = (int)$_GET['id'];

// Verificar permisos: admin o el dueño del pedido
$es_admin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$es_admin && !$usuario_id) {
    header('Location: login.php');
    exit;
}

// Obtener información del pedido
$stmt = $conn->prepare("
    SELECT p.*, u.nombre as usuario_nombre, u.email 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    die("Pedido no encontrado");
}

// Verificar que el usuario sea el dueño del pedido o admin
if (!$es_admin && $pedido['usuario_id'] != $usuario_id) {
    die("No tienes permiso para ver este pedido");
}

// Obtener detalles del pedido
$stmt = $conn->prepare("
    SELECT pd.*, f.nombre as flor_nombre 
    FROM pedido_detalles pd 
    JOIN flores f ON pd.flor_id = f.id 
    WHERE pd.pedido_id = ?
");
$stmt->execute([$pedido_id]);
$items = $stmt->fetchAll();

// ============================================
// PROCESAR VALIDACIÓN DE PAGO (SOLO ADMIN)
// ============================================
$mensaje = '';
if ($es_admin && isset($_POST['validar_pago'])) {
    $estado_anterior = $pedido['estado'];
    $nuevo_estado = 'pagado';
    
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        // Actualizar estado del pedido
        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $pedido_id]);
        
        // ============================================
        // ACTUALIZAR VENTAS TOTALES
        // ============================================
        if ($estado_anterior != 'pagado') {
            // Insertar o actualizar estadísticas de ventas
            $stmt_stats = $conn->prepare("
                INSERT INTO estadisticas_ventas (fecha, total_ventas, num_pedidos) 
                VALUES (CURDATE(), ?, 1)
                ON DUPLICATE KEY UPDATE 
                total_ventas = total_ventas + ?, 
                num_pedidos = num_pedidos + 1
            ");
            $stmt_stats->execute([$pedido['total'], $pedido['total']]);
        }
        
        $conn->commit();
        $mensaje = '✓ Pago validado correctamente. Ventas totales actualizadas.';
        
        // Recargar datos del pedido
        $stmt = $conn->prepare("
            SELECT p.*, u.nombre as usuario_nombre, u.email 
            FROM pedidos p 
            JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$pedido_id]);
        $pedido = $stmt->fetch();
        
    } catch(PDOException $e) {
        $conn->rollBack();
        $mensaje = '✗ Error al validar el pago: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta - <?php echo $pedido['numero_pedido']; ?></title>
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
        
        .boleta-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .boleta-header {
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid #667eea;
            margin-bottom: 2rem;
        }
        
        .boleta-header h1 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .numero-pedido {
            font-size: 1.5rem;
            color: #333;
            font-weight: bold;
        }
        
        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .badge.pendiente { background: #fff3cd; color: #856404; }
        .badge.pagado { background: #d4edda; color: #155724; }
        .badge.enviado { background: #cce5ff; color: #004085; }
        .badge.entregado { background: #d1ecf1; color: #0c5460; }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section h3 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
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
        }
        
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
            max-height: 400px;
            margin-top: 1rem;
            border-radius: 10px;
            border: 2px solid #dee2e6;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: transform 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .mensaje {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        
        .mensaje.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media print {
            .actions, .no-print {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .boleta-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="boleta-container">
        <div class="boleta-header">
            <h1>🌸 FLORES ONLINE UNAP</h1>
            <div class="numero-pedido">Boleta: <?php echo $pedido['numero_pedido']; ?></div>
            <span class="badge <?php echo $pedido['estado']; ?>">
                <?php echo strtoupper($pedido['estado']); ?>
            </span>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo strpos($mensaje, '✓') !== false ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <!-- Información del Cliente -->
        <div class="section">
            <h3>📋 Datos del Cliente</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pedido['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha de Pedido:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Método de Pago:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pedido['metodo_pago']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Productos -->
        <div class="section">
            <h3>🛒 Detalle del Pedido</h3>
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
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['flor_nombre']); ?></td>
                            <td style="text-align: center;"><?php echo $item['cantidad']; ?></td>
                            <td style="text-align: right;">S/ <?php echo number_format($item['precio_unitario'], 2); ?></td>
                            <td style="text-align: right;">S/ <?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;"><strong>TOTAL:</strong></td>
                        <td style="text-align: right;"><strong>S/ <?php echo number_format($pedido['total'], 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Comprobante de Pago -->
        <?php if ($pedido['comprobante_pago']): ?>
        <div class="comprobante-section no-print">
            <h3>📷 Comprobante de Pago</h3>
            <img src="<?php echo htmlspecialchars($pedido['comprobante_pago']); ?>" 
                 alt="Comprobante" 
                 class="comprobante-img">
        </div>
        <?php endif; ?>
        
        <!-- Botones de Acción -->
        <div class="actions no-print">
            <?php if ($es_admin): ?>
                <a href="admin/pedidos.php" class="btn btn-secondary">← Volver a Pedidos</a>
                
                <?php if ($pedido['estado'] == 'pendiente'): ?>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="validar_pago" class="btn btn-success"
                                onclick="return confirm('¿Confirmar validación del pago? Esto actualizará las ventas totales.')">
                            ✓ Validar Pago
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <a href="mis_pedidos.php" class="btn btn-secondary">← Volver a Mis Pedidos</a>
            <?php endif; ?>
            
            <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        </div>
    </div>
</body>
</html>