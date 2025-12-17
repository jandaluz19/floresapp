<?php
/*
 * Script para actualizar la base de datos
 * Ejecuta este archivo UNA SOLA VEZ desde el navegador
 * Luego ELIMÍNALO por seguridad
 

require_once 'config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Actualización de Base de Datos</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #667eea; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Actualización de Base de Datos</h1>";

try {
    $conn = conectarDB();
    
    echo "<div class='info'>📋 Iniciando actualización...</div>";
    
    // 1. Actualizar la columna de estado en pedidos
    echo "<h3>1. Actualizando estados de pedidos</h3>";
    try {
        $stmt = $conn->query("ALTER TABLE pedidos MODIFY estado ENUM('pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente'");
        echo "<div class='success'>✅ Columna 'estado' actualizada correctamente</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "<div class='info'>ℹ️ La columna ya está actualizada</div>";
        } else {
            echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
    
    // 2. Actualizar pedidos existentes de 'pagado' a 'confirmado'
    echo "<h3>2. Actualizando pedidos existentes</h3>";
    try {
        $stmt = $conn->query("UPDATE pedidos SET estado = 'confirmado' WHERE estado = 'pagado'");
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "<div class='success'>✅ Se actualizaron $affected pedidos de 'pagado' a 'confirmado'</div>";
        } else {
            echo "<div class='info'>ℹ️ No hay pedidos con estado 'pagado' para actualizar</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // 3. Verificar estructura de flores
    echo "<h3>3. Verificando tabla de flores</h3>";
    try {
        $stmt = $conn->query("DESCRIBE flores");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('imagen', $columns)) {
            echo "<div class='success'>✅ La tabla 'flores' tiene la columna 'imagen'</div>";
            echo "<div class='info'>ℹ️ Las flores son editables. Puedes subir imágenes desde el panel admin.</div>";
        } else {
            echo "<div class='error'>❌ Falta la columna 'imagen' en la tabla flores</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // 4. Verificar carpetas de uploads
    echo "<h3>4. Verificando carpetas de archivos</h3>";
    
    $carpetas = [
        'uploads/comprobantes' => 'Comprobantes de pago',
        'uploads/flores' => 'Imágenes de flores'
    ];
    
    foreach ($carpetas as $carpeta => $descripcion) {
        if (is_dir($carpeta)) {
            if (is_writable($carpeta)) {
                echo "<div class='success'>✅ Carpeta '$carpeta' existe y tiene permisos de escritura</div>";
            } else {
                echo "<div class='error'>❌ Carpeta '$carpeta' existe pero NO tiene permisos de escritura. Ejecuta: chmod 777 $carpeta</div>";
            }
        } else {
            if (mkdir($carpeta, 0777, true)) {
                echo "<div class='success'>✅ Carpeta '$carpeta' creada exitosamente</div>";
            } else {
                echo "<div class='error'>❌ No se pudo crear la carpeta '$carpeta'. Créala manualmente.</div>";
            }
        }
    }
    
    // 5. Resumen final
    echo "<h3>📊 Resumen de Actualización</h3>";
    
    // Contar pedidos por estado
    $stmt = $conn->query("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado");
    $estados = $stmt->fetchAll();
    
    if (count($estados) > 0) {
        echo "<div class='info'><strong>Pedidos en el sistema:</strong><br>";
        foreach ($estados as $estado) {
            echo "- " . ucfirst($estado['estado']) . ": " . $estado['total'] . "<br>";
        }
        echo "</div>";
    }
    
    // Contar flores
    $stmt = $conn->query("SELECT COUNT(*) as total FROM flores");
    $total_flores = $stmt->fetch()['total'];
    echo "<div class='info'><strong>Total de flores:</strong> $total_flores</div>";
    
    // Calcular ventas
    $stmt = $conn->query("SELECT COALESCE(SUM(total), 0) as ventas FROM pedidos WHERE estado IN ('confirmado', 'enviado', 'entregado')");
    $ventas = $stmt->fetch()['ventas'];
    echo "<div class='info'><strong>Ventas totales:</strong> S/ " . number_format($ventas, 2) . "</div>";
    
    echo "<div class='success' style='margin-top: 30px;'>
            <h2>✅ Actualización Completada</h2>
            <p><strong>¿Qué puedes hacer ahora?</strong></p>
            <ul>
                <li>Las flores existentes ya son editables</li>
                <li>Puedes subir imágenes al editar flores</li>
                <li>Los estados ahora son: Pendiente, Confirmado, Enviado, Entregado, Cancelado</li>
                <li>Las ventas se calculan correctamente</li>
                <li>Las boletas ya NO dicen 'provisional'</li>
            </ul>
            <p style='margin-top: 20px;'><strong>⚠️ IMPORTANTE:</strong> Por seguridad, <strong style='color: red;'>ELIMINA este archivo (actualizar_bd.php)</strong> después de ejecutarlo.</p>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='error'><h3>❌ Error Fatal</h3>" . $e->getMessage() . "</div>";
}

echo "
        <div style='text-align: center; margin-top: 30px;'>
            <a href='admin/dashboard.php' style='background: #667eea; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; display: inline-block;'>
                Ir al Dashboard Admin
            </a>
            <a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; display: inline-block; margin-left: 10px;'>
                Ir al Inicio
            </a>
        </div>
    </div>
</body>
</html>";
?>

*/

//<?php
session_start();
require_once 'config.php';

