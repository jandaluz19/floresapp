<?php
session_start();
require_once 'config.php';

$conn = conectarDB();

// Obtener flores destacadas (últimas 6)
$stmt = $conn->query("SELECT * FROM flores ORDER BY id DESC LIMIT 6");
$flores_destacadas = $stmt->fetchAll();

$usuario_logueado = isset($_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - FLORES ONLINE UNAP</title>
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
        }
        
        /* Header */
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
        
        /* ============================================
           HERO SECTION CON BUSCADOR
        ============================================ */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6rem 2rem;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        
        /* Buscador en Hero */
        .search-hero {
            max-width: 700px;
            margin: 2rem auto;
        }
        
        .search-box {
            display: flex;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .search-box input {
            flex: 1;
            padding: 1.2rem 1.5rem;
            border: none;
            font-size: 1.1rem;
            outline: none;
        }
        
        .search-box button {
            background: #ff4757;
            color: white;
            border: none;
            padding: 1.2rem 2.5rem;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
            transition: background 0.3s;
        }
        
        .search-box button:hover {
            background: #ff3838;
        }
        
        .btn-hero {
            background: white;
            color: #667eea;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            display: inline-block;
            margin-top: 1rem;
            transition: transform 0.3s;
        }
        
        .btn-hero:hover {
            transform: translateY(-3px);
        }
        
        /* Contenedor */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 3rem;
        }
        
        /* Características */
        .caracteristicas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .caracteristica-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .caracteristica-card:hover {
            transform: translateY(-10px);
        }
        
        .caracteristica-card .icono {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .caracteristica-card h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        /* Grid de Flores */
        .flores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .flor-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .flor-card:hover {
            transform: translateY(-10px);
        }
        
        .flor-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .flor-info {
            padding: 1.5rem;
        }
        
        .flor-nombre {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .flor-precio {
            font-size: 1.5rem;
            color: #667eea;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .btn-ver {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: block;
            text-align: center;
            padding: 0.8rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        
        .btn-catalogo {
            text-align: center;
            margin: 3rem 0;
        }
        
        /* Footer */
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
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
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    <a href="logout.php" class="btn-primary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-primary">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>🌸 Bienvenido a Flores Online UNAP</h1>
        <p>Las flores más frescas y hermosas de Puno, entregadas con amor</p>
        
        <!-- ============================================
             BUSCADOR EN INDEX
        ============================================ -->
        <div class="search-hero">
            <form method="GET" action="catalogo.php" class="search-box">
                <input type="text" name="busqueda" 
                       placeholder="¿Qué flores estás buscando?..." 
                       required>
                <button type="submit">🔍 Buscar</button>
            </form>
        </div>
        
        <a href="catalogo.php" class="btn-hero">Ver Catálogo Completo →</a>
    </section>

    <div class="container">
        <!-- Características -->
        <div class="caracteristicas">
            <div class="caracteristica-card">
                <div class="icono">🌺</div>
                <h3>Flores Frescas</h3>
                <p>Flores de la más alta calidad, frescas todos los días</p>
            </div>
            <div class="caracteristica-card">
                <div class="icono">🚚</div>
                <h3>Envío Rápido</h3>
                <p>Entrega el mismo día en toda la ciudad de Puno</p>
            </div>
            <div class="caracteristica-card">
                <div class="icono">💳</div>
                <h3>Pago Seguro</h3>
                <p>Múltiples opciones de pago, 100% seguro</p>
            </div>
        </div>

        <!-- Flores Destacadas -->
        <h2 class="section-title">🌟 Flores Destacadas</h2>
        <div class="flores-grid">
            <?php foreach ($flores_destacadas as $flor): ?>
                <div class="flor-card">
                    <img src="<?php echo htmlspecialchars($flor['imagen']); ?>" 
                         alt="<?php echo htmlspecialchars($flor['nombre']); ?>" 
                         class="flor-img"
                         onerror="this.src='https://via.placeholder.com/300x250?text=Sin+Imagen'">
                    <div class="flor-info">
                        <div class="flor-nombre"><?php echo htmlspecialchars($flor['nombre']); ?></div>
                        <div class="flor-precio">S/ <?php echo number_format($flor['precio'], 2); ?></div>
                        <a href="detalle.php?id=<?php echo $flor['id']; ?>" class="btn-ver">Ver Detalles</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="btn-catalogo">
            <a href="catalogo.php" class="btn-hero">Ver Todas las Flores →</a>
        </div>
    </div>

    
    <!-- Botón flotante de WhatsApp -->
    <a href="https://wa.me/51901400564?text=Hola,%20me%20interesa%20comprar%20flores%20🌸" target="_blank" class="whatsapp-float" title="Chatea con nosotros">
        💬
    </a>

    <footer>
        <div style="margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Síguenos en Redes Sociales</h3>
            <div style="display: flex; gap: 1rem; justify-content: center; align-items: center;">
                <a href="https://www.facebook.com/TuPagina" target="_blank" style="color: white; font-size: 2rem; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                    📘
                </a>
                <a href="https://wa.me/51901400564?text=Hola,%20me%20interesa%20comprar%20flores" target="_blank" style="color: white; font-size: 2rem; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                    💬
                </a>
                <a href="https://www.instagram.com/TuPagina" target="_blank" style="color: white; font-size: 2rem; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                    📷
                </a>
                <a href="https://www.tiktok.com/@TuPagina" target="_blank" style="color: white; font-size: 2rem; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                    🎵
                </a>
            </div>
        </div>
        <p>&copy; 2025 Flores online unap - Derechos reservados</p>
        <p>Puno, Perú 🇵🇪 | 📞 (01) 234-5678 | 📧 ventas@floresonlineunap.com</p>
    </footer>
</body>
</html>