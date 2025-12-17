<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    foreach ($_SESSION['carrito'] as $key => $item) {
        if ($item['id'] == $id_eliminar) {
            unset($_SESSION['carrito'][$key]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
            break;
        }
    }
    header('Location: carrito.php');
    exit;
}

// Actualizar cantidad
if (isset($_POST['actualizar'])) {
    $id_producto = (int)$_POST['id_producto'];
    $nueva_cantidad = max(1, (int)$_POST['cantidad']);
    
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $id_producto) {
            $item['cantidad'] = $nueva_cantidad;
            break;
        }
    }
    header('Location: carrito.php');
    exit;
}

$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Flores online unap</title>
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
        
        .carrito-vacio {
            background: white;
            padding: 4rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .carrito-vacio h2 {
            font-size: 2rem;
            color: #666;
            margin: 2rem 0;
        }
        
        .carrito-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .carrito-items {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .carrito-item {
            display: grid;
            grid-template-columns: 120px 1fr auto auto;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            align-items: center;
        }
        
        .carrito-item:last-child {
            border-bottom: none;
        }
        
        .item-imagen img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .item-info h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .item-precio {
            color: #667eea;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .item-cantidad {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .item-cantidad input {
            width: 60px;
            padding: 0.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            text-align: center;
            font-size: 1rem;
        }
        
        .btn-actualizar {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-eliminar {
            background: #ff4757;
            color: white;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-eliminar:hover {
            background: #e84118;
        }
        
        .resumen {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .resumen h2 {
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .resumen-linea {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            font-size: 1.1rem;
        }
        
        .resumen-total {
            display: flex;
            justify-content: space-between;
            padding: 1.5rem 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #f0f0f0;
            margin-top: 1rem;
        }
        
        .btn-pagar {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            margin-top: 1.5rem;
        }
        
        .btn-pagar:hover {
            transform: translateY(-3px);
        }
        
        .btn-seguir {
            width: 100%;
            padding: 1rem;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        @media (max-width: 968px) {
            .carrito-content {
                grid-template-columns: 1fr;
            }
            
            .carrito-item {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                🌸 FLORES ONLINE UNAP
            </div>
            <div class="nav-links">
                <a href="index.php">Inicio</a>
                <a href="catalogo.php">Catálogo</a>
                <a href="carrito.php">🛒 Carrito</a>
                <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <a href="logout.php" class="btn-primary">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">🛒 Mi Carrito de Compras</h1>

        <?php if (empty($_SESSION['carrito'])): ?>
            <div class="carrito-vacio">
                <div style="font-size: 5rem;">🛒</div>
                <h2>Tu carrito está vacío</h2>
                <p style="color: #666; margin: 1rem 0;">Explora nuestro catálogo y encuentra las flores perfectas para ti</p>
                <a href="catalogo.php" class="btn-pagar" style="max-width: 300px; margin: 2rem auto; display: block;">
                    Ver Catálogo
                </a>
            </div>
        <?php else: ?>
            <div class="carrito-content">
                <div class="carrito-items">
                    <?php foreach ($_SESSION['carrito'] as $item): ?>
                        <div class="carrito-item">
                            <div class="item-imagen">
                                <img src="<?php echo $item['imagen']; ?>" alt="<?php echo $item['nombre']; ?>">
                            </div>
                            
                            <div class="item-info">
                                <h3><?php echo $item['nombre']; ?></h3>
                                <p class="item-precio">S/ <?php echo number_format($item['precio'], 2); ?> c/u</p>
                                <p style="color: #666;">Subtotal: S/ <?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></p>
                            </div>
                            
                            <div class="item-cantidad">
                                <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="hidden" name="id_producto" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" min="1">
                                    <button type="submit" name="actualizar" class="btn-actualizar">Actualizar</button>
                                </form>
                            </div>
                            
                            <div>
                                <a href="carrito.php?eliminar=<?php echo $item['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar este producto?')">
                                    🗑️ Eliminar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="resumen">
                    <h2>Resumen del Pedido</h2>
                    
                    <div class="resumen-linea">
                        <span>Subtotal:</span>
                        <span>S/ <?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="resumen-linea">
                        <span>Envío:</span>
                        <span>S/ 10.00</span>
                    </div>
                    
                    <div class="resumen-total">
                        <span>Total:</span>
                        <span>S/ <?php echo number_format($total + 10, 2); ?></span>
                    </div>
                    
                    <a href="pago.php" class="btn-pagar">
                        Proceder al Pago
                    </a>
                    
                    <a href="catalogo.php" class="btn-seguir">
                        ← Seguir Comprando
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>