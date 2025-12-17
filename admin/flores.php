<?php
session_start();
require_once '../config.php';
requerirAdmin();

$conn = conectarDB();
$mensaje = '';

// Eliminar flor
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    try {
        $stmt = $conn->prepare("DELETE FROM flores WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = 'Flor eliminada exitosamente';
    } catch(PDOException $e) {
        $mensaje = 'Error al eliminar la flor';
    }
}

// Agregar flor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $detalles = trim($_POST['detalles']);
    $precio = (float)$_POST['precio'];
    $categoria_id = (int)$_POST['categoria_id'];
    $disponibilidad = $_POST['disponibilidad'];
    
    // Procesar imagen subida
    $imagen_ruta = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($extension, $extensiones_permitidas) && $archivo['size'] <= 5000000) {
            // Crear directorio si no existe
            if (!file_exists('uploads/flores')) {
                mkdir('uploads/flores', 0777, true);
            }
            
            $nombre_archivo = 'flor_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $ruta_destino = 'uploads/flores/' . $nombre_archivo;
            
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                $imagen_ruta = $ruta_destino;
            }
        }
    }
    
    // Si no se subió imagen, usar URL por defecto
    if (empty($imagen_ruta)) {
        $imagen_ruta = isset($_POST['imagen_url']) && !empty($_POST['imagen_url']) ? trim($_POST['imagen_url']) : 'https://via.placeholder.com/400x400?text=Flor';
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO flores (nombre, descripcion, detalles, precio, imagen, categoria_id, disponibilidad) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $detalles, $precio, $imagen_ruta, $categoria_id, $disponibilidad]);
        $mensaje = 'Flor agregada exitosamente';
    } catch(PDOException $e) {
        $mensaje = 'Error al agregar la flor';
    }
}

// Obtener todas las flores
$stmt = $conn->query("SELECT f.*, c.nombre as categoria_nombre FROM flores f LEFT JOIN categorias c ON f.categoria_id = c.id ORDER BY f.id DESC");
$flores = $stmt->fetchAll();

// Obtener categorías
$stmt = $conn->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Flores - Admin</title>
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
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
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
            padding: 2rem;
        }
        
        .header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .mensaje {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
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
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
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
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-danger {
            background: #ff4757;
            color: white;
        }
        
        .btn-warning {
            background: #ffa502;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .flores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .flor-card {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e0e0e0;
        }
        
        .flor-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .flor-info {
            padding: 1rem;
        }
        
        .flor-nombre {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .flor-precio {
            font-size: 1.5rem;
            color: #667eea;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        
        .flor-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge.stock { background: #d4edda; color: #155724; }
        .badge.agotado { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">🌸 Admin Panel</div>
            <ul class="menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="flores.php" class="active">🌸 Gestionar Flores</a></li>
                <li><a href="pedidos.php">📦 Pedidos</a></li>
                <li><a href="usuarios.php">👥 Usuarios</a></li>
                <li><a href="../index.php">🏠 Ver Sitio</a></li>
                <li><a href="../logout.php">🚪 Cerrar Sesión</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Gestionar Flores</h1>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="mensaje">✓ <?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <h2>➕ Agregar Nueva Flor</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Precio (S/)</label>
                            <input type="number" step="0.01" name="precio" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria_id" required>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nombre']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Disponibilidad</label>
                            <select name="disponibilidad" required>
                                <option value="En stock">En stock</option>
                                <option value="Agotado">Agotado</option>
                            </select>
                        </div>
                        
                        <div class="form-group full">
                            <label>URL de la Imagen</label>
                            <input type="url" name="imagen" placeholder="https://..." required>
                        </div>
                        
                        <div class="form-group full">
                            <label>Descripción Corta</label>
                            <textarea name="descripcion" required></textarea>
                        </div>
                        
                        <div class="form-group full">
                            <label>Detalles Completos</label>
                            <textarea name="detalles" required></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" name="agregar" class="btn btn-primary">Agregar Flor</button>
                </form>
            </div>
            
            <div class="card">
                <h2>🌸 Flores Registradas (<?php echo count($flores); ?>)</h2>
                <div class="flores-grid">
                    <?php foreach ($flores as $flor): ?>
                        <div class="flor-card">
                            <img src="<?php echo $flor['imagen']; ?>" alt="<?php echo $flor['nombre']; ?>" class="flor-img">
                            <div class="flor-info">
                                <div class="flor-nombre"><?php echo htmlspecialchars($flor['nombre']); ?></div>
                                <span class="badge"><?php echo $flor['categoria_nombre']; ?></span>
                                <span class="badge <?php echo $flor['disponibilidad'] == 'En stock' ? 'stock' : 'agotado'; ?>">
                                    <?php echo $flor['disponibilidad']; ?>
                                </span>
                                <div class="flor-precio">S/ <?php echo number_format($flor['precio'], 2); ?></div>
                                <p style="color: #666; font-size: 0.9rem; margin-top: 0.5rem;">
                                    <?php echo substr($flor['descripcion'], 0, 80); ?>...
                                </p>
                                <div class="flor-actions">
                                    <a href="editar_flor.php?id=<?php echo $flor['id']; ?>" class="btn btn-warning">✏️ Editar</a>
                                    <a href="flores.php?eliminar=<?php echo $flor['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('¿Eliminar esta flor?')">🗑️ Eliminar</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>