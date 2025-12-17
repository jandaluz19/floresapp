<?php
session_start();
require_once '../config.php';
requerirAdmin();

$conn = conectarDB();
$mensaje = '';

$id = (int)$_GET['id'];

// Obtener datos de la flor
$stmt = $conn->prepare("SELECT * FROM flores WHERE id = ?");
$stmt->execute([$id]);
$flor = $stmt->fetch();

if (!$flor) {
    header('Location: flores.php');
    exit;
}

// Actualizar flor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $detalles = trim($_POST['detalles']);
    $precio = (float)$_POST['precio'];
    $categoria_id = (int)$_POST['categoria_id'];
    $disponibilidad = $_POST['disponibilidad'];
    
    // Procesar imagen si se subió una nueva
    $imagen_ruta = $flor['imagen']; // Mantener imagen actual por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($extension, $extensiones_permitidas) && $archivo['size'] <= 5000000) {
            if (!file_exists('../uploads/flores')) {
                mkdir('../uploads/flores', 0777, true);
            }
            
            $nombre_archivo = 'flor_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $ruta_destino = '../uploads/flores/' . $nombre_archivo;
            
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                // Eliminar imagen anterior si existe y no es URL externa
                if (!empty($flor['imagen']) && strpos($flor['imagen'], 'http') === false && file_exists('../' . $flor['imagen'])) {
                    @unlink('../' . $flor['imagen']);
                }
                $imagen_ruta = 'uploads/flores/' . $nombre_archivo;
            }
        }
    } elseif (isset($_POST['imagen_url']) && !empty($_POST['imagen_url'])) {
        // Si se proporcionó una URL y no se subió archivo
        $imagen_ruta = trim($_POST['imagen_url']);
    }
    
    try {
        $stmt = $conn->prepare("UPDATE flores SET nombre = ?, descripcion = ?, detalles = ?, precio = ?, imagen = ?, categoria_id = ?, disponibilidad = ? WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $detalles, $precio, $imagen_ruta, $categoria_id, $disponibilidad, $id]);
        $mensaje = 'Flor actualizada exitosamente. Precio: S/ ' . number_format($precio, 2);
        
        // Recargar datos actualizados
        $stmt = $conn->prepare("SELECT * FROM flores WHERE id = ?");
        $stmt->execute([$id]);
        $flor = $stmt->fetch();
    } catch(PDOException $e) {
        $mensaje = 'Error al actualizar la flor: ' . $e->getMessage();
    }
}

// Obtener categorías
$stmt = $conn->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Flor - Admin</title>
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
            max-width: 1000px;
        }
        
        .header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        }
        
        .preview {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .preview img {
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
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
                <h1>✏️ Editar Flor</h1>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="mensaje">✓ <?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="preview">
                    <img src="../<?php echo $flor['imagen']; ?>" alt="<?php echo $flor['nombre']; ?>" onerror="this.src='https://via.placeholder.com/400x400?text=Imagen+No+Disponible'">
                    <p style="color: #666; margin-top: 1rem;">Vista previa de la imagen actual</p>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($flor['nombre']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Precio (S/)</label>
                            <input type="number" step="0.01" name="precio" value="<?php echo $flor['precio']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria_id" required>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $flor['categoria_id'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Disponibilidad</label>
                            <select name="disponibilidad" required>
                                <option value="En stock" <?php echo $flor['disponibilidad'] == 'En stock' ? 'selected' : ''; ?>>En stock</option>
                                <option value="Agotado" <?php echo $flor['disponibilidad'] == 'Agotado' ? 'selected' : ''; ?>>Agotado</option>
                            </select>
                        </div>
                        
                        <div class="form-group full">
                            <label>🖼️ Cambiar Imagen (Opcional)</label>
                            <input type="file" name="imagen" accept="image/*" onchange="previewNewImage(this)">
                            <small style="color: #666;">JPG, PNG o WEBP - Máx. 5MB. Deja vacío para mantener la imagen actual</small>
                            <div id="newPreview" style="margin-top: 1rem;"></div>
                        </div>
                        
                        <div class="form-group full">
                            <label>O ingresa nueva URL de imagen (Opcional)</label>
                            <input type="url" name="imagen_url" placeholder="https://...">
                            <small style="color: #666;">Solo se usa si no subes un archivo</small>
                        </div>
                        
                        <div class="form-group full">
                            <label>Descripción Corta</label>
                            <textarea name="descripcion" required><?php echo htmlspecialchars($flor['descripcion']); ?></textarea>
                        </div>
                        
                        <div class="form-group full">
                            <label>Detalles Completos</label>
                            <textarea name="detalles" required><?php echo htmlspecialchars($flor['detalles']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                        <a href="flores.php" class="btn btn-secondary">← Volver</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        function previewNewImage(input) {
            const preview = document.getElementById('newPreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<p style="color: #28a745; font-weight: bold;">Nueva imagen:</p><img src="' + e.target.result + '" style="max-width: 300px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
</body>
</html>