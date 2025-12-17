<?php
session_start();
require_once '../config.php';
requerirAdmin();

$conn = conectarDB();

// Parámetros de filtro
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mes';
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// Calcular fechas según el período
if ($periodo == 'semana') {
    $fecha_inicio = date('Y-m-d', strtotime('-7 days'));
    $fecha_fin = date('Y-m-d');
} elseif ($periodo == 'mes') {
    $fecha_inicio = date('Y-m-01');
    $fecha_fin = date('Y-m-t');
} elseif ($periodo == 'anio') {
    $fecha_inicio = date('Y-01-01');
    $fecha_fin = date('Y-12-31');
}

// =========================
// REPORTE DETALLADO POR FLORES
// =========================
$stmt = $conn->prepare("
    SELECT 
        f.nombre as flor_nombre,
        c.nombre as categoria_nombre,
        SUM(pd.cantidad) as cantidad_vendida,
        pd.precio_unitario,
        SUM(pd.cantidad * pd.precio_unitario) as total_por_flor
    FROM pedido_detalles pd
    INNER JOIN flores f ON pd.flor_id = f.id
    LEFT JOIN categorias c ON f.categoria_id = c.id
    INNER JOIN pedidos p ON pd.pedido_id = p.id
    WHERE p.estado IN ('pagado', 'enviado', 'entregado')
    AND DATE(p.fecha_pedido) BETWEEN ? AND ?
    GROUP BY f.id, f.nombre, c.nombre, pd.precio_unitario
    ORDER BY total_por_flor DESC
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$flores_vendidas = $stmt->fetchAll();

// =========================
// RESUMEN DE VENTAS
// =========================
$stmt_resumen = $conn->prepare("
    SELECT 
        COUNT(DISTINCT p.id) as total_pedidos,
        COALESCE(SUM(p.total), 0) as ventas_totales,
        COALESCE(AVG(p.total), 0) as ticket_promedio,
        COALESCE(SUM(pd.cantidad), 0) as total_unidades
    FROM pedidos p
    LEFT JOIN pedido_detalles pd ON p.id = pd.pedido_id
    WHERE p.estado IN ('pagado', 'enviado', 'entregado')
    AND DATE(p.fecha_pedido) BETWEEN ? AND ?
");
$stmt_resumen->execute([$fecha_inicio, $fecha_fin]);
$resumen = $stmt_resumen->fetch();

// =========================
// VENTAS POR DÍA
// =========================
$stmt_diario = $conn->prepare("
    SELECT 
        DATE(fecha_pedido) as fecha,
        COUNT(*) as num_pedidos,
        SUM(total) as total_dia
    FROM pedidos
    WHERE estado IN ('pagado', 'enviado', 'entregado')
    AND DATE(fecha_pedido) BETWEEN ? AND ?
    GROUP BY DATE(fecha_pedido)
    ORDER BY fecha DESC
");
$stmt_diario->execute([$fecha_inicio, $fecha_fin]);
$ventas_diarias = $stmt_diario->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas - Flores Online UNAP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 2rem;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 2rem;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        /* Header del Reporte */
        .reporte-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        
        .reporte-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .reporte-header p {
            font-size: 1.2rem;
            opacity: 0.95;
        }
        
        /* Filtros */
        .filtros-card {
            background: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .filtros-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 150px;
        }
        
        .form-group label {
            margin-bottom: 0.5rem;
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .form-group select,
        .form-group input {
            padding: 0.7rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-generar {
            padding: 0.7rem 2rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: background 0.3s;
        }
        
        .btn-generar:hover {
            background: #5568d3;
        }
        
        /* Estadísticas Resumen */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-box {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
        }
        
        .stat-box .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .stat-box .value {
            font-size: 2.2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .stat-box .label {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }
        
        /* Tabla de Flores */
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
            font-size: 1.8rem;
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
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .total-row {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .total-row td {
            color: #667eea;
            border-top: 3px solid #667eea;
            padding: 1.5rem 1rem;
        }
        
        .badge-categoria {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }
        
        .empty-state .icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            font-size: 1.2rem;
        }
        
        /* Botones de Acción */
        .acciones {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }
        
        .btn-imprimir,
        .btn-exportar {
            padding: 1rem 2.5rem;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-imprimir {
            background: #28a745;
            color: white;
        }
        
        .btn-exportar {
            background: #17a2b8;
            color: white;
        }
        
        .btn-imprimir:hover,
        .btn-exportar:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .info-periodo {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 5px;
            color: #0c5aa6;
            font-weight: 500;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                background: white;
                padding: 0;
            }
            
            .card {
                box-shadow: none;
                page-break-inside: avoid;
            }
            
            .stat-box {
                box-shadow: none;
            }
        }
        
        @media (max-width: 768px) {
            .filtros-form {
                flex-direction: column;
            }
            
            .form-group {
                width: 100%;
            }
            
            .acciones {
                flex-direction: column;
            }
            
            .btn-imprimir,
            .btn-exportar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn no-print">⬅ Volver al Dashboard</a>
        
        <!-- Header -->
        <div class="reporte-header">
            <h1>📊 Reporte de Ventas</h1>
            <p>Flores Online UNAP</p>
            <p style="font-size: 1rem; margin-top: 0.5rem;">
                <?php 
                $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                echo "Período: " . date('d/m/Y', strtotime($fecha_inicio)) . " - " . date('d/m/Y', strtotime($fecha_fin)); 
                ?>
            </p>
        </div>
        
        <!-- Filtros -->
        <div class="filtros-card no-print">
            <form method="GET" class="filtros-form">
                <div class="form-group">
                    <label>📅 Período</label>
                    <select name="periodo" id="periodoSelect" onchange="toggleDates(this.value)">
                        <option value="semana" <?php echo $periodo == 'semana' ? 'selected' : ''; ?>>Última Semana</option>
                        <option value="mes" <?php echo $periodo == 'mes' ? 'selected' : ''; ?>>Este Mes</option>
                        <option value="anio" <?php echo $periodo == 'anio' ? 'selected' : ''; ?>>Este Año</option>
                        <option value="personalizado" <?php echo $periodo == 'personalizado' ? 'selected' : ''; ?>>Personalizado</option>
                    </select>
                </div>
                <div class="form-group" id="fecha-inicio-group" style="display: none;">
                    <label>📆 Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="form-group" id="fecha-fin-group" style="display: none;">
                    <label>📆 Fecha Fin</label>
                    <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                </div>
                <button type="submit" class="btn-generar">🔍 Generar Reporte</button>
            </form>
        </div>
        
        <div class="info-periodo no-print">
            ℹ️ <strong>Mostrando datos del:</strong> <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> al <?php echo date('d/m/Y', strtotime($fecha_fin)); ?>
        </div>
        
        <!-- Resumen de Estadísticas -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="icon">📦</div>
                <div class="value"><?php echo number_format($resumen['total_pedidos'] ?: 0); ?></div>
                <div class="label">Pedidos Completados</div>
            </div>
            <div class="stat-box">
                <div class="icon">💰</div>
                <div class="value">S/ <?php echo number_format($resumen['ventas_totales'] ?: 0, 2); ?></div>
                <div class="label">Ventas Totales</div>
            </div>
            <div class="stat-box">
                <div class="icon">📊</div>
                <div class="value">S/ <?php echo number_format($resumen['ticket_promedio'] ?: 0, 2); ?></div>
                <div class="label">Ticket Promedio</div>
            </div>
            <div class="stat-box">
                <div class="icon">🌸</div>
                <div class="value"><?php echo number_format($resumen['total_unidades'] ?: 0); ?></div>
                <div class="label">Unidades Vendidas</div>
            </div>
        </div>
        
        <!-- Detalle de Flores Vendidas -->
        <div class="card">
            <h2>🌺 Detalle de Flores Vendidas</h2>
            
            <?php if (empty($flores_vendidas)): ?>
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <p>No hay ventas registradas en el período seleccionado</p>
                    <p style="margin-top: 1rem; font-size: 0.9rem;">Intenta seleccionar otro rango de fechas</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Flor</th>
                            <th>Categoría</th>
                            <th style="text-align: center;">Cantidad Vendida</th>
                            <th style="text-align: right;">Precio Unitario</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $gran_total = 0;
                        foreach ($flores_vendidas as $flor): 
                            $gran_total += $flor['total_por_flor'];
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($flor['flor_nombre']); ?></strong></td>
                                <td>
                                    <?php if (!empty($flor['categoria_nombre'])): ?>
                                        <span class="badge-categoria"><?php echo htmlspecialchars($flor['categoria_nombre']); ?></span>
                                    <?php else: ?>
                                        <span style="color: #999;">Sin categoría</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;"><strong><?php echo $flor['cantidad_vendida']; ?></strong> unidades</td>
                                <td style="text-align: right;">S/ <?php echo number_format($flor['precio_unitario'], 2); ?></td>
                                <td style="text-align: right;"><strong>S/ <?php echo number_format($flor['total_por_flor'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right;"><strong>TOTAL GENERAL:</strong></td>
                            <td style="text-align: right;"><strong>S/ <?php echo number_format($gran_total, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Ventas por Día -->
        <?php if (!empty($ventas_diarias)): ?>
        <div class="card">
            <h2>📅 Ventas por Día</h2>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th style="text-align: center;">Nº Pedidos</th>
                        <th style="text-align: right;">Total del Día</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    foreach ($ventas_diarias as $dia): 
                        $dia_semana = $dias_semana[date('w', strtotime($dia['fecha']))];
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo $dia_semana; ?></strong><br>
                                <span style="color: #666; font-size: 0.9rem;">
                                    <?php echo date('d/m/Y', strtotime($dia['fecha'])); ?>
                                </span>
                            </td>
                            <td style="text-align: center;"><strong><?php echo $dia['num_pedidos']; ?></strong></td>
                            <td style="text-align: right;"><strong>S/ <?php echo number_format($dia['total_dia'], 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Acciones -->
        <div class="acciones no-print">
            <button onclick="window.print()" class="btn-imprimir">🖨️ Imprimir Reporte</button>
            <button onclick="exportarExcel()" class="btn-exportar">📊 Exportar a Excel</button>
        </div>
        
        <!-- Pie de Página -->
        <div style="text-align: center; margin-top: 3rem; padding: 2rem; color: #666; font-size: 0.9rem; border-top: 2px solid #e0e0e0;">
            <p><strong>Reporte generado el:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            <p style="margin-top: 0.5rem;">🌸 Flores Online UNAP - Sistema de Gestión</p>
        </div>
    </div>

    <script>
        function toggleDates(periodo) {
            const fechaInicio = document.getElementById('fecha-inicio-group');
            const fechaFin = document.getElementById('fecha-fin-group');
            
            if (periodo === 'personalizado') {
                fechaInicio.style.display = 'flex';
                fechaFin.style.display = 'flex';
            } else {
                fechaInicio.style.display = 'none';
                fechaFin.style.display = 'none';
            }
        }
        
        function exportarExcel() {
            const tablas = document.querySelectorAll('table');
            let html = '<html><head><meta charset="UTF-8"><style>table{border-collapse:collapse;width:100%;margin-bottom:20px;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#f2f2f2;}</style></head><body>';
            html += '<h1>Reporte de Ventas - Flores Online UNAP</h1>';
            html += '<p>Período: <?php echo date("d/m/Y", strtotime($fecha_inicio)) . " - " . date("d/m/Y", strtotime($fecha_fin)); ?></p>';
            
            tablas.forEach(tabla => {
                html += tabla.outerHTML;
            });
            
            html += '</body></html>';
            
            const blob = new Blob(['\ufeff' + html], { 
                type: 'application/vnd.ms-excel;charset=utf-8;' 
            });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'reporte_ventas_<?php echo date("Y-m-d"); ?>.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Mostrar campos de fecha si es personalizado al cargar
        window.onload = function() {
            toggleDates('<?php echo $periodo; ?>');
        };
    </script>
</body>
</html>