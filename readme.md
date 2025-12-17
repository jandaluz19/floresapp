# 🌸 FLORES ONLINE UNAP - Sistema Completo

Sistema completo de tienda en línea de flores con panel de administración.

## 📋 Características

### Para Usuarios:
- ✅ Registro e inicio de sesión
- 🌸 Catálogo de flores con búsqueda
- 🛒 Carrito de compras funcional
- 💳 Sistema de pago con Yape (QR personalizable)
- 📄 **Boleta de compra imprimible**
- 📦 Historial de pedidos guardados en BD
- 💬 **Integración con redes sociales**
- 📱 **Botón flotante de WhatsApp**

### Para Administradores:
- 📊 Dashboard con estadísticas
- 🌺 Gestión completa de flores (CRUD)
- 📦 Gestión de pedidos y estados
- 👥 Gestión de usuarios
- ✏️ Edición de productos en tiempo real

## 🚀 Instalación

### Requisitos:
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- PHPMyAdmin (recomendado)

### Pasos de Instalación:

1. **Crear la Base de Datos**
   - Abre PHPMyAdmin
   - Crea una nueva base de datos llamada `floreria_db`
   - Importa el archivo `database.sql` o ejecuta el código SQL manualmente

2. **Configurar la Conexión**
   - Abre el archivo `config.php`
   - Ajusta las credenciales si es necesario:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'floreria_db');
   ```

3. **Estructura de Archivos**
   ```
   proyecto/
   ├── config.php
   ├── index.php
   ├── login.php
   ├── registro.php
   ├── catalogo.php
   ├── detalle.php
   ├── carrito.php
   ├── pago.php
   ├── logout.php
   ├── database.sql
   └── admin/
       ├── dashboard.php
       ├── flores.php
       ├── editar_flor.php
       ├── pedidos.php
       └── usuarios.php
   ```

4. **Crear Carpeta Admin**
   - Crea una carpeta llamada `admin` en la raíz del proyecto
   - Coloca todos los archivos del panel de administración dentro

5. **Acceder al Sistema**
   - Usuario normal: `http://localhost/tu_proyecto/index.php`
   - Panel admin: `http://localhost/tu_proyecto/admin/dashboard.php`

## 👤 Credenciales de Acceso

### Usuario Normal:
- **Email:** usuario@gmail.com
- **Contraseña:** 123456

### Administrador:
- **Email:** admin@flores.com
- **Contraseña:** admin123

## 📁 Archivos del Proyecto

### Archivos Principales:
1. **config.php** - Configuración de base de datos y funciones
2. **database.sql** - Estructura y datos de la base de datos
3. **index.php** - Página principal
4. **login.php** - Inicio de sesión
5. **registro.php** - Registro de usuarios
6. **catalogo.php** - Catálogo completo de flores
7. **detalle.php** - Detalle de producto individual
8. **carrito.php** - Carrito de compras
9. **pago.php** - Proceso de pago con Yape
10. **logout.php** - Cerrar sesión

### Panel de Administración (carpeta admin/):
1. **dashboard.php** - Estadísticas generales
2. **flores.php** - Gestionar flores (agregar/eliminar)
3. **editar_flor.php** - Editar flores existentes
4. **pedidos.php** - Ver y gestionar pedidos
5. **usuarios.php** - Gestionar usuarios

## 🗄️ Estructura de la Base de Datos

### Tablas:
- **usuarios** - Información de usuarios y administradores
- **categorias** - Categorías de flores
- **flores** - Productos (flores)
- **pedidos** - Órdenes de compra
- **pedido_detalles** - Detalle de items en cada pedido

## 🎨 Funcionalidades Destacadas

### Sistema de Roles:
- **Usuario:** Puede navegar, comprar y ver sus pedidos
- **Admin:** Acceso completo al panel de administración

### Panel de Administración:
- Dashboard con estadísticas en tiempo real
- CRUD completo de flores
- Gestión de estados de pedidos
- Eliminación de usuarios
- Actualización de disponibilidad de productos

### Carrito de Compras:
- Agregar/eliminar productos
- Actualizar cantidades
- Cálculo automático de totales
- Persistencia en sesión

### Sistema de Pago:
- Integración con Yape
- QR Code visual
- Confirmación de pedidos
- Registro en base de datos

## 🔧 Personalización

### Cambiar Imágenes:
Las imágenes están alojadas en Unsplash. Para cambiarlas:
1. Ve al panel admin → Gestionar Flores
2. Edita la flor que desees
3. Cambia la URL de la imagen

### Agregar Nuevas Categorías:
```sql
INSERT INTO categorias (nombre) VALUES ('Nueva Categoría');
```

### Modificar Precios:
1. Accede al panel admin
2. Ve a "Gestionar Flores"
3. Edita la flor y actualiza el precio

## 📱 Responsive Design

El sistema está optimizado para:
- 💻 Desktop
- 📱 Móviles
- 📱 Tablets

## 🔒 Seguridad

- Contraseñas hasheadas con `password_hash()`
- Preparación de consultas SQL (PDO)
- Validación de sesiones
- Protección contra inyección SQL
- Control de acceso por roles

## 🐛 Solución de Problemas

### Error de Conexión:
- Verifica que MySQL esté corriendo
- Revisa las credenciales en `config.php`
- Asegúrate de que la base de datos existe

### Las imágenes no cargan:
- Verifica tu conexión a internet (las imágenes son externas)
- Puedes cambiar las URLs por imágenes locales

### No puedo acceder al admin:
- Asegúrate de usar las credenciales correctas: admin@flores.com / admin123
- Verifica que la carpeta `admin` existe

## 📞 Soporte

Si tienes problemas:
1. Revisa que todos los archivos estén en su lugar
2. Verifica la conexión a la base de datos
3. Comprueba los permisos de carpetas

## 🎯 Próximas Mejoras

Ideas para expandir el proyecto:
- [ ] Sistema de búsqueda avanzada
- [ ] Filtros por categoría y precio
- [ ] Calificaciones y reseñas
- [ ] Múltiples métodos de pago
- [ ] Notificaciones por email
- [ ] Panel de estadísticas más detallado
- [ ] Exportar reportes a PDF
- [ ] Sistema de cupones/descuentos

## 📄 Licencia

Proyecto educativo - Libre para usar y modificar

---

Desarrollado usando PHP y MySQL