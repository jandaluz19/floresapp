<?php
// =====================================
// ⚙️ CONFIGURACIÓN DE LA BASE DE DATOS
// =====================================

define('DB_HOST', '127.0.0.1');  // o 'localhost'
define('DB_PORT', '3306');        // cambia a 3307 si tu MySQL usa otro puerto
define('DB_USER', 'root');        // usuario por defecto en XAMPP
define('DB_PASS', '');            // sin contraseña por defecto
define('DB_NAME', 'floreria_db'); // 👈 tu base de datos actual

// =====================================
// 🔗 CONEXIÓN PDO
// =====================================
function conectarDB() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        die("<strong>Error de conexión:</strong> " . $e->getMessage());
    }
}

// =====================================
// 🧍‍♂️ FUNCIONES DE SESIÓN / PERMISOS
// =====================================

// Verifica si el usuario tiene rol admin
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

// Exige que haya un usuario logueado
function requerirLogin() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
}

// Exige que el usuario sea administrador
function requerirAdmin() {
    requerirLogin();
    if (!esAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// =============================
// 📊 FUNCIONES DE ESTADÍSTICAS
// =============================

// Registrar una venta cuando un pedido pasa a "pagado"
function registrarVenta($conn, $monto) {
    $fecha = date('Y-m-d');

    // Insertar o actualizar el día
    $stmt = $conn->prepare("
        INSERT INTO estadisticas_ventas (fecha, total_dia, total_mes, total_anio)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            total_dia = total_dia + VALUES(total_dia),
            total_mes = total_mes + VALUES(total_mes),
            total_anio = total_anio + VALUES(total_anio)
    ");

    $stmt->execute([
        $fecha,
        $monto,
        $monto,
        $monto
    ]);
}

// Obtener estadísticas para el dashboard
function obtenerEstadisticas($conn) {
    $hoy = date('Y-m-d');
    $mes = date('Y-m');
    
    // Ventas del día
    $dia = $conn->prepare("SELECT SUM(total) AS total FROM pedidos WHERE estado='pagado' AND DATE(fecha_pedido)=?");
    $dia->execute([$hoy]);
    $ventas_hoy = $dia->fetchColumn() ?: 0;

    // Ventas del mes
    $mesStmt = $conn->prepare("SELECT SUM(total) AS total FROM pedidos WHERE estado='pagado' AND DATE_FORMAT(fecha_pedido,'%Y-%m')=?");
    $mesStmt->execute([$mes]);
    $ventas_mes = $mesStmt->fetchColumn() ?: 0;

    return [
        'ventas_hoy' => $ventas_hoy,
        'ventas_mes' => $ventas_mes
    ];
}

?>


