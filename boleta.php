<?php
session_start();

if (!isset($_SESSION['ultimo_pedido'])) {
    header('Location: index.php');
    exit;
}

$pedido = $_SESSION['ultimo_pedido'];
// Ya no verificamos si es provisional - siempre es la boleta final
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Venta - <?php echo $pedido['numero']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 2rem;
        }
        
        .boleta {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 3rem;
            border: 2px solid #333;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .empresa-info {
            font-size: 0.9rem;
            color: #666;
        }
        
        .boleta-tipo {
            background: #333;
            color: white;
            padding: 0.5rem 1rem;
            display: inline-block;
            margin-top: 1rem;
            font-weight: bold;
        }
        
        .info-destacada {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
            color: #1565c0;
        }
        
        .info-section {
            margin: 1.5rem 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .label {
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            margin: 2rem 0;
            border-collapse: collapse;
        }
        
        .items-table th {
            background: #333;
            color: white;
            padding: 0.8rem;
            text-align: left;
        }
        
        .items-table td {
            padding: 0.8rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .totales {
            margin-top: 2rem;
            text-align: right;
        }
        
        .total-row {
            padding: 0.5rem 0;
            font-size: 1.1rem;
        }
        
        .total-final {
            font-size: 1.5rem;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 2px dashed #333;
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-imprimir {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin: 1rem 0;
        }
        
        .btn-imprimir:hover {
            background: #5568d3;
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
            
            .boleta {
                border: none;
                box-shadow: none;
            }
        }
        
        .qr-code {
            text-align: center;
            margin: 2rem 0;
        }
        
        .qr-code img {
            width: 150px;
            height: 150px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-imprimir">🖨️ Imprimir Boleta</button>
        <button onclick="window.close()" class="btn-imprimir" style="background: #6c757d;">← Cerrar</button>
    </div>

    <div class="boleta">
        <div class="header">
            <div class="logo">🌸 FLORES ONLINE UNAP</div>
            <div class="empresa-info">
                RUC: 20123456789<br>
                Av. Sesquicentenario 1150, Puno - Perú<br>
                Tel: (051) 123-4567 | Email: ventas@floresonlineunap.com
            </div>
            <div class="boleta-tipo">COMPROBANTE DE COMPRA</div>
        </div>
        
        <div class="info-destacada">
            <strong>📋 Comprobante de Compra Válido</strong><br>
            Este documento confirma tu pedido. Conserva este comprobante para cualquier consulta.
        </div>
        
        <div class="info-section">
            <div class="info-row">
                <span class="label">Nº Boleta:</span>
                <span><?php echo $pedido['numero']; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Fecha de Emisión:</span>
                <span><?php echo date('d/m/Y H:i:s', strtotime($pedido['fecha'])); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Cliente:</span>
                <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Estado del Pedido:</span>
                <span style="color: #2196F3; font-weight: bold;">
                    <?php 
                    $estados = [
                        'pendiente' => '⏳ En Verificación',
                        'confirmado' => '✓ Confirmado',
                        'enviado' => '📦 Enviado',
                        'entregado' => '✅ Entregado'
                    ];
                    echo isset($pedido['estado']) && isset($estados[$pedido['estado']]) 
                        ? $estados[$pedido['estado']] 
                        : '⏳ En Verificación';
                    ?>
                </span>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Cant.</th>
                    <th>Descripción</th>
                    <th>P. Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedido['items'] as $item): ?>
                    <tr>
                        <td><?php echo $item['cantidad']; ?></td>
                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                        <td>S/ <?php echo number_format($item['precio'], 2); ?></td>
                        <td>S/ <?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="totales">
            <div class="total-row">
                <span>Subtotal: </span>
                <strong>S/ <?php echo number_format($pedido['subtotal'], 2); ?></strong>
            </div>
            <div class="total-row">
                <span>Envío: </span>
                <strong>S/ <?php echo number_format($pedido['envio'], 2); ?></strong>
            </div>
            <div class="total-final">
                <span>TOTAL A PAGAR: </span>
                <strong>S/ <?php echo number_format($pedido['total'], 2); ?></strong>
            </div>
        </div>
        
        <div class="qr-code">
            <p style="margin-bottom: 0.5rem; font-size: 0.9rem; color: #666;">Código de verificación</p>
            <svg width="150" height="150" viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
                <rect width="150" height="150" fill="white"/>
                <g fill="#333">
                    <rect x="10" y="10" width="40" height="40"/>
                    <rect x="20" y="20" width="20" height="20" fill="white"/>
                    <rect x="100" y="10" width="40" height="40"/>
                    <rect x="110" y="20" width="20" height="20" fill="white"/>
                    <rect x="10" y="100" width="40" height="40"/>
                    <rect x="20" y="110" width="20" height="20" fill="white"/>
                    <rect x="60" y="15" width="8" height="8"/>
                    <rect x="70" y="15" width="8" height="8"/>
                    <rect x="80" y="15" width="8" height="8"/>
                    <rect x="60" y="60" width="8" height="8"/>
                    <rect x="70" y="60" width="8" height="8"/>
                    <rect x="80" y="60" width="8" height="8"/>
                    <rect x="100" y="60" width="8" height="8"/>
                    <rect x="110" y="60" width="8" height="8"/>
                    <rect x="120" y="60" width="8" height="8"/>
                    <rect x="130" y="60" width="8" height="8"/>
                    <rect x="60" y="100" width="8" height="8"/>
                    <rect x="80" y="100" width="8" height="8"/>
                    <rect x="100" y="100" width="8" height="8"/>
                    <rect x="120" y="100" width="8" height="8"/>
                </g>
            </svg>
        </div>
        
        <div class="footer">
            <p><strong>¡Gracias por su compra!</strong></p>
            <p>Comprobante válido de compra</p>
            <p>Para consultas: ventas@floresonlineunap.com</p>
            <p style="margin-top: 1rem;">---</p>
            <p>🌸 Flores frescas con garantía | Entrega en 24-48 horas</p>
            <p>Síguenos en nuestras redes sociales</p>
        </div>
    </div>
</body>
</html>



