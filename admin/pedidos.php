<?php
session_start();
require '../config.php';
requerirAdmin();

$conn = conectarDB();
$mensaje = '';

// ============================================
// ACTUALIZAR ESTADO DEL PEDIDO
// ============================================
if (isset($_POST['actualizar_estado'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $nuevo_estado = $_POST['estado'];
    
    try {
        // Obtener información actual del pedido ANTES de actualizar
        $stmt_info = $conn->prepare("SELECT estado, total FROM pedidos WHERE id = ?");
        $stmt_info->execute([$pedido_id]);
        $pedido_actual = $stmt_info->fetch();
        
        if (!$pedido_actual) {
            throw new Exception("Pedido no encontrado");
        }
        
        $estado_anterior = $pedido_actual['estado'];
        $total_pedido = $pedido_actual['total'];
        
        // Actualizar estado del pedido
        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $pedido_id]);
        
        $mensaje = "✓ Estado actualizado correctamente de " . ucfirst($estado_anterior) . " a " . ucfirst($nuevo_estado) . ".";
        
        // Si cambió a pagado, marcar fecha de pago
        if ($nuevo_estado == 'pagado' && $estado_anterior != 'pagado') {
            $stmt = $conn->prepare("UPDATE pedidos SET fecha_actualizacion = NOW() WHERE id = ?");
            $stmt->execute([$pedido_id]);
            $mensaje .= " Monto: S/ " . number_format($total_pedido, 2);
        }
        
    } catch(Exception $e) {
        $mensaje = "✗ Error al actualizar el estado: " . $e->getMessage();
    }
}

// ============================================
// PARÁMETROS DE BÚSQUEDA Y FILTROS
// ============================================
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Construir consulta con filtros
$sql = "SELECT p.*, u.nombre as usuario_nombre, u.email 
        FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        WHERE 1=1";

$params = [];

// Filtro de búsqueda (nombre o email)
if (!empty($busqueda)) {
    $sql .= " AND (u.nombre LIKE ? OR u.email LIKE ? OR p.id LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
}

// Filtro de estado
if (!empty($filtro_estado)) {
    $sql .= " AND p.estado = ?";
    $params[] = $filtro_estado;
}

// Filtro de fecha desde
if (!empty($fecha_desde)) {
    $sql .= " AND DATE(p.fecha_pedido) >= ?";
    $params[] = $fecha_desde;
}

// Filtro de fecha hasta
if (!empty($fecha_hasta)) {
    $sql .= " AND DATE(p.fecha_pedido) <= ?";
    $params[] = $fecha_hasta;
}

$sql .= " ORDER BY p.fecha_pedido DESC";

// Ejecutar consulta
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// ============================================
// OBTENER ESTADÍSTICAS DESDE PEDIDOS
// ============================================
$stmt_stats = $conn->query("
    SELECT 
        COUNT(*) AS total_pedidos,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pedidos_pendientes,
        SUM(CASE WHEN estado = 'pagado' THEN 1 ELSE 0 END) AS pedidos_pagados,
        SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) AS pedidos_enviados,
        SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) AS pedidos_entregados,
        COALESCE(SUM(CASE WHEN estado IN ('pagado', 'enviado', 'entregado') THEN total ELSE 0 END), 0) AS ventas_totales
    FROM pedidos
