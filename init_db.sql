-- =====================================================
-- SCRIPT SQL COMPLETO - FLORES ONLINE UNAP
-- =====================================================

-- 1. CREAR BASE DE DATOS
CREATE DATABASE IF NOT EXISTS floreria_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE floreria_db;

-- =====================================================
-- 2. TABLA USUARIOS
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    rol ENUM('cliente', 'admin') DEFAULT 'cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 3. TABLA CATEGORÍAS
-- =====================================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 4. TABLA FLORES
-- =====================================================
CREATE TABLE IF NOT EXISTS flores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    detalles TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    imagen VARCHAR(500),
    categoria_id INT,
    disponibilidad ENUM('En stock', 'Agotado') DEFAULT 'En stock',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_categoria (categoria_id),
    INDEX idx_disponibilidad (disponibilidad),
    INDEX idx_precio (precio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 5. TABLA PEDIDOS
-- =====================================================
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    metodo_pago VARCHAR(50),
    direccion_envio TEXT,
    notas TEXT,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 6. TABLA PEDIDO_DETALLES
-- =====================================================
CREATE TABLE IF NOT EXISTS pedido_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    flor_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (flor_id) REFERENCES flores(id) ON DELETE CASCADE,
    INDEX idx_pedido (pedido_id),
    INDEX idx_flor (flor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 7. TABLA ESTADISTICAS_VENTAS (OPCIONAL)
-- =====================================================
CREATE TABLE IF NOT EXISTS estadisticas_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL UNIQUE,
    total_dia DECIMAL(10, 2) DEFAULT 0,
    total_mes DECIMAL(10, 2) DEFAULT 0,
    total_anio DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERTAR DATOS DE PRUEBA
-- =====================================================

-- Insertar usuarios
INSERT INTO usuarios (nombre, email, password, telefono, rol) VALUES
('Admin Sistema', 'admin@unap.edu.pe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '951234567', 'admin'),
('Juan Pérez', 'juan@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '951234568', 'cliente'),
('María González', 'maria@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '951234569', 'cliente');

-- Insertar categorías
INSERT INTO categorias (nombre, descripcion) VALUES
('Rosas', 'Flores clásicas para expresar amor y pasión'),
('Lirios', 'Flores elegantes para ocasiones especiales'),
('Girasoles', 'Flores radiantes que transmiten alegría'),
('Tulipanes', 'Flores primaverales de colores vibrantes'),
('Orquídeas', 'Flores exóticas y sofisticadas'),
('Margaritas', 'Flores simples y hermosas'),
('Claveles', 'Flores duraderas con fragancia única'),
('Peonías', 'Flores lujosas con pétalos abundantes'),
('Hortensias', 'Flores grandes con colores impresionantes'),
('Azucenas', 'Flores puras con aroma celestial'),
('Ranúnculos', 'Flores delicadas con pétalos como papel'),
('Arreglos', 'Combinaciones especiales de flores');

-- Insertar flores
INSERT INTO flores (nombre, descripcion, detalles, precio, imagen, categoria_id, disponibilidad) VALUES
('Rosas Rojas', 'Hermosas rosas rojas frescas, símbolo del amor verdadero', 'Ramo de 12 rosas rojas frescas. Perfectas para expresar amor y pasión. Incluye envoltorio elegante y tarjeta personalizada.', 45.00, 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=800', 1, 'En stock'),
('Lirios Blancos', 'Elegantes lirios blancos perfectos para ocasiones especiales', 'Arreglo de 6 lirios blancos. Ideales para bodas, bautizos y eventos especiales. Fragancia suave y elegante.', 38.00, 'https://images.unsplash.com/photo-1588423771073-b8ecd0eb7e1f?w=800', 2, 'En stock'),
('Girasoles', 'Radiantes girasoles amarillos que iluminan cualquier espacio', 'Bouquet de 8 girasoles grandes. Perfectos para alegrar cualquier ambiente. Representan felicidad y energía positiva.', 32.00, 'https://images.unsplash.com/photo-1597848212624-e530d146d08e?w=800', 3, 'En stock'),
('Tulipanes', 'Tulipanes de colores variados, belleza primaveral', 'Ramo de 15 tulipanes en colores variados. Flores de primavera por excelencia. Frescura y alegría garantizada.', 40.00, 'https://images.unsplash.com/photo-1520763185298-1b434c919102?w=800', 4, 'En stock'),
('Orquídeas Moradas', 'Exóticas orquídeas moradas, elegancia y sofisticación', 'Planta de orquídea morada en maceta elegante. Duración de hasta 3 meses con cuidado adecuado. Regalo perfecto.', 65.00, 'https://images.unsplash.com/photo-1600984342051-48e00df5b3ca?w=800', 5, 'En stock'),
('Margaritas', 'Frescas margaritas blancas, simplicidad y pureza', 'Ramo de 20 margaritas blancas. Sencillas pero hermosas. Perfectas para regalos casuales y decoración.', 28.00, 'https://images.unsplash.com/photo-1574856344991-aaa31b6f4ce3?w=800', 6, 'En stock'),
('Claveles Rosados', 'Delicados claveles rosados con fragancia única', 'Bouquet de 15 claveles rosados. Duración prolongada. Aroma característico y agradable.', 35.00, 'https://images.unsplash.com/photo-1591886960571-74d43a9d4166?w=800', 7, 'En stock'),
('Peonías', 'Lujosas peonías, flores de ensueño', 'Arreglo de 8 peonías rosadas. Flores de lujo con pétalos abundantes. Perfectas para ocasiones especiales.', 55.00, 'https://images.unsplash.com/photo-1525310072745-f49212b5ac6d?w=800', 8, 'En stock'),
('Hortensias Azules', 'Impresionantes hortensias azules', 'Arreglo de 3 hortensias azules grandes. Color único y hermoso. Ideal para centros de mesa.', 48.00, 'https://images.unsplash.com/photo-1557672172-298e090bd0f1?w=800', 9, 'En stock'),
('Azucenas', 'Puras azucenas blancas con aroma celestial', 'Ramo de 6 azucenas blancas. Fragancia intensa y agradable. Símbolo de pureza y renovación.', 42.00, 'https://images.unsplash.com/photo-1586973691398-5d62f48f04db?w=800', 10, 'En stock'),
('Ranúnculos', 'Coloridos ranúnculos, pétalos como papel de seda', 'Bouquet de 10 ranúnculos multicolores. Pétalos delicados y abundantes. Aspecto romántico y sofisticado.', 50.00, 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=800', 11, 'En stock'),
('Bouquet Mixto', 'Hermoso arreglo con variedad de flores', 'Arreglo premium con rosas, lirios, gerberas y follaje. Combinación perfecta de colores y texturas. Impresionante regalo.', 60.00, 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?w=800', 12, 'En stock');

-- =====================================================
-- INSERTAR PEDIDOS DE PRUEBA PARA REPORTES
-- =====================================================

-- VENTAS DE NOVIEMBRE 2024
INSERT INTO pedidos (usuario_id, total, estado, metodo_pago, direccion_envio, fecha_pedido) VALUES
(2, 45.00, 'pagado', 'Yape', 'Jr. Lima 123, Puno', '2024-11-05 10:30:00'),
(3, 73.00, 'entregado', 'Yape', 'Av. El Sol 456, Puno', '2024-11-08 14:20:00'),
(2, 110.00, 'pagado', 'Yape', 'Jr. Lima 123, Puno', '2024-11-15 09:15:00'),
(3, 32.00, 'entregado', 'Yape', 'Av. El Sol 456, Puno', '2024-11-20 16:45:00'),
(2, 95.00, 'pagado', 'Yape', 'Jr. Lima 123, Puno', '2024-11-25 11:30:00');

-- VENTAS DE DICIEMBRE 2024 (MES ACTUAL)
INSERT INTO pedidos (usuario_id, total, estado, metodo_pago, direccion_envio, fecha_pedido) VALUES
(2, 90.00, 'pagado', 'Yape', 'Jr. Lima 123, Puno', '2024-12-01 10:00:00'),
(3, 125.00, 'entregado', 'Yape', 'Av. El Sol 456, Puno', '2024-12-03 15:30:00'),
(2, 45.00, 'pagado', 'Yape', 'Jr. Lima 123, Puno', '2024-12-05 09:20:00'),
(3, 180.00, 'entregado', 'Yape', 'Av. El Sol 456, Puno', '2024-12-07 14:10:00'),
(2, 65.00, 'pagado', 'Yape', 'Jr. Lima 123, Puno', '2024-12-10 11:45:00'),
(3, 77.00, 'entregado', 'Yape', 'Av. El Sol 456, Puno', '2024-12-12 16:20:00'),
(2, 150.00, 'pagado', 'Yape', 'Jr. Lima 123, Puno', '2024-12-14 10:30:00'),
(3, 42.00, 'enviado', 'Yape', 'Av. El Sol 456, Puno', '2024-12-15 13:00:00');

-- VENTAS DE ESTA SEMANA (últimos 7 días desde hoy 16/12/2024)
INSERT INTO pedidos (usuario_id, total, estado, metodo_pago, direccion_envio, fecha_pedido) VALUES
(2, 88.00, 'pagado', 'Yape', 'Jr. Lima 123, Puno', '2024-12-16 09:00:00'),
(3, 105.00, 'pagado', 'Yape', 'Av. El Sol 456, Puno', '2024-12-16 14:30:00');

-- =====================================================
-- INSERTAR DETALLES DE PEDIDOS
-- =====================================================

-- Detalles para pedido 1 (Noviembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 1, 45.00, 45.00);

-- Detalles para pedido 2 (Noviembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(2, 1, 1, 45.00, 45.00),
(2, 6, 1, 28.00, 28.00);

-- Detalles para pedido 3 (Noviembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(3, 5, 1, 65.00, 65.00),
(3, 1, 1, 45.00, 45.00);

-- Detalles para pedido 4 (Noviembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(4, 3, 1, 32.00, 32.00);

-- Detalles para pedido 5 (Noviembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(5, 8, 1, 55.00, 55.00),
(5, 4, 1, 40.00, 40.00);

-- Detalles para pedido 6 (Diciembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(6, 1, 2, 45.00, 90.00);

-- Detalles para pedido 7 (Diciembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(7, 5, 1, 65.00, 65.00),
(7, 12, 1, 60.00, 60.00);

-- Detalles para pedido 8 (Diciembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(8, 1, 1, 45.00, 45.00);

-- Detalles para pedido 9 (Diciembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(9, 1, 2, 45.00, 90.00),
(9, 1, 2, 45.00, 90.00);

-- Detalles para pedido 10 (Diciembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(10, 5, 1, 65.00, 65.00);

-- Detalles para pedido 11 (Diciembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(11, 2, 2, 38.00, 76.00),
(11, 1, 1, 45.00, 45.00);

-- Detalles para pedido 12 (Diciembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(12, 8, 2, 55.00, 110.00),
(12, 4, 1, 40.00, 40.00);

-- Detalles para pedido 13 (Diciembre)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(13, 10, 1, 42.00, 42.00);

-- Detalles para pedido 14 (HOY - últimos 7 días)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(14, 9, 1, 48.00, 48.00),
(14, 4, 1, 40.00, 40.00);

-- Detalles para pedido 15 (HOY - últimos 7 días)
INSERT INTO pedido_detalles (pedido_id, flor_id, cantidad, precio_unitario, subtotal) VALUES
(15, 5, 1, 65.00, 65.00),
(15, 4, 1, 40.00, 40.00);

-- =====================================================
-- CONSULTAS PARA VERIFICAR DATOS
-- =====================================================

-- Ver resumen de ventas por mes
SELECT 
    DATE_FORMAT(fecha_pedido, '%Y-%m') as mes,
    COUNT(*) as total_pedidos,
    SUM(total) as ventas_totales
FROM pedidos
WHERE estado IN ('pagado', 'enviado', 'entregado')
GROUP BY DATE_FORMAT(fecha_pedido, '%Y-%m')
ORDER BY mes DESC;

-- Ver flores más vendidas
SELECT 
    f.nombre,
    COUNT(pd.id) as veces_vendida,
    SUM(pd.cantidad) as unidades_vendidas,
    SUM(pd.subtotal) as total_generado
FROM pedido_detalles pd
INNER JOIN flores f ON pd.flor_id = f.id
INNER JOIN pedidos p ON pd.pedido_id = p.id
WHERE p.estado IN ('pagado', 'enviado', 'entregado')
GROUP BY f.id, f.nombre
ORDER BY total_generado DESC;

-- Ver ventas del mes actual (Diciembre 2024)
SELECT 
    COUNT(*) as pedidos,
    SUM(total) as ventas_totales
FROM pedidos
WHERE estado IN ('pagado', 'enviado', 'entregado')
AND YEAR(fecha_pedido) = 2024
AND MONTH(fecha_pedido) = 12;

-- Ver ventas del año 2024
SELECT 
    COUNT(*) as pedidos,
    SUM(total) as ventas_totales
FROM pedidos
WHERE estado IN ('pagado', 'enviado', 'entregado')
AND YEAR(fecha_pedido) = 2024;