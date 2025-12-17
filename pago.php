<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

$conn = conectarDB();

$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
$total_con_envio = $total + 10;

$pedido_completado = false;
$numero_pedido = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_pago'])) {
    
    $metodo_pago = $_POST['metodo_pago'] ?? 'Yape';
    
    // Verificar que se haya subido un archivo
    if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] != 0) {
        $error_msg = 'Por favor, sube una captura del comprobante de pago';
    } else {
        $archivo = $_FILES['comprobante'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($extension, $extensiones_permitidas)) {
            $error_msg = 'Solo se permiten archivos JPG, PNG o PDF';
        } elseif ($archivo['size'] > 5000000) { // 5MB máximo
            $error_msg = 'El archivo no debe superar los 5MB';
        } else {
            try {
                $conn->beginTransaction();
                
                $numero_pedido = 'PED-' . date('YmdHis') . rand(100, 999);
                
                // Crear directorio si no existe
                if (!file_exists('uploads/comprobantes')) {
                    mkdir('uploads/comprobantes', 0777, true);
                }
                
                // Guardar archivo con nombre único
                $nombre_archivo = $numero_pedido . '_' . time() . '.' . $extension;
                $ruta_destino = 'uploads/comprobantes/' . $nombre_archivo;
                
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    // Insertar pedido como PENDIENTE (ya no se envía correo)
                    $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, numero_pedido, total, estado, metodo_pago, comprobante_pago, fecha_pedido) VALUES (?, ?, ?, 'pendiente', ?, ?, NOW())");
                    $stmt->execute([$_SESSION['usuario_id'], $numero_pedido, $total_con_envio, $metodo_pago, $ruta_destino]);
                    $pedido_id = $conn->lastInsertId();
                    
                    // Insertar detalles del pedido
                    $stmt = $conn->prepare("INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                    foreach ($_SESSION['carrito'] as $item) {
                        $stmt->execute([$pedido_id, $item['id'], $item['cantidad'], $item['precio']]);
                    }
                    
                    $conn->commit();
                    
                    // Guardar datos del pedido en sesión
                    $_SESSION['ultimo_pedido'] = [
                        'id' => $pedido_id,
                        'numero' => $numero_pedido,
                        'fecha' => date('Y-m-d H:i:s'),
                        'total' => $total_con_envio,
                        'subtotal' => $total,
                        'envio' => 10,
                        'items' => $_SESSION['carrito'],
                        'estado' => 'pendiente'
                    ];
                    
                    // Limpiar carrito
                    $_SESSION['carrito'] = [];
                    
                    $pedido_completado = true;
                } else {
                    $error_msg = 'Error al subir el archivo';
                    $conn->rollBack();
                }
            } catch(PDOException $e) {
                $conn->rollBack();
                $error_msg = 'Error al procesar el pedido: ' . $e->getMessage();
            }
        }
    }
}

