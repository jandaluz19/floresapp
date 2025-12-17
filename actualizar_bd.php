<?php
/**
 * Script para actualizar la base de datos
 * Ejecuta este archivo UNA SOLA VEZ desde el navegador
 * Luego ELIMÍNALO por seguridad
 */

require_once 'config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Actualización de Base de Datos</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #667eea; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Actualización de Base de Datos</h1>";

try {
    $conn = conectarDB();
    
    echo "<div class='info'>📋 Iniciando actualización...</div>";
    
    // 1. Actualizar la columna de estado en pedidos
    echo "<h3>1. Actualizando estados de pedidos</h3>";
    try {
        $stmt = $conn->query("ALTER TABLE pedidos MODIFY estado ENUM('pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente'");
        echo "<div class='success'>✅ Columna 'estado' actualizada correctamente</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "<div class='info'>ℹ️ La columna ya está actualizada</div>";
        } else {
            echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
    
    // 1.5 Actualizar columna metodo_pago para soportar múltiples métodos
    echo "<h3>1.5 Actualizando métodos de pago</h3>";
    try {
        $stmt = $conn->query("ALTER TABLE pedidos MODIFY metodo_pago ENUM('Yape', 'Visa', 'Mastercard', 'BCP', 'Interbank') DEFAULT 'Yape'");
        echo "<div class='success'>✅ Métodos de pago actualizados (Yape, Visa, Mastercard, BCP, Interbank)</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "<div class='info'>ℹ️ Los métodos de pago ya están actualizados</div>";
        } else {
            echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
    
    // 2. Actualizar pedidos existentes de 'pagado' a 'confirmado'
    echo "<h3>2. Actualizando pedidos existentes</h3>";
    try {
        $stmt = $conn->query("UPDATE pedidos SET estado = 'confirmado' WHERE estado = 'pagado'");
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "<div class='success'>✅ Se actualizaron $affected pedidos de 'pagado' a 'confirmado'</div>";
        } else {
            echo "<div class='info'>ℹ️ No hay pedidos con estado 'pagado' para actualizar</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // 3. Verificar estructura de flores
    echo "<h3>3. Verificando tabla de flores</h3>";
    try {
        $stmt = $conn->query("DESCRIBE flores");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('imagen', $columns)) {
            echo "<div class='success'>✅ La tabla 'flores' tiene la columna 'imagen'</div>";
            echo "<div class='info'>ℹ️ Las flores son editables. Puedes subir imágenes desde el panel admin.</div>";
        } else {
            echo "<div class='error'>❌ Falta la columna 'imagen' en la tabla flores</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // 4. Verificar carpetas de uploads
    echo "<h3>4. Verificando carpetas de archivos</h3>";
    
    $carpetas = [
        'uploads/comprobantes' => 'Comprobantes de pago',
        'uploads/flores' => 'Imágenes de flores'
    ];
    
    foreach ($carpetas as $carpeta => $descripcion) {
        if (is_dir($carpeta)) {
            if (is_writable($carpeta)) {
                echo "<div class='success'>✅ Carpeta '$carpeta' existe y tiene permisos de escritura</div>";
            } else {
                echo "<div class='error'>❌ Carpeta '$carpeta' existe pero NO tiene permisos de escritura. Ejecuta: chmod 777 $carpeta</div>";
            }
        } else {
            if (mkdir($carpeta, 0777, true)) {
                echo "<div class='success'>✅ Carpeta '$carpeta' creada exitosamente</div>";
            } else {
                echo "<div class='error'>❌ No se pudo crear la carpeta '$carpeta'. Créala manualmente.</div>";
            }
        }
    }
    
    // 5. Resumen final
    echo "<h3>📊 Resumen de Actualización</h3>";
    
    // Contar pedidos por estado
    $stmt = $conn->query("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado");
    $estados = $stmt->fetchAll();
    
    if (count($estados) > 0) {
        echo "<div class='info'><strong>Pedidos en el sistema:</strong><br>";
        foreach ($estados as $estado) {
            echo "- " . ucfirst($estado['estado']) . ": " . $estado['total'] . "<br>";
        }
        echo "</div>";
    }
    
    // Contar flores
    $stmt = $conn->query("SELECT COUNT(*) as total FROM flores");
    $total_flores = $stmt->fetch()['total'];
    echo "<div class='info'><strong>Total de flores:</strong> $total_flores</div>";
    
    // Calcular ventas
    $stmt = $conn->query("SELECT COALESCE(SUM(total), 0) as ventas FROM pedidos WHERE estado IN ('confirmado', 'enviado', 'entregado')");
    $ventas = $stmt->fetch()['ventas'];
    echo "<div class='info'><strong>Ventas totales:</strong> S/ " . number_format($ventas, 2) . "</div>";
    
    echo "<div class='success' style='margin-top: 30px;'>
            <h2>✅ Actualización Completada</h2>
            <p><strong>¿Qué puedes hacer ahora?</strong></p>
            <ul>
                <li>Las flores existentes ya son editables</li>
                <li>Puedes subir imágenes al editar flores</li>
                <li>Los estados ahora son: Pendiente, Confirmado, Enviado, Entregado, Cancelado</li>
                <li>Las ventas se calculan correctamente</li>
                <li>Las boletas ya NO dicen 'provisional'</li>
            </ul>
            <p style='margin-top: 20px;'><strong>⚠️ IMPORTANTE:</strong> Por seguridad, <strong style='color: red;'>ELIMINA este archivo (actualizar_bd.php)</strong> después de ejecutarlo.</p>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='error'><h3>❌ Error Fatal</h3>" . $e->getMessage() . "</div>";
}

echo "
        <div style='text-align: center; margin-top: 30px;'>
            <a href='admin/dashboard.php' style='background: #667eea; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; display: inline-block;'>
                Ir al Dashboard Admin
            </a>
            <a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; display: inline-block; margin-left: 10px;'>
                Ir al Inicio
            </a>
        </div>
    </div>
</body>
</html>";
?>