// Verificar si es admin
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener ID del pedido
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($pedido_id <= 0) {
    header("Location: admin_pedidos.php");
    exit();
}

// Obtener información del pedido
$sql = "SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email, u.telefono as cliente_telefono
        FROM pedidos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();

if(!$pedido) {
    header("Location: admin_pedidos.php");
    exit();
}

// Obtener detalles del pedido
$sql_detalles = "SELECT dp.*, f.nombre as flor_nombre, f.precio
                 FROM detalle_pedido dp
                 INNER JOIN flores f ON dp.flor_id = f.id
                 WHERE dp.pedido_id = ?";
$stmt_detalles = $conn->prepare($sql_detalles);
$stmt_detalles->bind_param("i", $pedido_id);
$stmt_detalles->execute();
$detalles = $stmt_detalles->get_result();

// Procesar validación de pago
if(isset($_POST['validar_pago'])) {
    $nuevo_estado = 'confirmado';
    
    // Inicializar variable para evitar error undefined
    $es_provisional = false;
    
    // Verificar si el pedido estaba en estado provisional
    if($pedido['estado'] == 'provisional' || $pedido['estado'] == 'pendiente') {
        $es_provisional = true;
    }
    
    // Actualizar estado del pedido
    $sql_update = "UPDATE pedidos SET estado = ?, fecha_pago = NOW() WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $nuevo_estado, $pedido_id);
    
    if($stmt_update->execute()) {
        // Si el pedido estaba provisional, ahora contabilizar en ventas totales
        if($es_provisional) {
            // Actualizar estadísticas de ventas
            $sql_stats = "INSERT INTO estadisticas_ventas (fecha, total_ventas, num_pedidos) 
                          VALUES (CURDATE(), ?, 1)
                          ON DUPLICATE KEY UPDATE 
                          total_ventas = total_ventas + ?, 
                          num_pedidos = num_pedidos + 1";
            $stmt_stats = $conn->prepare($sql_stats);
            $stmt_stats->bind_param("dd", $pedido['total'], $pedido['total']);
            $stmt_stats->execute();
        }
        
        $_SESSION['mensaje'] = "Pago validado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: admin_pedidos.php");
        exit();
    } else {
        $error = "Error al validar el pago: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Pedido #<?php echo $pedido['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .boleta-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .boleta-header {
            border-bottom: 3px solid #e91e63;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .estado-badge {
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 20px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .tabla-productos {
            margin-top: 20px;
        }
        .btn-imprimir {
            margin-top: 20px;
        }
        @media print {
            .btn-imprimir, .navbar, .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="boleta-container">
            <div class="boleta-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-file-invoice"></i> BOLETA DE VENTA</h2>
                        <p class="text-muted mb-0">Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div>
                        <?php
                        $badge_class = '';
                        switch($pedido['estado']) {
                            case 'confirmado':
                                $badge_class = 'bg-success';
                                break;
                            case 'pendiente':
                            case 'provisional':
                                $badge_class = 'bg-warning text-dark';
                                break;
                            case 'cancelado':
                                $badge_class = 'bg-danger';
                                break;
                            default:
                                $badge_class = 'bg-secondary';
                        }
                        ?>
                        <span class="badge estado-badge <?php echo $badge_class; ?>">
                            <?php echo strtoupper($pedido['estado']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Información del Cliente -->
            <div class="info-section">
                <h5><i class="fas fa-user"></i> Datos del Cliente</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido['cliente_nombre']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido['cliente_email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['cliente_telefono']); ?></p>
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Dirección de Entrega -->
            <div class="info-section">
                <h5><i class="fas fa-map-marker-alt"></i> Dirección de Entrega</h5>
                <p><?php echo htmlspecialchars($pedido['direccion_entrega']); ?></p>
            </div>

            <!-- Productos -->
            <div class="tabla-productos">
                <h5><i class="fas fa-shopping-cart"></i> Productos</h5>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio Unit.</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        while($detalle = $detalles->fetch_assoc()): 
                            $subtotal_item = $detalle['cantidad'] * $detalle['precio'];
                            $subtotal += $subtotal_item;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detalle['flor_nombre']); ?></td>
                            <td class="text-center"><?php echo $detalle['cantidad']; ?></td>
                            <td class="text-end">S/ <?php echo number_format($detalle['precio'], 2); ?></td>
                            <td class="text-end">S/ <?php echo number_format($subtotal_item, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>S/ <?php echo number_format($pedido['total'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Comprobante de Pago -->
            <?php if(!empty($pedido['comprobante_pago'])): ?>
            <div class="info-section">
                <h5><i class="fas fa-receipt"></i> Comprobante de Pago</h5>
                <img src="<?php echo htmlspecialchars($pedido['comprobante_pago']); ?>" 
                     alt="Comprobante" 
                     class="img-fluid" 
                     style="max-width: 400px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <?php endif; ?>

            <!-- Botones de Acción -->
            <div class="d-flex gap-2 mt-4 no-print">
                <a href="admin_pedidos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                
                <?php if($pedido['estado'] == 'pendiente' || $pedido['estado'] == 'provisional'): ?>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="validar_pago" class="btn btn-success" 
                            onclick="return confirm('¿Confirmar validación del pago?')">
                        <i class="fas fa-check"></i> Validar Pago
                    </button>
                </form>
                <?php endif; ?>
                
                <button onclick="window.print()" class="btn btn-primary ms-auto">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>