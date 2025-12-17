<?php
session_start();
require_once 'config.php';

$conn = conectarDB();

// Intentar obtener flores de la base de datos
$stmt = $conn->query("SELECT f.*, c.nombre as categoria_nombre FROM flores f LEFT JOIN categorias c ON f.categoria_id = c.id");
$flores_db = $stmt->fetchAll();

// Si hay flores en la DB, usarlas; si no, usar el array estático
if (count($flores_db) > 0) {
    $todas_flores = $flores_db;
} else {
    // Array estático de respaldo con todas las flores
    $todas_flores = [
        [
            'id' => 1,
            'nombre' => 'Rosas Rojas',
            'precio' => 45.00,
            'imagen' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=800',
            'descripcion' => 'Hermosas rosas rojas frescas, símbolo del amor verdadero',
            'categoria' => 'Rosas',
            'detalles' => 'Ramo de 12 rosas rojas frescas. Perfectas para expresar amor y pasión. Incluye envoltorio elegante y tarjeta personalizada.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 2,
            'nombre' => 'Lirios Blancos',
            'precio' => 38.00,
            'imagen' => 'https://images.unsplash.com/photo-1588423771073-b8ecd0eb7e1f?w=800',
            'descripcion' => 'Elegantes lirios blancos perfectos para ocasiones especiales',
            'categoria' => 'Lirios',
            'detalles' => 'Arreglo de 6 lirios blancos. Ideales para bodas, bautizos y eventos especiales. Fragancia suave y elegante.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 3,
            'nombre' => 'Girasoles',
            'precio' => 32.00,
            'imagen' => 'https://images.unsplash.com/photo-1597848212624-e530d146d08e?w=800',
            'descripcion' => 'Radiantes girasoles amarillos que iluminan cualquier espacio',
            'categoria' => 'Girasoles',
            'detalles' => 'Bouquet de 8 girasoles grandes. Perfectos para alegrar cualquier ambiente. Representan felicidad y energía positiva.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 4,
            'nombre' => 'Tulipanes',
            'precio' => 40.00,
            'imagen' => 'https://images.unsplash.com/photo-1520763185298-1b434c919102?w=800',
            'descripcion' => 'Tulipanes de colores variados, belleza primaveral',
            'categoria' => 'Tulipanes',
            'detalles' => 'Ramo de 15 tulipanes en colores variados. Flores de primavera por excelencia. Frescura y alegría garantizada.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 5,
            'nombre' => 'Orquídeas Moradas',
            'precio' => 65.00,
            'imagen' => 'https://images.unsplash.com/photo-1600984342051-48e00df5b3ca?w=800',
            'descripcion' => 'Exóticas orquídeas moradas, elegancia y sofisticación',
            'categoria' => 'Orquídeas',
            'detalles' => 'Planta de orquídea morada en maceta elegante. Duración de hasta 3 meses con cuidado adecuado. Regalo perfecto.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 6,
            'nombre' => 'Margaritas',
            'precio' => 28.00,
            'imagen' => 'https://images.unsplash.com/photo-1574856344991-aaa31b6f4ce3?w=800',
            'descripcion' => 'Frescas margaritas blancas, simplicidad y pureza',
            'categoria' => 'Margaritas',
            'detalles' => 'Ramo de 20 margaritas blancas. Sencillas pero hermosas. Perfectas para regalos casuales y decoración.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 7,
            'nombre' => 'Claveles Rosados',
            'precio' => 35.00,
            'imagen' => 'https://images.unsplash.com/photo-1591886960571-74d43a9d4166?w=800',
            'descripcion' => 'Delicados claveles rosados con fragancia única',
            'categoria' => 'Claveles',
            'detalles' => 'Bouquet de 15 claveles rosados. Duración prolongada. Aroma característico y agradable.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 8,
            'nombre' => 'Peonías',
            'precio' => 55.00,
            'imagen' => 'https://images.unsplash.com/photo-1525310072745-f49212b5ac6d?w=800',
            'descripcion' => 'Lujosas peonías, flores de ensueño',
            'categoria' => 'Peonías',
            'detalles' => 'Arreglo de 8 peonías rosadas. Flores de lujo con pétalos abundantes. Perfectas para ocasiones especiales.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 9,
            'nombre' => 'Hortensias Azules',
            'precio' => 48.00,
            'imagen' => 'https://images.unsplash.com/photo-1557672172-298e090bd0f1?w=800',
            'descripcion' => 'Impresionantes hortensias azules',
            'categoria' => 'Hortensias',
            'detalles' => 'Arreglo de 3 hortensias azules grandes. Color único y hermoso. Ideal para centros de mesa.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 10,
            'nombre' => 'Azucenas',
            'precio' => 42.00,
            'imagen' => 'https://images.unsplash.com/photo-1586973691398-5d62f48f04db?w=800',
            'descripcion' => 'Puras azucenas blancas con aroma celestial',
            'categoria' => 'Azucenas',
            'detalles' => 'Ramo de 6 azucenas blancas. Fragancia intensa y agradable. Símbolo de pureza y renovación.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 11,
            'nombre' => 'Ranúnculos',
            'precio' => 50.00,
            'imagen' => 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=800',
            'descripcion' => 'Coloridos ranúnculos, pétalos como papel de seda',
            'categoria' => 'Ranúnculos',
            'detalles' => 'Bouquet de 10 ranúnculos multicolores. Pétalos delicados y abundantes. Aspecto romántico y sofisticado.',
            'disponibilidad' => 'En stock'
        ],
        [
            'id' => 12,
            'nombre' => 'Bouquet Mixto',
            'precio' => 60.00,
            'imagen' => 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?w=800',
            'descripcion' => 'Hermoso arreglo con variedad de flores',
            'categoria' => 'Arreglos',
            'detalles' => 'Arreglo premium con rosas, lirios, gerberas y follaje. Combinación perfecta de colores y texturas. Impresionante regalo.',
            'disponibilidad' => 'En stock'
        ]
    ];
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$flor = null;

foreach ($todas_flores as $f) {
    if ($f['id'] == $id) {
        $flor = $f;
        break;
    }
}

if (!$flor) {
    header('Location: catalogo.php');
    exit;
}

$usuario_logueado = isset($_SESSION['usuario']);
$mensaje = '';

// Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_carrito'])) {
    if (!$usuario_logueado) {
        header('Location: login.php');
        exit;
    }
    
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    
    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $flor['id']) {
            $item['cantidad']++;
            $encontrado = true;
            break;
        }
    }
    
    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            'id' => $flor['id'],
            'nombre' => $flor['nombre'],
            'precio' => $flor['precio'],
            'imagen' => $flor['imagen'],
            'cantidad' => 1
        ];
    }
    
    $mensaje = 'Producto agregado al carrito exitosamente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $flor['nombre']; ?> - Flores online unap</title>
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
        
        .btn-primary {
            background: white;
            color: #667eea;
            font-weight: bold;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
        }
        
        .cart-icon {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .breadcrumb {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }
        
        .producto-detalle {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            padding: 3rem;
        }
        
        .producto-imagen img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .producto-info h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .categoria-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .precio {
            font-size: 3rem;
            color: #667eea;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .disponibilidad {
            color: #27ae60;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
        
        .descripcion-larga {
            color: #666;
            line-height: 1.8;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .btn-agregar {
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
        }
        
        .btn-agregar:hover {
            transform: translateY(-3px);
        }
        
        .mensaje-exito {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        }
        
        .info-adicional {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
        
        .info-adicional h3 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .info-adicional ul {
            list-style: none;
        }
        
        .info-adicional li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-adicional li:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 768px) {
            .producto-detalle {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 2rem;
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
                <?php if ($usuario_logueado): ?>
                    <a href="carrito.php" class="cart-icon">
                        🛒
                        <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
                            <span class="cart-count"><?php echo count($_SESSION['carrito']); ?></span>
                        <?php endif; ?>
                    </a>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    <a href="logout.php" class="btn-primary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-primary">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Inicio</a> / <a href="catalogo.php">Catálogo</a> / <?php echo $flor['nombre']; ?>
        </div>

        <div class="producto-detalle">
            <div class="producto-imagen">
                <img src="<?php echo $flor['imagen']; ?>" alt="<?php echo $flor['nombre']; ?>">
            </div>

            <div class="producto-info">
                <?php if ($mensaje): ?>
                    <div class="mensaje-exito">✓ <?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <span class="categoria-badge"><?php echo $flor['categoria']; ?></span>
                <h1><?php echo $flor['nombre']; ?></h1>
                <p class="disponibilidad">✓ <?php echo $flor['disponibilidad']; ?></p>
                
                <div class="precio">S/ <?php echo number_format($flor['precio'], 2); ?></div>
                
                <p class="descripcion-larga"><?php echo $flor['detalles']; ?></p>
                
                <form method="POST">
                    <button type="submit" name="agregar_carrito" class="btn-agregar">
                        🛒 Agregar al Carrito
                    </button>
                </form>
                
                <div class="info-adicional">
                    <h3>Información del Producto</h3>
                    <ul>
                        <li>🚚 Entrega en Puno en 24-48 horas</li>
                        <li>🌿 Flores 100% frescas</li>
                        <li>💳 Pago seguro con Yape</li>
                        <li>📝 Tarjeta personalizada incluida</li>
                        <li>🎁 Envoltorio elegante</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>