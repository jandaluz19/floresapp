<?php
session_start();
require_once 'config.php';

$conn = conectarDB();

// ============================================
// PARÁMETROS DE BÚSQUEDA Y FILTROS
// ============================================
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$ocasion = isset($_GET['ocasion']) ? $_GET['ocasion'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'id DESC';

// Construir consulta SQL
$sql = "SELECT * FROM flores WHERE 1=1";
$params = [];

// Filtro de búsqueda
if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
}

// Filtro por categoría
if (!empty($categoria)) {
    $sql .= " AND categoria = ?";
    $params[] = $categoria;
}

// Filtro por ocasión
if (!empty($ocasion)) {
    $sql .= " AND ocasion LIKE ?";
    $ocasion_param = "%{$ocasion}%";
    $params[] = $ocasion_param;
}

// Validar y aplicar ordenamiento seguro (evitar inyección SQL)
$allowed_orders = [
    'id DESC' => 'id DESC',
    'precio_asc' => 'precio ASC',
    'precio_desc' => 'precio DESC',
    'nombre' => 'nombre ASC'
];
$orden_sql = $allowed_orders[$orden] ?? 'id DESC';

$sql .= " ORDER BY {$orden_sql}";

// Ejecutar consulta principal
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$flores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías disponibles desde la BD (solo las que existen en la tabla)
$stmt_cat = $conn->query("SELECT DISTINCT categoria FROM flores WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
$categorias_en_db = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

// ============================================
// DEFINIR CATEGORÍAS PREDEFINIDAS (para el sidebar)
// ============================================
$categorias = [
    '' => ['nombre' => 'Todas las Flores', 'icono' => '🌸', 'color' => '#667eea'],
    'Rosas' => ['nombre' => 'Rosas', 'icono' => '🌹', 'color' => '#e91e63'],
    'Lirios' => ['nombre' => 'Lirios', 'icono' => '🌺', 'color' => '#9c27b0'],
    'Tulipanes' => ['nombre' => 'Tulipanes', 'icono' => '🌷', 'color' => '#f48fb1'],
    'Orquídeas' => ['nombre' => 'Orquídeas', 'icono' => '🪷', 'color' => '#ab47bc'],
    'Girasoles' => ['nombre' => 'Girasoles', 'icono' => '🌻', 'color' => '#ffc107'],
    'Margaritas' => ['nombre' => 'Margaritas', 'icono' => '🌼', 'color' => '#fff176']
];

// Filtrar solo las categorías que realmente existen en la BD (opcional, para limpieza)
// Puedes omitir esto si quieres mostrar todas aunque no haya flores aún
$categorias_disponibles = ['' => $categorias['']];
foreach ($categorias as $key => $data) {
    if ($key === '') continue;
    if (in_array($key, $categorias_en_db)) {
        $categorias_disponibles[$key] = $data;
    }
}
// Si prefieres mostrar TODAS las categorías del array (aunque no haya flores), usa:
// $categorias_disponibles = $categorias;

$ocasiones = [
    'cumpleanos' => ['nombre' => 'Cumpleaños', 'icono' => '🎂', 'color' => '#FF6B9D'],
    'parejas' => ['nombre' => 'Parejas', 'icono' => '❤️', 'color' => '#E91E63'],
    'aniversario' => ['nombre' => 'Aniversario', 'icono' => '💑', 'color' => '#C2185B'],
    'agradecimiento' => ['nombre' => 'Gracias', 'icono' => '🙏', 'color' => '#F48FB1'],
    'graduacion' => ['nombre' => 'Graduación', 'icono' => '🎓', 'color' => '#AB47BC'],
    'condolencias' => ['nombre' => 'Condolencias', 'icono' => '🕊️', 'color' => '#757575']
];

$usuario_logueado = isset($_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - FLORES ONLINE UNAP</title>
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
        
        /* HEADER Y NAVEGACIÓN */
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
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
        
        /* HERO SECTION */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        
        .search-container {
            max-width: 600px;
            margin: 2rem auto 0;
        }
        
        .search-box {
            display: flex;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .search-box input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            font-size: 1rem;
            outline: none;
        }
        
        .search-box button {
            background: #ff4757;
            color: white;
            border: none;
            padding: 1rem 2rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .search-box button:hover {
            background: #ff3838;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* MENÚ DE OCASIONES */
        .ocasiones-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .ocasiones-section h2 {
            text-align: center;
            color: #667eea;
            margin-bottom: 1.5rem;
        }
        
        .ocasiones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .ocasion-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            transition: transform 0.3s;
            cursor: pointer;
        }
        
        .ocasion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .ocasion-card .icono {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .ocasion-card .nombre {
            font-weight: bold;
            color: white;
        }
        
        /* LAYOUT PRINCIPAL */
        .main-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
        }
        
        /* SIDEBAR DE CATEGORÍAS */
        .sidebar-categorias {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            height: fit-content;
            position: sticky;
            top: 100px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-categorias h3 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .categoria-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .categoria-item:hover {
            background: #f0f0f0;
            transform: translateX(5px);
        }
        
        .categoria-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .categoria-item .icono {
            font-size: 1.5rem;
        }
        
        /* CONTENIDO PRINCIPAL */
        .contenido-principal {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filtros-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .resultados-info {
            color: #666;
        }
        
        .ordenar-select {
            padding: 0.6rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
        }
        
        /* GRID DE FLORES */
        .flores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        .flor-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .flor-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .flor-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .flor-info {
            padding: 1.5rem;
        }
        
        .flor-categoria {
            color: #667eea;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .flor-nombre {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .flor-descripcion {
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        
        .flor-precio {
            font-size: 1.8rem;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .btn-ver {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: block;
            text-align: center;
            width: 100%;
            padding: 0.8rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s;
        }
        
        .btn-ver:hover {
            transform: scale(1.05);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem;
            color: #999;
        }
        
        .empty-state .icono {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar-categorias {
                position: relative;
                top: 0;
            }
            
            .ocasiones-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo">🌸 FLORES ONLINE UNAP</div>
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
                    <a href="mis_pedidos.php">📦 Pedidos</a>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="logout.php" class="btn-primary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-primary">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">🌺 Encuentra las Flores Perfectas</h1>
        <p style="opacity: 0.9;">Explora nuestro catálogo completo de flores frescas</p>
        
        <div class="search-container">
            <form method="GET" action="" class="search-box">
                <input type="text" name="busqueda" 
                       value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>" 
                       placeholder="Buscar flores por nombre o descripción...">
                <button type="submit">🔍 Buscar</button>
            </form>
        </div>
    </div>

    <div class="container">
        <!-- MENÚ DE OCASIONES -->
        <div class="ocasiones-section">
            <h2>🎉 Compra por Ocasión</h2>
            <div class="ocasiones-grid">
                <?php foreach ($ocasiones as $key => $oca): ?>
                    <a href="?ocasion=<?php echo urlencode($key); ?>" class="ocasion-card" 
                       style="background: <?php echo htmlspecialchars($oca['color'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="icono"><?php echo $oca['icono']; ?></div>
                        <div class="nombre"><?php echo htmlspecialchars($oca['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Layout Principal -->
        <div class="main-layout">
            <!-- SIDEBAR DE CATEGORÍAS -->
            <aside class="sidebar-categorias">
                <h3>📂 Categorías</h3>
                <?php foreach ($categorias_disponibles as $key => $cat): ?>
                    <a href="?categoria=<?php echo urlencode($key); ?>" 
                       class="categoria-item <?php echo ($categoria === $key) ? 'active' : ''; ?>">
                        <span class="icono"><?php echo $cat['icono']; ?></span>
                        <span><?php echo htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php endforeach; ?>
            </aside>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="contenido-principal">
                <div class="filtros-toolbar">
                    <div class="resultados-info">
                        <strong><?php echo count($flores); ?></strong> flores encontradas
                        <?php if ($busqueda): ?>
                            para "<em><?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?></em>"
                        <?php endif; ?>
                        <?php if ($categoria): ?>
                            en <em><?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?></em>
                        <?php endif; ?>
                        <?php if ($ocasion): ?>
                            para <em><?php echo htmlspecialchars($ocasion, ENT_QUOTES, 'UTF-8'); ?></em>
                        <?php endif; ?>
                        
                        <?php if ($busqueda || $categoria || $ocasion): ?>
                            <a href="catalogo.php" style="color: #667eea; margin-left: 1rem;">✕ Limpiar filtros</a>
                        <?php endif; ?>
                    </div>
                    
                    <form method="GET" style="display: inline;">
                        <?php if ($busqueda): ?>
                            <input type="hidden" name="busqueda" value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <?php if ($categoria): ?>
                            <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <?php if ($ocasion): ?>
                            <input type="hidden" name="ocasion" value="<?php echo htmlspecialchars($ocasion, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <select name="orden" class="ordenar-select" onchange="this.form.submit()">
                            <option value="id DESC" <?php echo $orden == 'id DESC' ? 'selected' : ''; ?>>Más recientes</option>
                            <option value="precio_asc" <?php echo $orden == 'precio_asc' ? 'selected' : ''; ?>>Precio: Menor a Mayor</option>
                            <option value="precio_desc" <?php echo $orden == 'precio_desc' ? 'selected' : ''; ?>>Precio: Mayor a Menor</option>
                            <option value="nombre" <?php echo $orden == 'nombre' ? 'selected' : ''; ?>>Nombre A-Z</option>
                        </select>
                    </form>
                </div>

                <!-- Grid de Flores -->
                <?php if (empty($flores)): ?>
                    <div class="empty-state">
                        <div class="icono">🔍</div>
                        <h2>No se encontraron flores</h2>
                        <p>Intenta con otros criterios de búsqueda</p>
                        <a href="catalogo.php" class="btn-ver" style="max-width: 300px; margin: 2rem auto; display: inline-block;">
                            Ver todas las flores
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flores-grid">
                        <?php foreach ($flores as $flor): ?>
                            <div class="flor-card" onclick="window.location.href='detalle.php?id=<?php echo (int)$flor['id']; ?>'">
                                <img src="<?php echo htmlspecialchars($flor['imagen'] ?? 'https://via.placeholder.com/300x250?text=Sin+Imagen', ENT_QUOTES, 'UTF-8'); ?>" 
                                     alt="<?php echo htmlspecialchars($flor['nombre'] ?? 'Flor', ENT_QUOTES, 'UTF-8'); ?>" 
                                     class="flor-img">
                                <div class="flor-info">
                                    <?php if (!empty($flor['categoria'])): ?>
                                        <div class="flor-categoria"><?php echo htmlspecialchars($flor['categoria'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                    <div class="flor-nombre"><?php echo htmlspecialchars($flor['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="flor-descripcion">
                                        <?php 
                                        $desc = htmlspecialchars($flor['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
                                        echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc;
                                        ?>
                                    </div>
                                    <div class="flor-precio">S/ <?php echo number_format($flor['precio'] ?? 0, 2); ?></div>
                                    <a href="detalle.php?id=<?php echo (int)$flor['id']; ?>" class="btn-ver">Ver Detalles</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 FLORES ONLINE UNAP</p>
        <p>Puno, Perú 🇵🇪</p>
    </footer>
</body>
</html>