");
$stats = $stmt_stats->fetch();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Pedidos - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .admin-container {
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .logo {
            text-align: center;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        
        .menu {
            list-style: none;
        }
        
        .menu li {
            margin: 0.5rem 0;
        }
        
        .menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            transition: background 0.3s;
        }
        
        .menu a:hover, .menu a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .mensaje {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.3rem;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .filtros-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filtros-card h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .filtros-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 0.5rem;
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            padding: 0.7rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-buscar {
            padding: 0.7rem 1.5rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-buscar:hover {
            background: #5568d3;
        }
        
        .btn-limpiar {
            padding: 0.6rem 1.2rem;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn-limpiar:hover {
            background: #5a6268;
        }
        
        .resultados-info {
            background: #e7f3ff;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            color: #004085;
            border-left: 4px solid #2196F3;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge.pendiente { background: #fff3cd; color: #856404; }
        .badge.pagado { background: #d4edda; color: #155724; }
        .badge.enviado { background: #cce5ff; color: #004085; }
        .badge.entregado { background: #d1ecf1; color: #0c5460; }
        .badge.cancelado { background: #f8d7da; color: #721c24; }
        
        .form-actualizar {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .form-actualizar select {
            padding: 0.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            min-width: 120px;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            font-weight: 600;
            background: #667eea;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .btn-ver {
            background: #28a745;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        
        .btn-ver:hover {
            background: #218838;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }
        
        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            background-color: white;
            margin: 3% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }
        
        .close:hover {
            color: #000;
        }
        
        .comprobante-img {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 1rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                transition: left 0.3s;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .filtros-form {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">🌸 Admin Panel</div>
            <ul class="menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="flores.php">🌸 Gestionar Flores</a></li>
                <li><a href="pedidos.php" class="active">📦 Pedidos</a></li>
                <li><a href="usuarios.php">👥 Usuarios</a></li>
                <li><a href="reporte.php">📑 Reportes</a></li>
                <li><a href="../index.php">🏠 Ver Sitio</a></li>
                <li><a href="../logout.php">🚪 Cerrar Sesión</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>📦 Gestionar Pedidos</h1>
                <p style="color: #666; margin-top: 0.5rem;">Administración de Pedidos - Flores Online UNAP</p>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo strpos($mensaje, '✓') !== false ? 'success' : 'error'; ?>">
                    <span><?php echo $mensaje; ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon">📦</div>
                    <div class="number"><?php echo number_format($stats['total_pedidos']); ?></div>
                    <div class="label">Total Pedidos</div>
                </div>
                <div class="stat-card">
                    <div class="icon">💰</div>
                    <div class="number">S/ <?php echo number_format($stats['ventas_totales'], 2); ?></div>
                    <div class="label">Ventas Totales</div>
                </div>
                <div class="stat-card">
                    <div class="icon">⏳</div>
                    <div class="number"><?php echo number_format($stats['pedidos_pendientes']); ?></div>
                    <div class="label">Pendientes</div>
                </div>
                <div class="stat-card">
                    <div class="icon">✅</div>
                    <div class="number"><?php echo number_format($stats['pedidos_pagados']); ?></div>
                    <div class="label">Pagados</div>
                </div>
            </div>
            
            <!-- Filtros de Búsqueda -->
            <div class="filtros-card">
                <h3>🔍 Filtros de Búsqueda</h3>
                <form method="GET" action="" class="filtros-form">
                    <div class="form-group">
                        <label>Buscar Cliente</label>
                        <input type="text" name="busqueda" 
                               value="<?php echo htmlspecialchars($busqueda); ?>" 
                               placeholder="Nombre, email o ID">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="">Todos los Estados</option>
                            <option value="pendiente" <?php echo $filtro_estado == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="pagado" <?php echo $filtro_estado == 'pagado' ? 'selected' : ''; ?>>Pagado</option>
                            <option value="enviado" <?php echo $filtro_estado == 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                            <option value="entregado" <?php echo $filtro_estado == 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                            <option value="cancelado" <?php echo $filtro_estado == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha Desde</label>
                        <input type="date" name="fecha_desde" 
                               value="<?php echo htmlspecialchars($fecha_desde); ?>">
                    </div>
                    <div class="form-group">
                        <label>Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" 
                               value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-buscar">🔍 Buscar</button>
                    </div>
                </form>
                
                <?php if (!empty($busqueda) || !empty($filtro_estado) || !empty($fecha_desde) || !empty($fecha_hasta)): ?>
                    <a href="pedidos.php" class="btn-limpiar">✕ Limpiar Filtros</a>
                <?php endif; ?>
            </div>
            
            <!-- Información de resultados -->
            <?php if (!empty($busqueda) || !empty($filtro_estado) || !empty($fecha_desde) || !empty($fecha_hasta)): ?>
                <div class="resultados-info">
                    📊 Se encontraron <strong><?php echo count($pedidos); ?></strong> pedidos con los filtros aplicados
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>Pedidos Registrados (<?php echo count($pedidos); ?>)</h2>
                
                <?php if (empty($pedidos)): ?>
                    <div class="empty-state">
                        <div class="icon">📦</div>
                        <p>No se encontraron pedidos</p>
                        <?php if (!empty($busqueda) || !empty($filtro_estado)): ?>
                            <p style="font-size: 0.9rem; margin-top: 1rem;">Prueba ajustando los filtros de búsqueda</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Actualizar Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></td>
                                        <td><small><?php echo htmlspecialchars($pedido['email']); ?></small></td>
                                        <td><strong>S/ <?php echo number_format($pedido['total'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo $pedido['estado']; ?>">
                                                <?php echo ucfirst($pedido['estado']); ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></small></td>
                                        <td>
                                            <form method="POST" class="form-actualizar" 
                                                  onsubmit="return confirm('¿Confirmar cambio de estado?');">
                                                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                <select name="estado" required>
                                                    <option value="pendiente" <?php echo $pedido['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="pagado" <?php echo $pedido['estado'] == 'pagado' ? 'selected' : ''; ?>>Pagado</option>
                                                    <option value="enviado" <?php echo $pedido['estado'] == 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                                    <option value="entregado" <?php echo $pedido['estado'] == 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                                                    <option value="cancelado" <?php echo $pedido['estado'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                                </select>
                                                <button type="submit" name="actualizar_estado" class="btn">Actualizar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>