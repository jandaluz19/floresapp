<?php
/*session_start();
require_once 'config.php';
requerirLogin();

$conn = conectarDB();

// Obtener pedidos del usuario
$stmt = $conn->prepare("
    SELECT p.* 
    FROM pedidos p 
    WHERE p.usuario_id = ? 
    ORDER BY p.fecha_pedido DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Flores Online Unap</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .nav-links a:hover {
            opacity: 0.8;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
            font-weight: bold;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 2rem;
        }
        
        .pedidos-grid {
            display: grid;
            gap: 2rem;
        }
        
        .pedido-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .pedido-numero {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
        }
        
        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .badge.pendiente { background: #fff3cd; color: #856404; }
        .badge.confirmado { background: #d4edda; color: #155724; }
        .badge.enviado { background: #cce5ff; color: #004085; }
        .badge.entregado { background: #d1ecf1; color: #0c5460; }
        .badge.cancelado { background: #f8d7da; color: #721c24; }
        
        .pedido-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
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
        
        .pedido-total {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .pedido-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.3s;
            font-weight: 600;
            display: inline-block;
        }
        
        .btn-boleta {
            background: #667eea;
            color: white;
        }
        
        .btn-comprobante {
            background: #28a745;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .empty-state {
            background: white;
            padding: 4rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .empty-state h2 {
            font-size: 2rem;
            color: #666;
            margin: 2rem 0;
        }
        
        /* Modal 
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .comprobante-img {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                🌸 Flores Online Unap
            </div>
            <div class="nav-links">
                <a href="index.php">Inicio</a>
                <a href="catalogo.php">Catálogo</a>
                <a href="carrito.php">🛒 Carrito</a>
                <a href="mis_pedidos.php">📦 Mis Pedidos</a>
                <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <a href="logout.php" class="btn-primary">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">📦 Mis Pedidos</h1>

        <?php if (empty($pedidos)): ?>
            <div class="empty-state">
                <div style="font-size: 5rem;">📦</div>
                <h2>Aún no tienes pedidos</h2>
                <p style="color: #666; margin: 1rem 0;">Explora nuestro catálogo y realiza tu primera compra</p>
                <a href="catalogo.php" class="btn btn-boleta" style="max-width: 300px; margin: 2rem auto; display: inline-block;">
                    Ver Catálogo
                </a>
            </div>
        <?php else: ?>
            <div class="pedidos-grid">
                <?php foreach ($pedidos as $pedido): 
                    // Obtener items del pedido
                    $stmt = $conn->prepare("
                        SELECT pd.*, f.nombre as flor_nombre 
                        FROM pedido_detalles pd 
                        JOIN flores f ON pd.flor_id = f.id 
                        WHERE pd.pedido_id = ?
                    ");
                    $stmt->execute([$pedido['id']]);
                    $items = $stmt->fetchAll();
                ?>
                    <div class="pedido-card">
                        <div class="pedido-header">
                            <div>
                                <div class="pedido-numero"><?php echo $pedido['numero_pedido']; ?></div>
                                <small style="color: #666;">
                                    <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                </small>
                            </div>
                            <span class="badge <?php echo $pedido['estado']; ?>">
                                <?php 
                                    $estados = [
                                        'pendiente' => '⏳ Pendiente',
                                        'pagado' => '✓ Pagado',
                                        'enviado' => '📦 Enviado',
                                        'entregado' => '✅ Entregado',
                                        'cancelado' => '❌ Cancelado'
                                    ];
                                    echo $estados[$pedido['estado']];
                                ?>
                            </span>
                        </div>
                        
                        <div class="pedido-info">
                            <div class="info-item">
                                <span class="info-label">Total Pagado</span>
                                <span class="pedido-total">S/ <?php echo number_format($pedido['total'], 2); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Método de Pago</span>
                                <span class="info-value"><?php echo $pedido['metodo_pago']; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Productos</span>
                                <span class="info-value"><?php echo count($items); ?> item(s)</span>
                            </div>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                            <strong>Productos:</strong>
                            <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                                <?php foreach ($items as $item): ?>
                                    <li><?php echo $item['cantidad']; ?>x <?php echo htmlspecialchars($item['flor_nombre']); ?> - S/ <?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="pedido-actions">
                            <button onclick="verBoleta(<?php echo $pedido['id']; ?>, '<?php echo $pedido['numero_pedido']; ?>', '<?php echo $pedido['estado']; ?>')" class="btn btn-boleta">
                                📄 Ver Boleta
                            </button>
                            
                            <?php if ($pedido['comprobante_pago']): ?>
                                <button onclick="verComprobante('<?php echo $pedido['comprobante_pago']; ?>', '<?php echo $pedido['numero_pedido']; ?>')" class="btn btn-comprobante">
                                    📷 Ver Comprobante
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para comprobante -->
    <div id="comprobanteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="modalTitle">Comprobante de Pago</h2>
            <img id="comprobanteImg" class="comprobante-img" src="" alt="Comprobante">
        </div>
    </div>

    <script>
        function verBoleta(pedidoId, numeroPedido, estado) {
            // Guardar en sesión y abrir boleta
            window.open('ver_boleta_pedido.php?id=' + pedidoId, '_blank');
        }

        function verComprobante(ruta, numeroPedido) {
            document.getElementById('comprobanteModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Comprobante - ' + numeroPedido;
            document.getElementById('comprobanteImg').src = ruta;
        }

        function cerrarModal() {
            document.getElementById('comprobanteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('comprobanteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

*/


//<?php
session_start();
require_once 'config.php';
requerirLogin();

$conn = conectarDB();

