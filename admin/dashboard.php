<?php
session_start();
require_once '../config.php';
requerirAdmin();

$conn = conectarDB();

// ============================================
// ESTADÍSTICAS PRINCIPALES
// ============================================

// Total de usuarios (solo clientes, no admins)
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'");
$total_usuarios = $stmt->fetch()['total'];

// Total de flores
$stmt = $conn->query("SELECT COUNT(*) as total FROM flores");
$total_flores = $stmt->fetch()['total'];

// Total de pedidos
$stmt = $conn->query("SELECT COUNT(*) as total FROM pedidos");
$total_pedidos = $stmt->fetch()['total'];

// ============================================
// VENTAS TOTALES (SOLO PEDIDOS COMPLETADOS)
// ============================================
$stmt = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS total 
    FROM pedidos 
    WHERE estado IN ('pagado', 'enviado', 'entregado')
");
$ventas_totales = $stmt->fetch()['total'];

// ============================================
// PEDIDOS POR ESTADO
// ============================================
$stmt = $conn->query("
    SELECT 
        COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
        COUNT(CASE WHEN estado = 'pagado' THEN 1 END) as pagados,
        COUNT(CASE WHEN estado = 'enviado' THEN 1 END) as enviados,
        COUNT(CASE WHEN estado = 'entregado' THEN 1 END) as entregados,
        COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as cancelados
    FROM pedidos
");
$pedidos_stats = $stmt->fetch();

// ============================================
// VENTAS DEL DÍA
// ============================================
$stmt = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS total
    FROM pedidos
    WHERE estado IN ('pagado', 'enviado', 'entregado')
    AND DATE(fecha_pedido) = CURDATE()
");
$ventas_hoy = $stmt->fetch()['total'];

// Pedidos de hoy
$stmt = $conn->query("
    SELECT COUNT(*) as total
    FROM pedidos
    WHERE DATE(fecha_pedido) = CURDATE()
");
$pedidos_hoy = $stmt->fetch()['total'];

// ============================================
// VENTAS DEL MES
// ============================================
$stmt = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS total
    FROM pedidos
    WHERE estado IN ('pagado', 'enviado', 'entregado')
    AND MONTH(fecha_pedido) = MONTH(CURDATE())
    AND YEAR(fecha_pedido) = YEAR(CURDATE())
");
$ventas_mes = $stmt->fetch()['total'];

// Pedidos del mes
$stmt = $conn->query("
    SELECT COUNT(*) as total
    FROM pedidos
    WHERE MONTH(fecha_pedido) = MONTH(CURDATE())
    AND YEAR(fecha_pedido) = YEAR(CURDATE())
");
$pedidos_mes = $stmt->fetch()['total'];

// ============================================
// VENTAS DEL AÑO
// ============================================
$stmt = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS total
    FROM pedidos
    WHERE estado IN ('pagado', 'enviado', 'entregado')
    AND YEAR(fecha_pedido) = YEAR(CURDATE())
");
$ventas_anio = $stmt->fetch()['total'];

// ============================================
// PRODUCTOS MÁS VENDIDOS
// ============================================
$stmt = $conn->query("
    SELECT 
        f.nombre,
        SUM(pd.cantidad) as total_vendido,
        SUM(pd.subtotal) as ingresos_generados
    FROM pedido_detalles pd
    INNER JOIN flores f ON pd.flor_id = f.id
    INNER JOIN pedidos p ON pd.pedido_id = p.id
    WHERE p.estado IN ('pagado', 'enviado', 'entregado')
    GROUP BY f.id, f.nombre
    ORDER BY total_vendido DESC
    LIMIT 5
");
$productos_top = $stmt->fetchAll();

// ============================================
// ÚLTIMOS PEDIDOS
// ============================================
$stmt = $conn->query("
    SELECT p.*, u.nombre as usuario_nombre, u.email 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.fecha_pedido DESC 
    LIMIT 10
");
$ultimos_pedidos = $stmt->fetchAll();

// ============================================
// FLORES CON STOCK BAJO (opcional)
// ============================================
$stmt = $conn->query("
    SELECT COUNT(*) as total 
    FROM flores 
    WHERE disponibilidad = 'Agotado'
");
$flores_agotadas = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Flores Online UNAP</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header h1 {
            color: #333;
            font-size: 2rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3rem;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
        }
        
        .stat-icon.usuarios { background: #e3f2fd; }
        .stat-icon.flores { background: #f3e5f5; }
        .stat-icon.pedidos { background: #fff3e0; }
        .stat-icon.ventas { background: #e8f5e9; }
        
        .stat-info h3 {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .stat-info .number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        
        .ventas-detalle {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .mini-stat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .mini-stat:hover {
            transform: translateY(-3px);
        }
        
        .mini-stat .label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        
        .mini-stat .value {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
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
        .badge.admin { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            display: inline-block;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .actualizar-info {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            padding: 1rem;
            background: #e7f3ff;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid #2196F3;
        }
        
        .acciones-rapidas {
            text-align: center;
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .top-productos-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-productos-item:last-child {
            border-bottom: none;
        }
        
        .top-productos-item:hover {
            background: #f8f9fa;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        @media (max-width: 1024px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                transition: left 0.3s;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .ventas-detalle {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">🌸 Admin Panel</div>
            <ul class="menu">
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="flores.php">🌸 Gestionar Flores</a></li>
                <li><a href="pedidos.php">📦 Pedidos</a></li>
                <li><a href="usuarios.php">👥 Usuarios</a></li>
                <li><a href="reportes.php">📑 Reportes</a></li>
                <li><a href="../index.php">🏠 Ver Sitio</a></li>
                <li><a href="../logout.php">🚪 Cerrar Sesión</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <div>
                    <h1>📊 Dashboard</h1>
                    <p style="color: #666; margin-top: 0.5rem;">Panel de Control - Flores Online UNAP</p>
                </div>
                <div class="user-info">
                    <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
                    <span class="badge admin">Admin</span>
                </div>
            </div>
            
            <!-- Información de actualización -->
            <div class="actualizar-info">
                🔄 Estadísticas actualizadas en tiempo real | 
                Última actualización: <?php echo date('d/m/Y H:i:s'); ?>
            </div>
            
            <!-- Estadísticas Principales -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon usuarios">👥</div>
                    <div class="stat-info">
                        <h3>Total Clientes</h3>
                        <div class="number"><?php echo number_format($total_usuarios); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon flores">🌸</div>
                    <div class="stat-info">
                        <h3>Flores en Catálogo</h3>
                        <div class="number"><?php echo number_format($total_flores); ?></div>
                        <?php if ($flores_agotadas > 0): ?>
                            <small style="color: #e74c3c;"><?php echo $flores_agotadas; ?> agotadas</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pedidos">📦</div>
                    <div class="stat-info">
                        <h3>Total Pedidos</h3>
                        <div class="number"><?php echo number_format($total_pedidos); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon ventas">💰</div>
                    <div class="stat-info">
                        <h3>Ventas Totales</h3>
                        <div class="number">S/ <?php echo number_format($ventas_totales, 2); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas Detalladas de Ventas -->
            <div class="ventas-detalle">
                <div class="mini-stat">
                    <div class="label">💵 Ventas Hoy</div>
                    <div class="value">S/ <?php echo number_format($ventas_hoy, 2); ?></div>
                    <small style="opacity: 0.8;"><?php echo $pedidos_hoy; ?> pedidos</small>
                </div>
                <div class="mini-stat">
                    <div class="label">📅 Ventas del Mes</div>
                    <div class="value">S/ <?php echo number_format($ventas_mes, 2); ?></div>
                    <small style="opacity: 0.8;"><?php echo $pedidos_mes; ?> pedidos</small>
                </div>
                <div class="mini-stat">
                    <div class="label">📆 Ventas del Año</div>
                    <div class="value">S/ <?php echo number_format($ventas_anio, 2); ?></div>
                </div>
                <div class="mini-stat">
                    <div class="label">⏳ Pedidos Pendientes</div>
                    <div class="value"><?php echo number_format($pedidos_stats['pendientes']); ?></div>
                </div>
            </div>
            
            <!-- Grid con 2 columnas -->
            <div class="grid-2">
                <!-- Últimos Pedidos -->
                <div class="card">
                    <h2>📦 Últimos Pedidos</h2>
                    <?php if (empty($ultimos_pedidos)): ?>
                        <div class="empty-state">
                            <p style="font-size: 3rem;">📭</p>
                            <p>No hay pedidos registrados aún</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimos_pedidos as $pedido): ?>
                                        <tr>
                                            <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></strong><br>
                                                <small style="color: #999;"><?php echo htmlspecialchars($pedido['email']); ?></small>
                                            </td>
                                            <td><strong>S/ <?php echo number_format($pedido['total'], 2); ?></strong></td>
                                            <td><span class="badge <?php echo $pedido['estado']; ?>"><?php echo ucfirst($pedido['estado']); ?></span></td>
                                            <td><small><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Productos Más Vendidos -->
                <div class="card">
                    <h2>🏆 Top 5 Flores Vendidas</h2>
                    <?php if (empty($productos_top)): ?>
                        <div class="empty-state">
                            <p style="font-size: 3rem;">🌸</p>
                            <p>Sin ventas aún</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($productos_top as $index => $producto): ?>
                            <div class="top-productos-item">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-size: 1.5rem; font-weight: bold; color: #667eea;">#<?php echo $index + 1; ?></span>
                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                    </div>
                                    <small style="color: #999;">
                                        <?php echo $producto['total_vendido']; ?> unidades vendidas
                                    </small>
                                </div>
                                <strong style="color: #27ae60;">
                                    S/ <?php echo number_format($producto['ingresos_generados'], 2); ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Resumen de Estados -->
            <div class="card">
                <h2>📊 Estado de Pedidos</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; text-align: center;">
                    <div>
                        <div class="badge pendiente" style="font-size: 2rem; padding: 1rem; display: block; margin-bottom: 0.5rem;">
                            <?php echo $pedidos_stats['pendientes']; ?>
                        </div>
                        <strong>Pendientes</strong>
                    </div>
                    <div>
                        <div class="badge pagado" style="font-size: 2rem; padding: 1rem; display: block; margin-bottom: 0.5rem;">
                            <?php echo $pedidos_stats['pagados']; ?>
                        </div>
                        <strong>Pagados</strong>
                    </div>
                    <div>
                        <div class="badge enviado" style="font-size: 2rem; padding: 1rem; display: block; margin-bottom: 0.5rem;">
                            <?php echo $pedidos_stats['enviados']; ?>
                        </div>
                        <strong>Enviados</strong>
                    </div>
                    <div>
                        <div class="badge entregado" style="font-size: 2rem; padding: 1rem; display: block; margin-bottom: 0.5rem;">
                            <?php echo $pedidos_stats['entregados']; ?>
                        </div>
                        <strong>Entregados</strong>
                    </div>
                    <div>
                        <div class="badge cancelado" style="font-size: 2rem; padding: 1rem; display: block; margin-bottom: 0.5rem;">
                            <?php echo $pedidos_stats['cancelados']; ?>
                        </div>
                        <strong>Cancelados</strong>
                    </div>
                </div>
            </div>
            
            <!-- Accesos Rápidos -->
            <div class="acciones-rapidas">
                <a href="flores.php" class="btn btn-primary">🌸 Gestionar Flores</a>
                <a href="pedidos.php" class="btn btn-primary">📦 Ver Todos los Pedidos</a>
                <a href="reportes.php" class="btn btn-primary">📊 Ver Reportes</a>
                <a href="usuarios.php" class="btn btn-primary">👥 Gestionar Usuarios</a>
            </div>
        </main>
    </div>
</body>
</html>