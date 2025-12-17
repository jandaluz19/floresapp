<?php
session_start();
require_once '../config.php';
requerirAdmin();

$conn = conectarDB();
$mensaje = '';

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if ($id != $_SESSION['usuario_id']) { // No permitir que el admin se elimine a sí mismo
        try {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'usuario'");
            $stmt->execute([$id]);
            $mensaje = 'Usuario eliminado exitosamente';
        } catch(PDOException $e) {
            $mensaje = 'Error al eliminar el usuario';
        }
    } else {
        $mensaje = 'No puedes eliminarte a ti mismo';
    }
}

// Obtener todos los usuarios
$stmt = $conn->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Admin</title>
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
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge.admin { background: #667eea; color: white; }
        .badge.usuario { background: #e3f2fd; color: #1565c0; }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: transform 0.3s;
            font-weight: 600;
        }
        
        .btn-danger {
            background: #ff4757;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
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
                <li><a href="pedidos.php">📦 Pedidos</a></li>
                <li><a href="usuarios.php" class="active">👥 Usuarios</a></li>
                <li><a href="../index.php">🏠 Ver Sitio</a></li>
                <li><a href="../logout.php">🚪 Cerrar Sesión</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>👥 Gestionar Usuarios</h1>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="mensaje">✓ <?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <h2 style="margin-bottom: 1.5rem;">Usuarios Registrados (<?php echo count($usuarios); ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $usuario['rol']; ?>">
                                        <?php echo ucfirst($usuario['rol']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></td>
                                <td>
                                    <?php if ($usuario['id'] != $_SESSION['usuario_id'] && $usuario['rol'] == 'usuario'): ?>
                                        <a href="usuarios.php?eliminar=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('¿Eliminar este usuario?')">
                                            🗑️ Eliminar
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>