// Configuración del QR
$numero_yape = "901400564";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago - Flores Online Unap</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .pago-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .logo {
            text-align: center;
            font-size: 2.5rem;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 2rem;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 2rem;
        }
        
        .metodo-pago {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .yape-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .yape-logo {
            font-size: 3rem;
        }
        
        .yape-texto h3 {
            color: #722F8E;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .qr-section {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            margin: 2rem 0;
        }
        
        .qr-image {
            width: 300px;
            height: 300px;
            margin: 0 auto 1rem;
            border-radius: 15px;
        }
        
        .numero-yape {
            background: #722F8E;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            display: inline-block;
            margin: 1rem 0;
        }
        
        .instrucciones {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
        }
        
        .instrucciones h3 {
            color: #856404;
            margin-bottom: 1rem;
        }
        
        .instrucciones ol {
            margin-left: 1.5rem;
            color: #856404;
        }
        
        .instrucciones li {
            margin: 0.5rem 0;
        }
        
        .upload-section {
            background: #e3f2fd;
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
            border-left: 4px solid #2196F3;
        }
        
        .upload-section h3 {
            color: #1565c0;
            margin-bottom: 1rem;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
            margin-top: 1rem;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-label {
            display: block;
            padding: 1.5rem;
            background: white;
            border: 2px dashed #2196F3;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-input-label:hover {
            background: #f5f5f5;
            border-color: #1565c0;
        }
        
        .file-input-label.has-file {
            background: #d4edda;
            border-color: #28a745;
            border-style: solid;
        }
        
        .file-name {
            color: #1565c0;
            font-weight: bold;
            margin-top: 0.5rem;
        }
        
        .resumen-pago {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
        }
        
        .resumen-linea {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 1.1rem;
        }
        
        .resumen-total {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #dee2e6;
            margin-top: 1rem;
        }
        
        .btn-confirmar {
            width: 100%;
            padding: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.3rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            margin-top: 1rem;
        }
        
        .btn-confirmar:hover {
            transform: translateY(-3px);
        }
        
        .btn-confirmar:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-volver {
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
        
        .exito-mensaje {
            background: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin: 2rem 0;
        }
        
        .pendiente-mensaje {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin: 2rem 0;
        }
        
        .exito-icono {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .numero-pedido {
            background: #155724;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.3rem;
            font-weight: bold;
            display: inline-block;
            margin: 1rem 0;
        }
        
        .error-mensaje {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }
        
        .metodos-pago-selector {
            margin-bottom: 3rem;
        }
        
        .metodos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .metodo-card {
            background: white;
            border: 3px solid #e0e0e0;
            border-radius: 15px;
            padding: 2rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .metodo-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }
        
        .metodo-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 100%);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .metodo-card h3 {
            color: #333;
            margin: 1rem 0 0.5rem;
            font-size: 1.2rem;
        }
        
        .metodo-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .metodo-info {
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="pago-card">
            <div class="logo">🌸 Flores Online UNAP</div>

            <?php if ($pedido_completado): ?>
                <div class="exito-mensaje">
                    <div class="exito-icono">✅</div>
                    <h2>¡Comprobante Recibido!</h2>
                    <p style="margin: 1rem 0; font-size: 1.1rem;">Tu comprobante ha sido enviado exitosamente</p>
                    <div class="numero-pedido" style="background: #667eea;">
                        Pedido: <?php echo $numero_pedido; ?>
                    </div>
                    <p style="margin-top: 1rem;">Tu comprobante ha sido enviado. Puedes ver y descargar tu boleta ahora mismo.</p>
                    <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">El administrador verificará tu pago y lo confirmará pronto.</p>
                    
                    <!-- Botón para descargar boleta directamente -->
                    <div style="margin-top: 2rem; padding: 1.5rem; background: #d4edda; border-radius: 10px; border-left: 4px solid #28a745;">
                        <h3 style="color: #155724; margin-bottom: 1rem;">📄 Tu Boleta está Lista</h3>
                        <p style="color: #333; margin-bottom: 1rem;">Descarga tu boleta ahora. Podrás volver a verla en "Mis Pedidos" cuando quieras.</p>
                        <a href="boleta.php" class="btn-boleta" target="_blank" style="background: #28a745; color: white; padding: 1rem 2rem; border-radius: 10px; text-decoration: none; display: inline-block; font-weight: bold; transition: transform 0.3s;">
                            📥 Descargar Boleta
                        </a>
                    </div>
                </div>
                
                <a href="index.php" class="btn-confirmar">
                    Volver al Inicio
                </a>
                <a href="catalogo.php" class="btn-volver">
                    Seguir Comprando
                </a>
            <?php else: ?>
                <h1>Completa tu Pago</h1>

                <?php if ($error_msg): ?>
                    <div class="error-mensaje">✗ <?php echo $error_msg; ?></div>
                <?php endif; ?>

                <!-- Selector de método de pago -->
                <div class="metodos-pago-selector">
                    <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">Selecciona tu Método de Pago</h2>
                    
                    <div class="metodos-grid">
                        <div class="metodo-card" onclick="selectMethod('Yape')" id="method-Yape">
                            <div class="metodo-icon" style="font-size: 3rem;">💜</div>
                            <h3>Yape</h3>
                            <p>Transferencia instantánea</p>
                        </div>
                        
                        <div class="metodo-card" onclick="selectMethod('Visa')" id="method-Visa">
                            <div class="metodo-icon" style="font-size: 3rem;">💳</div>
                            <h3>Visa</h3>
                            <p>Tarjeta de crédito/débito</p>
                        </div>
                        
                        <div class="metodo-card" onclick="selectMethod('Mastercard')" id="method-Mastercard">
                            <div class="metodo-icon" style="font-size: 3rem;">💳</div>
                            <h3>Mastercard</h3>
                            <p>Tarjeta de crédito/débito</p>
                        </div>
                        
                        <div class="metodo-card" onclick="selectMethod('BCP')" id="method-BCP">
                            <div class="metodo-icon" style="font-size: 3rem;">🏦</div>
                            <h3>BCP</h3>
                            <p>Transferencia bancaria</p>
                        </div>
                        
                        <div class="metodo-card" onclick="selectMethod('Interbank')" id="method-Interbank">
                            <div class="metodo-icon" style="font-size: 3rem;">🏦</div>
                            <h3>Interbank</h3>
                            <p>Transferencia bancaria</p>
                        </div>
                    </div>
                </div>

                <!-- Información de pago según método seleccionado -->
                <div id="info-Yape" class="metodo-info" style="display: none;">
                    <div class="metodo-pago">
                    <div class="yape-info">
                        <div class="yape-logo">💜</div>
                        <div class="yape-texto">
                            <h3>Pago con Yape</h3>
                            <p>Método rápido y seguro</p>
                        </div>
                    </div>

                    <div class="qr-section">
                        <h3 style="margin-bottom: 1rem;">Escanea el código QR</h3>
                        
                        <div class="qr-image">
                            <img src="imagenes/qr_yape.jpeg" alt="QR Yape" class="qr-image"">
                                <rect width="300" height="300" fill="white"/>
                                <g fill="#722F8E">
                                    <rect x="20" y="20" width="80" height="80"/>
                                    <rect x="40" y="40" width="40" height="40" fill="white"/>
                                    <rect x="200" y="20" width="80" height="80"/>
                                    <rect x="220" y="40" width="40" height="40" fill="white"/>
                                    <rect x="20" y="200" width="80" height="80"/>
                                    <rect x="40" y="220" width="40" height="40" fill="white"/>
                                    <rect x="120" y="30" width="15" height="15"/>
                                    <rect x="140" y="30" width="15" height="15"/>
                                    <rect x="160" y="30" width="15" height="15"/>
                                    <rect x="120" y="50" width="15" height="15"/>
                                    <rect x="160" y="50" width="15" height="15"/>
                                    <rect x="120" y="70" width="15" height="15"/>
                                    <rect x="140" y="70" width="15" height="15"/>
                                    <rect x="160" y="70" width="15" height="15"/>
                                    <rect x="30" y="120" width="15" height="15"/>
                                    <rect x="50" y="120" width="15" height="15"/>
                                    <rect x="70" y="120" width="15" height="15"/>
                                    <rect x="90" y="120" width="15" height="15"/>
                                    <rect x="110" y="120" width="15" height="15"/>
                                    <rect x="130" y="120" width="15" height="15"/>
                                    <rect x="150" y="120" width="15" height="15"/>
                                    <rect x="170" y="120" width="15" height="15"/>
                                    <rect x="190" y="120" width="15" height="15"/>
                                    <rect x="210" y="120" width="15" height="15"/>
                                    <rect x="230" y="120" width="15" height="15"/>
                                    <rect x="250" y="120" width="15" height="15"/>
                                    <rect x="120" y="140" width="15" height="15"/>
                                    <rect x="160" y="140" width="15" height="15"/>
                                    <rect x="120" y="160" width="15" height="15"/>
                                    <rect x="140" y="160" width="15" height="15"/>
                                    <rect x="160" y="160" width="15" height="15"/>
                                    <rect x="120" y="200" width="15" height="15"/>
                                    <rect x="140" y="200" width="15" height="15"/>
                                    <rect x="160" y="200" width="15" height="15"/>
                                    <rect x="180" y="200" width="15" height="15"/>
                                    <rect x="200" y="200" width="15" height="15"/>
                                    <rect x="220" y="200" width="15" height="15"/>
                                    <rect x="240" y="200" width="15" height="15"/>
                                    <rect x="260" y="200" width="15" height="15"/>
                                    <rect x="120" y="220" width="15" height="15"/>
                                    <rect x="160" y="220" width="15" height="15"/>
                                    <rect x="200" y="220" width="15" height="15"/>
                                    <rect x="240" y="220" width="15" height="15"/>
                                    <rect x="120" y="240" width="15" height="15"/>
                                    <rect x="140" y="240" width="15" height="15"/>
                                    <rect x="160" y="240" width="15" height="15"/>
                                    <rect x="180" y="240" width="15" height="15"/>
                                    <rect x="200" y="240" width="15" height="15"/>
                                    <rect x="220" y="240" width="15" height="15"/>
                                    <rect x="240" y="240" width="15" height="15"/>
                                    <rect x="260" y="240" width="15" height="15"/>
                                </g>
                            </svg>
                        </div>
                        
                        <p style="color: #666;">o yapea al número:</p>
                        <div class="numero-yape"><?php echo $numero_yape; ?></div>
                    </div>
                </div>
                
                <div class="instrucciones">
                    <h3>📱 Instrucciones de Pago:</h3>
                    <ol>
                        <li>Abre tu aplicación Yape</li>
                        <li>Escanea el código QR o ingresa el número</li>
                        <li>Ingresa el monto exacto: <strong>S/ <?php echo number_format($total_con_envio, 2); ?></strong></li>
                        <li>Completa el pago en Yape</li>
                        <li><strong>Toma una captura de pantalla del comprobante</strong></li>
                        <li>Sube la captura abajo y confirma</li>
                    </ol>
                </div>
                </div>

                <!-- Información para Visa -->
                <div id="info-Visa" class="metodo-info" style="display: none;">
                    <div class="metodo-pago">
                        <div class="yape-info">
                            <div class="yape-logo" style="font-size: 4rem;">💳</div>
                            <div class="yape-texto">
                                <h3 style="color: #1A1F71;">Pago con Visa</h3>
                                <p>Transferencia o depósito bancario</p>
                            </div>
                        </div>

                        <div style="background: white; padding: 2rem; border-radius: 15px; margin: 2rem 0;">
                            <h3 style="margin-bottom: 1rem; color: #1A1F71;">Datos de la Cuenta</h3>
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                                <p style="margin: 0.5rem 0;"><strong>Banco:</strong> Cualquier banco</p>
                                <p style="margin: 0.5rem 0;"><strong>Titular:</strong> Flores Online UNAP</p>
                                <p style="margin: 0.5rem 0;"><strong>Cuenta:</strong> 1234-5678-9012-3456</p>
                                <p style="margin: 0.5rem 0;"><strong>CCI:</strong> 002-123-456789012345-67</p>
                                <p style="margin: 1rem 0; color: #667eea; font-size: 1.2rem;"><strong>Monto a transferir: S/ <?php echo number_format($total_con_envio, 2); ?></strong></p>
                            </div>
                        </div>

                        <div class="instrucciones">
                            <h3>💳 Instrucciones:</h3>
                            <ol>
                                <li>Realiza la transferencia desde tu banco</li>
                                <li>Usa los datos de cuenta mostrados arriba</li>
                                <li>Monto exacto: <strong>S/ <?php echo number_format($total_con_envio, 2); ?></strong></li>
                                <li>Toma captura del comprobante</li>
                                <li>Súbela abajo</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Información para Mastercard -->
                <div id="info-Mastercard" class="metodo-info" style="display: none;">
                    <div class="metodo-pago">
                        <div class="yape-info">
                            <div class="yape-logo" style="font-size: 4rem;">💳</div>
                            <div class="yape-texto">
                                <h3 style="color: #EB001B;">Pago con Mastercard</h3>
                                <p>Transferencia o depósito bancario</p>
                            </div>
                        </div>

                        <div style="background: white; padding: 2rem; border-radius: 15px; margin: 2rem 0;">
                            <h3 style="margin-bottom: 1rem; color: #EB001B;">Datos de la Cuenta</h3>
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                                <p style="margin: 0.5rem 0;"><strong>Banco:</strong> Cualquier banco</p>
                                <p style="margin: 0.5rem 0;"><strong>Titular:</strong> Flores Online UNAP</p>
                                <p style="margin: 0.5rem 0;"><strong>Cuenta:</strong> 9876-5432-1098-7654</p>
                                <p style="margin: 0.5rem 0;"><strong>CCI:</strong> 002-987-654321098765-43</p>
                                <p style="margin: 1rem 0; color: #667eea; font-size: 1.2rem;"><strong>Monto a transferir: S/ <?php echo number_format($total_con_envio, 2); ?></strong></p>
                            </div>
                        </div>

                        <div class="instrucciones">
                            <h3>💳 Instrucciones:</h3>
                            <ol>
                                <li>Realiza la transferencia desde tu banco</li>
                                <li>Usa los datos de cuenta mostrados arriba</li>
                                <li>Monto exacto: <strong>S/ <?php echo number_format($total_con_envio, 2); ?></strong></li>
                                <li>Toma captura del comprobante</li>
                                <li>Súbela abajo</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Información para BCP -->
                <div id="info-BCP" class="metodo-info" style="display: none;">
                    <div class="metodo-pago">
                        <div class="yape-info">
                            <div class="yape-logo" style="font-size: 4rem;">🏦</div>
                            <div class="yape-texto">
                                <h3 style="color: #002A8D;">Transferencia BCP</h3>
                                <p>Banco de Crédito del Perú</p>
                            </div>
                        </div>

                        <div style="background: white; padding: 2rem; border-radius: 15px; margin: 2rem 0;">
                            <h3 style="margin-bottom: 1rem; color: #002A8D;">Cuenta BCP</h3>
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                                <p style="margin: 0.5rem 0;"><strong>Banco:</strong> BCP</p>
                                <p style="margin: 0.5rem 0;"><strong>Titular:</strong> Flores Online UNAP</p>
                                <p style="margin: 0.5rem 0;"><strong>Cuenta Corriente:</strong> 191-2345678-9-10</p>
                                <p style="margin: 0.5rem 0;"><strong>CCI:</strong> 002-191-002345678910-11</p>
                                <p style="margin: 1rem 0; color: #667eea; font-size: 1.2rem;"><strong>Monto a transferir: S/ <?php echo number_format($total_con_envio, 2); ?></strong></p>
                            </div>
                        </div>

                        <div class="instrucciones">
                            <h3>🏦 Instrucciones:</h3>
                            <ol>
                                <li>Ingresa a tu Banca por Internet BCP</li>
                                <li>Selecciona "Transferencias a terceros"</li>
                                <li>Usa la cuenta mostrada arriba</li>
                                <li>Monto: <strong>S/ <?php echo number_format($total_con_envio, 2); ?></strong></li>
                                <li>Toma captura del comprobante</li>
                                <li>Súbela abajo</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Información para Interbank -->
                <div id="info-Interbank" class="metodo-info" style="display: none;">
                    <div class="metodo-pago">
                        <div class="yape-info">
                            <div class="yape-logo" style="font-size: 4rem;">🏦</div>
                            <div class="yape-texto">
                                <h3 style="color: #00A9E0;">Transferencia Interbank</h3>
                                <p>Banco Interbank</p>
                            </div>
                        </div>

                        <div style="background: white; padding: 2rem; border-radius: 15px; margin: 2rem 0;">
                            <h3 style="margin-bottom: 1rem; color: #00A9E0;">Cuenta Interbank</h3>
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                                <p style="margin: 0.5rem 0;"><strong>Banco:</strong> Interbank</p>
                                <p style="margin: 0.5rem 0;"><strong>Titular:</strong> Flores Online UNAP</p>
                                <p style="margin: 0.5rem 0;"><strong>Cuenta Corriente:</strong> 200-3001234567</p>
                                <p style="margin: 0.5rem 0;"><strong>CCI:</strong> 003-200-003001234567-88</p>
                                <p style="margin: 1rem 0; color: #667eea; font-size: 1.2rem;"><strong>Monto a transferir: S/ <?php echo number_format($total_con_envio, 2); ?></strong></p>
                            </div>
                        </div>

                        <div class="instrucciones">
                            <h3>🏦 Instrucciones:</h3>
                            <ol>
                                <li>Ingresa a tu Banca por Internet Interbank</li>
                                <li>Selecciona "Transferencias"</li>
                                <li>Usa la cuenta mostrada arriba</li>
                                <li>Monto: <strong>S/ <?php echo number_format($total_con_envio, 2); ?></strong></li>
                                <li>Toma captura del comprobante</li>
                                <li>Súbela abajo</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data" id="pagoForm">
                    <input type="hidden" name="metodo_pago" id="metodo_pago_input" value="">
                    
                    <div class="upload-section">
                        <h3>📸 Sube tu Comprobante de Pago</h3>
                        <p style="color: #1565c0; margin-bottom: 1rem;">
                            Por favor, sube una captura de pantalla de tu pago realizado en Yape
                        </p>
                        
                        <div class="file-input-wrapper">
                            <input type="file" id="comprobante" name="comprobante" accept="image/*,.pdf" required onchange="updateFileName(this)">
                            <label for="comprobante" class="file-input-label" id="fileLabel">
                                <span style="font-size: 3rem;">📤</span>
                                <p style="margin-top: 1rem; font-weight: bold;">Haz clic para seleccionar archivo</p>
                                <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">
                                    Formatos: JPG, PNG, PDF (Máx. 5MB)
                                </p>
                                <p class="file-name" id="fileName"></p>
                            </label>
                        </div>
                    </div>

                    <div class="resumen-pago">
                        <h3 style="margin-bottom: 1rem;">Resumen del Pago:</h3>
                        <div class="resumen-linea">
                            <span>Subtotal productos:</span>
                            <span>S/ <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="resumen-linea">
                            <span>Costo de envío:</span>
                            <span>S/ 10.00</span>
                        </div>
                        <div class="resumen-total">
                            <span>Total a pagar:</span>
                            <span>S/ <?php echo number_format($total_con_envio, 2); ?></span>
                        </div>
                    </div>

                    <button type="submit" name="confirmar_pago" class="btn-confirmar" id="btnConfirmar" disabled>
                        ✓ Confirmar Pago y Enviar Comprobante
                    </button>
                </form>

                <a href="carrito.php" class="btn-volver">
                    ← Volver al Carrito
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let selectedMethod = '';
        
        function selectMethod(method) {
            // Remover selección previa
            document.querySelectorAll('.metodo-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Ocultar todas las infos
            document.querySelectorAll('.metodo-info').forEach(info => {
                info.style.display = 'none';
            });
            
            // Seleccionar nuevo método
            document.getElementById('method-' + method).classList.add('selected');
            document.getElementById('info-' + method).style.display = 'block';
            document.getElementById('metodo_pago_input').value = method;
            
            selectedMethod = method;
            
            // Scroll suave a la información
            document.getElementById('info-' + method).scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        function updateFileName(input) {
            const fileLabel = document.getElementById('fileLabel');
            const fileName = document.getElementById('fileName');
            const btnConfirmar = document.getElementById('btnConfirmar');
            
            if (input.files && input.files[0]) {
                if (!selectedMethod) {
                    alert('Por favor, selecciona primero un método de pago');
                    input.value = '';
                    return;
                }
                
                fileName.textContent = '✓ Archivo: ' + input.files[0].name;
                fileLabel.classList.add('has-file');
                btnConfirmar.disabled = false;
            } else {
                fileName.textContent = '';
                fileLabel.classList.remove('has-file');
                btnConfirmar.disabled = true;
            }
        }
        
        // Validar antes de enviar
        document.getElementById('pagoForm').addEventListener('submit', function(e) {
            if (!selectedMethod) {
                e.preventDefault();
                alert('Por favor, selecciona un método de pago');
                return false;
            }
            
            const fileInput = document.getElementById('comprobante');
            if (!fileInput.files || !fileInput.files[0]) {
                e.preventDefault();
                alert('Por favor, sube el comprobante de pago');
                return false;
            }
        });
    </script>
</body>
</html>