// Obtener pedidos del usuario
$stmt = $conn->prepare("
    SELECT p.* 
    FROM pedidos p 
    WHERE p.usuario_id = ? 
    ORDER BY p.fecha_pedido DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Florería Bella Flora</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .nav-links a:hover {
            opacity: 0.8;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
            font-weight: bold;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 2rem;
        }
        
        .pedidos-grid {
            display: grid;
            gap: 2rem;
        }
        
        .pedido-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .pedido-numero {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
        }
        
        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .badge.pendiente { background: #fff3cd; color: #856404; }
        .badge.pagado { background: #d4edda; color: #155724; }
        .badge.confirmado { background: #d4edda; color: #155724; }
        .badge.enviado { background: #cce5ff; color: #004085; }
        .badge.entregado { background: #d1ecf1; color: #0c5460; }
        .badge.cancelado { background: #f8d7da; color: #721c24; }
        
        .pedido-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
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
        
        .pedido-total {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .pedido-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.3s;
            font-weight: 600;
            display: inline-block;
        }
        
        .btn-boleta {
            background: #667eea;
            color: white;
        }
        
        .btn-comprobante {
            background: #28a745;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .empty-state {
            background: white;
            padding: 4rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .empty-state h2 {
            font-size: 2rem;
            color: #666;
            margin: 2rem 0;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .comprobante-img {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                🌸 Flores Online Unap
            </div>
            <div class="nav-links">
                <a href="index.php">Inicio</a>
                <a href="catalogo.php">Catálogo</a>
                <a href="carrito.php">🛒 Carrito</a>
                <a href="mis_pedidos.php">📦 Mis Pedidos</a>
                <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <a href="logout.php" class="btn-primary">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">📦 Mis Pedidos</h1>

        <?php if (empty($pedidos)): ?>
            <div class="empty-state">
                <div style="font-size: 5rem;">📦</div>
                <h2>Aún no tienes pedidos</h2>
                <p style="color: #666; margin: 1rem 0;">Explora nuestro catálogo y realiza tu primera compra</p>
                <a href="catalogo.php" class="btn btn-boleta" style="max-width: 300px; margin: 2rem auto; display: inline-block;">
                    Ver Catálogo
                </a>
            </div>
        <?php else: ?>
            <div class="pedidos-grid">
                <?php foreach ($pedidos as $pedido): 
                    // Obtener items del pedido
                    $stmt = $conn->prepare("
                        SELECT pd.*, f.nombre as flor_nombre 
                        FROM pedido_detalles pd 
                        JOIN flores f ON pd.flor_id = f.id 
                        WHERE pd.pedido_id = ?
                    ");
                    $stmt->execute([$pedido['id']]);
                    $items = $stmt->fetchAll();
                    
                    // ============================================
                    // SOLUCIÓN: Definir estados con valores por defecto
                    // ============================================
                    $estados = [
                        'pendiente' => '⏳ Pendiente',
                        'pagado' => '✓ Pagado',
                        'confirmado' => '✓ Confirmado',
                        'enviado' => '📦 Enviado',
                        'entregado' => '✅ Entregado',
                        'cancelado' => '❌ Cancelado'
                    ];
                    
                    // Verificar que el estado existe, si no, usar 'pendiente' por defecto
                    $estado_actual = isset($pedido['estado']) && !empty($pedido['estado']) ? $pedido['estado'] : 'pendiente';
                    $estado_texto = isset($estados[$estado_actual]) ? $estados[$estado_actual] : '⏳ Pendiente';
                ?>
                    <div class="pedido-card">
                        <div class="pedido-header">
                            <div>
                                <div class="pedido-numero"><?php echo $pedido['numero_pedido']; ?></div>
                                <small style="color: #666;">
                                    <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                </small>
                            </div>
                            <span class="badge <?php echo $estado_actual; ?>">
                                <?php echo $estado_texto; ?>
                            </span>
                        </div>
                        
                        <div class="pedido-info">
                            <div class="info-item">
                                <span class="info-label">Total Pagado</span>
                                <span class="pedido-total">S/ <?php echo number_format($pedido['total'], 2); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Método de Pago</span>
                                <span class="info-value"><?php echo htmlspecialchars($pedido['metodo_pago']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Productos</span>
                                <span class="info-value"><?php echo count($items); ?> item(s)</span>
                            </div>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                            <strong>Productos:</strong>
                            <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                                <?php foreach ($items as $item): ?>
                                    <li><?php echo $item['cantidad']; ?>x <?php echo htmlspecialchars($item['flor_nombre']); ?> - S/ <?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="pedido-actions">
                            <button onclick="verBoleta(<?php echo $pedido['id']; ?>, '<?php echo $pedido['numero_pedido']; ?>', '<?php echo $estado_actual; ?>')" class="btn btn-boleta">
                                📄 Ver Boleta
                            </button>
                            
                            <?php if ($pedido['comprobante_pago']): ?>
                                <button onclick="verComprobante('<?php echo htmlspecialchars($pedido['comprobante_pago']); ?>', '<?php echo htmlspecialchars($pedido['numero_pedido']); ?>')" class="btn btn-comprobante">
                                    📷 Ver Comprobante
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para comprobante -->
    <div id="comprobanteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="modalTitle">Comprobante de Pago</h2>
            <img id="comprobanteImg" class="comprobante-img" src="" alt="Comprobante">
        </div>
    </div>

    <script>
        function verBoleta(pedidoId, numeroPedido, estado) {
            // Guardar en sesión y abrir boleta
            window.open('ver_boleta_pedido.php?id=' + pedidoId, '_blank');
        }

        function verComprobante(ruta, numeroPedido) {
            document.getElementById('comprobanteModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Comprobante - ' + numeroPedido;
            document.getElementById('comprobanteImg').src = ruta;
        }

        function cerrarModal() {
            document.getElementById('comprobanteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('comprobanteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>