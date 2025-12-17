<?php
session_start();
require_once 'config.php';

$error = '';
$registro_exitoso = isset($_GET['registro']) && $_GET['registro'] == 'exitoso';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        try {
            $conn = conectarDB();
            $stmt = $conn->prepare("SELECT id, nombre, email, password, telefono, direccion, rol FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($password, $usuario['password'])) {
                // Guardar toda la información del usuario en la sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['id'] = $usuario['id']; // Alias para compatibilidad
                $_SESSION['usuario'] = $usuario['nombre'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['telefono'] = $usuario['telefono'];
                $_SESSION['direccion'] = $usuario['direccion'];
                $_SESSION['rol'] = $usuario['rol'];
                
                // Redirigir según el rol
                if ($usuario['rol'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Credenciales incorrectas';
            }
        } catch(PDOException $e) {
            $error = 'Error al iniciar sesión: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - FLORES ONLINE UNAP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        
        .login-image {
            background: url('https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=600') center/cover;
            position: relative;
        }
        
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.8) 100%);
        }
        
        .login-image-content {
            position: relative;
            z-index: 1;
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
        
        .login-image-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .login-image-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .login-form {
            padding: 3rem;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-text {
            font-size: 2rem;
            color: #667eea;
            font-weight: bold;
        }
        
        h2 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 0.9rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .links p {
            margin: 0.5rem 0;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .demo-info {
            background: #f0f4ff;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }
        
        .demo-info h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .demo-info p {
            color: #666;
            font-size: 0.9rem;
            margin: 0.3rem 0;
        }
        
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }
            
            .login-image {
                display: none;
            }
            
            .login-form {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <div class="login-image-content">
                <h1>🌸 Bienvenido de vuelta</h1>
                <p>Las flores más hermosas te esperan. Inicia sesión para continuar tu experiencia con nosotros.</p>
            </div>
        </div>
        
        <div class="login-form">
            <div class="logo">
                <div class="logo-text">🌸 FLORES ONLINE UNAP</div>
            </div>
            
            <h2>Iniciar Sesión</h2>
            <p class="subtitle">Ingresa tus credenciales para continuar</p>
            
            <?php if ($registro_exitoso): ?>
                <div class="success">
                    <span>✓</span>
                    <span>¡Registro exitoso! Ahora puedes iniciar sesión con tus credenciales.</span>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error">
                    <span>✗</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="email">📧 Correo Electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="tu@email.com"
                        autocomplete="email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="••••••••"
                        autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn">Iniciar Sesión</button>
            </form>
            
            <div class="links">
                <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
                <p><a href="index.php">← Volver al inicio</a></p>
            </div>
        </div>
    </div>
</body>
</html>