# 🌸 Flores Online UNAP

Sistema web completo de venta de flores en línea desarrollado para la Universidad Nacional del Altiplano - Puno, Perú.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Tecnologías](#-tecnologías-utilizadas)
- [Requisitos Previos](#-requisitos-previos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Funcionalidades](#-funcionalidades-principales)
- [Capturas de Pantalla](#-capturas-de-pantalla)
- [Credenciales de Prueba](#-credenciales-de-prueba)
- [Base de Datos](#-base-de-datos)
- [API y Seguridad](#-api-y-seguridad)
- [Autor](#-autor)
- [Licencia](#-licencia)

---

## ✨ Características

- 🛒 **Carrito de compras** completo con gestión de productos
- 👥 **Sistema de autenticación** con roles (Cliente/Administrador)
- 🌺 **Catálogo de flores** con categorías y filtros
- 📦 **Gestión de pedidos** con seguimiento de estados
- 💳 **Procesamiento de pagos** mediante Yape
- 📊 **Panel administrativo** con estadísticas en tiempo real
- 📈 **Reportes de ventas** personalizables (día/mes/año)
- 🔐 **Seguridad robusta** con validaciones y protección contra inyecciones SQL
- 📱 **Diseño responsive** adaptable a todos los dispositivos
- 🎨 **Interfaz moderna** con gradientes y animaciones

---

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 7.4+** - Lenguaje del lado del servidor
- **MySQL 5.7+** - Sistema de gestión de bases de datos
- **PDO** - Capa de abstracción para base de datos

### Frontend
- **HTML5** - Estructura de las páginas
- **CSS3** - Estilos y diseño responsive
- **JavaScript (Vanilla)** - Interactividad del cliente
- **Gradientes CSS** - Diseño moderno y atractivo

### Servidor
- **Apache 2.4+** - Servidor web
- **XAMPP/WAMP** - Entorno de desarrollo local

---

## 📦 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

```bash
✅ PHP >= 7.4
✅ MySQL >= 5.7
✅ Apache Server >= 2.4
✅ XAMPP o WAMP (recomendado)
✅ Navegador web moderno (Chrome, Firefox, Edge)
```

---

## 🚀 Instalación

### 1️⃣ Clonar el Repositorio

```bash
cd C:\xampp\htdocs
git clone https://github.com/jandaluz19/floresapp.git
```

### 2️⃣ Configurar la Base de Datos

**Opción A: Usando phpMyAdmin**
1. Abre phpMyAdmin (`http://localhost/phpmyadmin`)
2. Crea una nueva base de datos llamada `floreria_db`
3. Importa el archivo SQL:
   - Ve a la pestaña "Importar"
   - Selecciona el archivo `database/floreria_db.sql`
   - Click en "Continuar"

**Opción B: Usando línea de comandos**
```bash
mysql -u root -p
CREATE DATABASE floreria_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE floreria_db;
SOURCE database/floreria_db.sql;
EXIT;
```

### 3️⃣ Configurar Conexión

Edita el archivo `config.php` con tus credenciales:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');          // Tu usuario MySQL
define('DB_PASS', '');              // Tu contraseña MySQL
define('DB_NAME', 'floreria_db');
```

### 4️⃣ Iniciar Servicios

1. Inicia **Apache** y **MySQL** desde XAMPP Control Panel
2. Abre tu navegador y accede a: `http://localhost/floresapp`

---

## ⚙️ Configuración

### Configuración del Sistema

El archivo `config.php` contiene todas las configuraciones principales:

```php
// Conexión a la base de datos
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'floreria_db');

// Funciones de seguridad
function esAdmin() { ... }
function requerirLogin() { ... }
function requerirAdmin() { ... }
```

### Configuración de Sesiones

Las sesiones se gestionan automáticamente al iniciar sesión:

```php
$_SESSION['usuario_id']  // ID del usuario
$_SESSION['usuario']     // Nombre del usuario
$_SESSION['email']       // Email del usuario
$_SESSION['rol']         // Rol: 'cliente' o 'admin'
$_SESSION['telefono']    // Teléfono del usuario
$_SESSION['direccion']   // Dirección del usuario
```

---

## 📁 Estructura del Proyecto

```
floresapp/
│
├── 📄 index.php              # Página principal
├── 📄 login.php              # Inicio de sesión
├── 📄 registro.php           # Registro de usuarios
├── 📄 catalogo.php           # Catálogo de flores
├── 📄 producto.php           # Detalles del producto
├── 📄 carrito.php            # Carrito de compras
├── 📄 checkout.php           # Proceso de pago
├── 📄 mis-pedidos.php        # Historial de pedidos del cliente
├── 📄 logout.php             # Cerrar sesión
├── 📄 config.php             # Configuración del sistema
│
├── 📁 admin/                 # Panel administrativo
│   ├── dashboard.php         # Dashboard con estadísticas
│   ├── flores.php            # Gestión de flores
│   ├── pedidos.php           # Gestión de pedidos
│   ├── usuarios.php          # Gestión de usuarios
│   ├── reporte.php           # Reportes de ventas
│   └── editar_flor.php       # Editar productos
│
├── 📁 uploads/               # Archivos subidos
│   ├── flores/               # Imágenes de flores
│   └── comprobantes/         # Comprobantes de pago
│
├── 📁 database/              # Scripts SQL
│   └── floreria_db.sql       # Estructura y datos de prueba
│
└── 📄 README.md              # Este archivo
```

---

## 🎯 Funcionalidades Principales

### 👤 Para Clientes

#### 🔐 Autenticación
- ✅ Registro de nuevos usuarios con validación
- ✅ Login seguro con contraseñas encriptadas (bcrypt)
- ✅ Recuperación de sesión automática
- ✅ Logout seguro

#### 🛒 Compras
- ✅ Navegación por catálogo de flores
- ✅ Filtrado por categorías
- ✅ Vista detallada de productos
- ✅ Agregar/eliminar productos del carrito
- ✅ Actualizar cantidades en el carrito
- ✅ Proceso de checkout con validación

#### 📦 Pedidos
- ✅ Crear pedidos con método de pago Yape
- ✅ Subir comprobante de pago
- ✅ Ver historial de pedidos
- ✅ Seguimiento de estados (Pendiente → Pagado → Enviado → Entregado)
- ✅ Detalles completos de cada pedido

### 👨‍💼 Para Administradores

#### 📊 Dashboard
- ✅ Estadísticas en tiempo real
  - Total de clientes
  - Flores en catálogo
  - Total de pedidos
  - Ventas totales
- ✅ Ventas del día/mes/año
- ✅ Top 5 flores más vendidas
- ✅ Últimos pedidos registrados
- ✅ Resumen por estados de pedidos

#### 🌸 Gestión de Flores
- ✅ Agregar nuevas flores con imagen
- ✅ Editar información de flores existentes
- ✅ Eliminar flores
- ✅ Gestión de categorías
- ✅ Control de disponibilidad (En stock/Agotado)
- ✅ Vista previa de imágenes

#### 📦 Gestión de Pedidos
- ✅ Lista completa de pedidos
- ✅ Filtros por:
  - Estado (pendiente, pagado, enviado, entregado, cancelado)
  - Fecha (desde/hasta)
  - Búsqueda por cliente
- ✅ Actualización de estados
- ✅ Visualización de comprobantes de pago
- ✅ Estadísticas de ventas

#### 👥 Gestión de Usuarios
- ✅ Ver lista de usuarios registrados
- ✅ Filtrar por rol (cliente/admin)
- ✅ Ver información completa de usuarios
- ✅ Gestión de permisos

#### 📈 Reportes de Ventas
- ✅ Reportes por período:
  - Última semana
  - Este mes
  - Este año
  - Rango personalizado
- ✅ Detalles de flores vendidas
- ✅ Ventas por día
- ✅ Exportar a Excel
- ✅ Imprimir reportes
- ✅ Estadísticas detalladas:
  - Pedidos completados
  - Ventas totales
  - Ticket promedio
  - Unidades vendidas

---

## 🖼️ Capturas de Pantalla

### Página Principal
![Página Principal](screenshots/home.png)

### Catálogo de Flores
![Catálogo](screenshots/catalogo.png)

### Dashboard Administrativo
![Dashboard](screenshots/dashboard.png)

### Panel de Pedidos
![Pedidos](screenshots/pedidos.png)

---

## 🔑 Credenciales de Prueba

### 👨‍💼 Administrador
```
Email: admin@unap.edu.pe
Contraseña: password
```

### 👤 Cliente
```
Email: juan@gmail.com
Contraseña: password
```

**Nota:** Todas las contraseñas por defecto son `password` (hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`)

---

## 🗄️ Base de Datos

### Estructura de Tablas

#### 📊 `usuarios`
Almacena información de clientes y administradores
```sql
- id (INT, PK, AUTO_INCREMENT)
- nombre (VARCHAR 100)
- email (VARCHAR 150, UNIQUE)
- password (VARCHAR 255)
- telefono (VARCHAR 20)
- direccion (TEXT)
- rol (ENUM: 'cliente', 'admin')
- fecha_registro (TIMESTAMP)
```

#### 🌸 `flores`
Catálogo de productos
```sql
- id (INT, PK, AUTO_INCREMENT)
- nombre (VARCHAR 150)
- descripcion (TEXT)
- detalles (TEXT)
- precio (DECIMAL 10,2)
- imagen (VARCHAR 500)
- categoria_id (INT, FK)
- disponibilidad (ENUM: 'En stock', 'Agotado')
- fecha_creacion (TIMESTAMP)
- fecha_actualizacion (TIMESTAMP)
```

#### 🏷️ `categorias`
Categorías de flores
```sql
- id (INT, PK, AUTO_INCREMENT)
- nombre (VARCHAR 100, UNIQUE)
- descripcion (TEXT)
- fecha_creacion (TIMESTAMP)
```

#### 📦 `pedidos`
Órdenes de compra
```sql
- id (INT, PK, AUTO_INCREMENT)
- usuario_id (INT, FK)
- total (DECIMAL 10,2)
- estado (ENUM: 'pendiente', 'pagado', 'enviado', 'entregado', 'cancelado')
- metodo_pago (VARCHAR 50)
- direccion_envio (TEXT)
- notas (TEXT)
- fecha_pedido (TIMESTAMP)
- fecha_actualizacion (TIMESTAMP)
```

#### 📝 `pedido_detalles`
Detalles de productos en cada pedido
```sql
- id (INT, PK, AUTO_INCREMENT)
- pedido_id (INT, FK)
- flor_id (INT, FK)
- cantidad (INT)
- precio_unitario (DECIMAL 10,2)
- subtotal (DECIMAL 10,2)
```

#### 📊 `estadisticas_ventas`
Estadísticas diarias de ventas (opcional)
```sql
- id (INT, PK, AUTO_INCREMENT)
- fecha (DATE, UNIQUE)
- total_dia (DECIMAL 10,2)
- total_mes (DECIMAL 10,2)
- total_anio (DECIMAL 10,2)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Diagrama de Relaciones

```
usuarios (1) ──── (N) pedidos
categorias (1) ──── (N) flores
pedidos (1) ──── (N) pedido_detalles
flores (1) ──── (N) pedido_detalles
```

---

## 🔐 API y Seguridad

### Medidas de Seguridad Implementadas

#### ✅ Protección contra Inyección SQL
- Uso de **PDO con prepared statements**
- Validación de tipos de datos
- Escapado de caracteres especiales

```php
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
```

#### ✅ Protección XSS (Cross-Site Scripting)
- `htmlspecialchars()` en todas las salidas
- Validación de entrada de usuarios

```php
echo htmlspecialchars($usuario['nombre']);
```

#### ✅ Autenticación Segura
- Contraseñas hasheadas con **bcrypt**
- Verificación con `password_verify()`
- Sesiones con tokens únicos

```php
$password_hash = password_hash($password, PASSWORD_DEFAULT);
password_verify($password, $usuario['password']);
```

#### ✅ Control de Acceso
- Funciones de autorización:
  - `requerirLogin()` - Exige estar autenticado
  - `requerirAdmin()` - Exige rol de administrador
  - `esAdmin()` - Verifica si es administrador

#### ✅ Validación de Archivos
- Extensiones permitidas: `jpg, jpeg, png, webp, pdf`
- Límite de tamaño: 5MB
- Validación de tipo MIME
- Nombres de archivo sanitizados

```php
$extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
$tamano_maximo = 5000000; // 5MB
```

#### ✅ Protección CSRF
- Tokens en formularios críticos
- Validación de origen de peticiones

---

## 📊 Funciones Principales

### Gestión de Sesiones

```php
// Verificar si usuario está logueado
requerirLogin();

// Verificar si es administrador
requerirAdmin();

// Verificar rol
if (esAdmin()) {
    // Código para admin
}
```

### Conexión a Base de Datos

```php
$conn = conectarDB();
// Retorna instancia PDO configurada
```

### Obtener Estadísticas

```php
$stats = obtenerEstadisticas($conn);
// Retorna array con ventas_hoy y ventas_mes
```

---

## 🎨 Características de Diseño

### Paleta de Colores

```css
/* Gradiente Principal */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Colores de Estado */
- Pendiente: #fff3cd (Amarillo)
- Pagado: #d4edda (Verde)
- Enviado: #cce5ff (Azul)
- Entregado: #d1ecf1 (Cyan)
- Cancelado: #f8d7da (Rojo)
```

### Tipografía
```css
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
```

### Responsive Design
- 📱 Móviles: < 768px
- 📱 Tablets: 768px - 1024px
- 💻 Desktop: > 1024px

---

## 🚧 Características Futuras (Roadmap)

- [ ] Sistema de cupones y descuentos
- [ ] Notificaciones por email
- [ ] Chat en vivo con clientes
- [ ] Integración con pasarelas de pago (Niubiz, Mercado Pago)
- [ ] Sistema de valoraciones y reseñas
- [ ] Wishlist (lista de deseos)
- [ ] Panel de analytics avanzado
- [ ] API REST para mobile app
- [ ] Modo oscuro

---

## 🐛 Problemas Conocidos

### Solucionados ✅
- ✅ Campo `numero_pedido` inexistente en tabla pedidos
- ✅ Columna `total_ventas` no encontrada en estadísticas
- ✅ Sidebar del dashboard con posicionamiento incorrecto
- ✅ Validación de estados de pedidos

### En Desarrollo 🔄
- Optimización de carga de imágenes
- Mejora en reportes con gráficos

---

## 📝 Notas de Desarrollo

### Configuración de Desarrollo

```bash
# Variables de entorno recomendadas
PHP_VERSION=7.4+
MYSQL_VERSION=5.7+
APACHE_VERSION=2.4+

# Extensiones PHP requeridas
- PDO
- PDO_MySQL
- mbstring
- openssl
```

### Comandos Útiles

```bash
# Iniciar servicios XAMPP
sudo /opt/lampp/lampp start

# Ver logs de Apache
tail -f /opt/lampp/logs/error_log

# Backup de base de datos
mysqldump -u root -p floreria_db > backup.sql

# Restaurar base de datos
mysql -u root -p floreria_db < backup.sql
```

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Para contribuir:

1. Haz un Fork del proyecto
2. Crea una rama para tu característica (`git checkout -b feature/NuevaCaracteristica`)
3. Commit tus cambios (`git commit -m 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/NuevaCaracteristica`)
5. Abre un Pull Request

---

## 📜 Licencia

Este proyecto es de código abierto y está disponible bajo la [Licencia MIT](LICENSE).

---

## 👨‍💻 Autor

**Jandaluz19**
- GitHub: [@jandaluz19](https://github.com/jandaluz19)
- Proyecto: [FloresApp](https://github.com/jandaluz19/floresapp)

---

## 🙏 Agradecimientos

- Universidad Nacional del Altiplano - Puno
- Comunidad de PHP y MySQL
- Unsplash por las imágenes de flores

---

## 📞 Soporte

¿Necesitas ayuda? 

- 📧 Email: [Crear un issue en GitHub](https://github.com/jandaluz19/floresapp/issues)
- 📚 Documentación: [Wiki del proyecto](https://github.com/jandaluz19/floresapp/wiki)
- 🐛 Reportar bug: [Issues](https://github.com/jandaluz19/floresapp/issues/new)

---

<div align="center">

### ⭐ Si este proyecto te ayudó, dale una estrella en GitHub

**Hecho con ❤️ y 🌸 en Puno, Perú**

![Flores Online UNAP](https://img.shields.io/badge/Flores-Online%20UNAP-purple?style=for-the-badge)

</div>