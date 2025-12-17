<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Los campos nombre, email y contraseña son obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $password_confirm) {
        $error = 'Las contraseñas no coinciden';
    } elseif (!empty($telefono) && !preg_match('/^[0-9]{9,15}$/', $telefono)) {
        $error = 'El teléfono debe tener entre 9 y 15 dígitos';
    } else {
        try {
            $conn = conectarDB();
            
            // Verificar si el email ya existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Este email ya está registrado. <a href="login.php">Inicia sesión aquí</a>';
            } else {
                // Insertar nuevo usuario
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("
                    INSERT INTO usuarios (nombre, email, password, telefono, direccion, rol, fecha_registro) 
                    VALUES (?, ?, ?, ?, ?, 'cliente', NOW())
                ");
                
                $stmt->execute([
                    $nombre, 
                    $email, 
                    $password_hash,
                    $telefono ?: null,
                    $direccion ?: null
                ]);
                
                // Redirigir al login con mensaje de éxito
                header('Location: login.php?registro=exitoso');
                exit;
            }
        } catch(PDOException $e) {
            $error = 'Error al registrar usuario: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - FLORES ONLINE UNAP</title>
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
        
        .registro-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 1100px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        
        .registro-image {
            background: url('https://images.unsplash.com/photo-1563241527-3004b7be0ffd?w=600') center/cover;
            position: relative;
        }
        
        .registro-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.8) 100%);
        }
        
        .registro-image-content {
            position: relative;
            z-index: 1;
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
        
        .registro-image-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .registro-image-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .registro-image-content ul {
            margin-top: 1.5rem;
            list-style: none;
        }
        
        .registro-image-content li {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }
        
        .registro-image-content li::before {
            content: '✓';
            position: absolute;
            left: 0;
            font-weight: bold;
        }
        
        .registro-form {
            padding: 3rem;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
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
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .required {
            color: #e74c3c;
        }
        
        input, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .helper-text {
            font-size: 0.85rem;
            color: #999;
            margin-top: 0.3rem;
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
            margin-top: 1rem;
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
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .error a {
            color: #c33;
            font-weight: bold;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .registro-container {
                grid-template-columns: 1fr;
            }
            
            .registro-image {
                display: none;
            }
            
            .registro-form {
                padding: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="registro-container">
        <div class="registro-image">
            <div class="registro-image-content">
                <h1>🌸 Únete a nosotros</h1>
                <p>Crea tu cuenta y disfruta de todos los beneficios:</p>
                <ul>
                    <li>Compra flores frescas y hermosas</li>
                    <li>Seguimiento de tus pedidos</li>
                    <li>Historial de compras</li>
                    <li>Ofertas exclusivas</li>
                    <li>Envío rápido en Puno</li>
                </ul>
            </div>
        </div>
        
        <div class="registro-form">
            <div class="logo">
                <div class="logo-text">🌸 FLORES ONLINE UNAP</div>
            </div>
            
            <h2>Crear cuenta</h2>
            <p class="subtitle">Complete el formulario para registrarse</p>
            
            <?php if ($error): ?>
                <div class="error">
                    <span>✗</span>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="nombre">👤 Nombre Completo <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        required 
                        placeholder="Ej: Juan Pérez García"
                        autocomplete="name"
                        value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">📧 Correo Electrónico <span class="required">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="tu@email.com"
                        autocomplete="email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="helper-text">Usarás este email para iniciar sesión</div>
                </div>
                
                <div class="form-group">
                    <label for="telefono">📱 Teléfono</label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        placeholder="951234567"
                        autocomplete="tel"
                        pattern="[0-9]{9,15}"
                        value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                    <div class="helper-text">Para contactarte sobre tus pedidos (opcional)</div>
                </div>
                
                <div class="form-group">
                    <label for="direccion">📍 Dirección de Entrega</label>
                    <textarea 
                        id="direccion" 
                        name="direccion" 
                        placeholder="Ej: Jr. Lima 123, Puno"
                        autocomplete="street-address"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
                    <div class="helper-text">Donde quieres recibir tus pedidos (opcional)</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">🔒 Contraseña <span class="required">*</span></label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder="Mínimo 6 caracteres"
                            autocomplete="new-password"
                            minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">🔒 Confirmar Contraseña <span class="required">*</span></label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            required 
                            placeholder="Repite tu contraseña"
                            autocomplete="new-password"
                            minlength="6">
                    </div>
                </div>
                
                <button type="submit" class="btn">Crear mi Cuenta</button>
            </form>
            
            <div class="links">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                <p><a href="index.php">← Volver al inicio</a></p>
            </div>
        </div>
    </div>

    <script>
        // Validar que las contraseñas coincidan en tiempo real
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirm');

        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    </script>
</body>
</html>