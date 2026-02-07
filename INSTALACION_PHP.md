# Guía de Instalación - DevLink con PHP

## Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache, Nginx, etc.)
- Laragon (o similar) para desarrollo local

## Paso 1: Configurar la Base de Datos

### Opción A: Ejecutar Script SQL Directamente

1. Abre phpMyAdmin o tu cliente MySQL favorito
2. Crea una nueva base de datos llamada `devlink`
3. Importa el archivo `devlink.sql` que se encuentra en la raíz del proyecto

### Opción B: Usar el Script de Inicialización PHP

1. Abre en tu navegador: `http://localhost/devlink/api/init-database.php`
2. El script creará automáticamente todas las tablas e insertará datos de ejemplo

## Paso 2: Verificar la Configuración de Base de Datos

El archivo `config/database.php` contiene la configuración de conexión:

```php
$host = 'localhost';      // Cambiar si es necesario
$dbname = 'devlink';      // Nombre de tu base de datos
$username = 'root';       // Usuario MySQL
$password = '';           // Contraseña (vacía para Laragon)
```

Si necesitas cambiar estos valores, edita el archivo según tu entorno.

## Paso 3: Estructura de Archivos

El proyecto debe estar organizado así:

```
/devlink/
├── config/
│   └── database.php          (Configuración de BD)
├── api/
│   ├── auth.php              (Autenticación)
│   ├── projects.php          (Gestión de proyectos)
│   ├── proposals.php         (Gestión de propuestas)
│   ├── developers.php        (Perfiles de desarrolladores)
│   ├── contracts.php         (Gestión de contratos)
│   ├── messages.php          (Sistema de mensajes)
│   ├── search.php            (Búsqueda unificada)
│   ├── helpers.php           (Funciones auxiliares)
│   └── init-database.php     (Inicialización de BD)
├── js/
│   ├── devlink-utils.js      (Utilidades compartidas)
│   └── api-client.js         (Cliente API)
├── *.html                    (Archivos HTML)
└── devlink.sql              (Script SQL)
```

## Paso 4: Integración con HTML

Para usar los APIs en tus páginas HTML, necesitas incluir los scripts en este orden:

```html
<!-- En el <head> o antes de </body> -->
<script src="js/devlink-utils.js"></script>
<script src="js/api-client.js"></script>
```

## Paso 5: Ejemplo de Uso en HTML

### Listar Proyectos

```javascript
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await APIClient.Projects.list(1, 10);
        console.log('Proyectos:', response.projects);
    } catch (error) {
        console.error('Error:', error);
    }
});
```

### Login de Usuario

```javascript
async function handleLogin(email, password, type) {
    try {
        const response = await APIClient.Auth.login(email, password, type);
        if (response.success) {
            // Usuario autenticado
            console.log('Usuario:', response.user);
            // Redirigir al dashboard
            window.location.href = type === 'cliente' ? 'dashboard_cliente.html' : 'dashboard_desarrollador.html';
        }
    } catch (error) {
        console.error('Error de login:', error);
    }
}
```

### Crear Proyecto (Cliente)

```javascript
async function createProject() {
    try {
        const response = await APIClient.Projects.create({
            categoria_id: 1,
            titulo: 'Mi Nuevo Proyecto',
            descripcion: 'Descripción del proyecto',
            presupuesto_min: 500,
            presupuesto_max: 1500,
            estado: 'abierto',
            requisitos: 'Requisitos del proyecto'
        });
        console.log('Proyecto creado:', response);
    } catch (error) {
        console.error('Error:', error);
    }
}
```

### Enviar Propuesta (Desarrollador)

```javascript
async function sendProposal(projectId) {
    try {
        const response = await APIClient.Proposals.create({
            proyecto_id: projectId,
            precio_propuesto: 800,
            tiempo_estimado: '2 semanas',
            descripcion: 'Mi propuesta para este proyecto'
        });
        console.log('Propuesta enviada:', response);
    } catch (error) {
        console.error('Error:', error);
    }
}
```

## Usuarios de Ejemplo

El script de inicialización crea automáticamente usuarios de prueba:

### Clientes
- **Email:** carlos@example.com | **Contraseña:** password123
- **Email:** maria@example.com | **Contraseña:** password123
- **Email:** juan@example.com | **Contraseña:** password123

### Desarrolladores
- **Email:** pedro@example.com | **Contraseña:** password123
- **Email:** ana@example.com | **Contraseña:** password123
- **Email:** luis@example.com | **Contraseña:** password123

## Endpoints Disponibles

### Autenticación
- `POST /api/auth.php?action=login` - Login
- `POST /api/auth.php?action=register` - Registro
- `GET /api/auth.php?action=logout` - Logout
- `GET /api/auth.php?action=get-current-user` - Obtener usuario actual

### Proyectos
- `GET /api/projects.php?action=list` - Listar proyectos
- `GET /api/projects.php?action=get&id=1` - Obtener proyecto
- `POST /api/projects.php?action=create` - Crear proyecto
- `POST /api/projects.php?action=update` - Actualizar proyecto
- `GET /api/projects.php?action=search&q=php` - Buscar proyectos

### Propuestas
- `GET /api/proposals.php?action=list` - Listar propuestas
- `POST /api/proposals.php?action=create` - Crear propuesta
- `GET /api/proposals.php?action=get-by-project&project_id=1` - Propuestas de proyecto

### Desarrolladores
- `GET /api/developers.php?action=list` - Listar desarrolladores
- `GET /api/developers.php?action=get&id=1` - Obtener desarrollador
- `GET /api/developers.php?action=get-top-rated` - Top desarrolladores

### Contratos
- `GET /api/contracts.php?action=list` - Listar contratos
- `POST /api/contracts.php?action=create` - Crear contrato
- `GET /api/contracts.php?action=get&id=1` - Obtener contrato

### Mensajes
- `POST /api/messages.php?action=send` - Enviar mensaje
- `GET /api/messages.php?action=get-conversation&user_id=1` - Obtener conversación
- `GET /api/messages.php?action=get-conversations` - Listar conversaciones

### Búsqueda
- `GET /api/search.php?action=global&q=php` - Búsqueda global
- `GET /api/search.php?action=categories` - Listar categorías
- `GET /api/search.php?action=skills` - Listar habilidades

## Solución de Problemas

### Error: "Error de conexión a BD"
- Verifica que MySQL esté corriendo
- Verifica las credenciales en `config/database.php`
- Verifica que la base de datos `devlink` exista

### Error 404 en API
- Verifica que los archivos `.php` existan en la carpeta `/api`
- Verifica la ruta base en `api-client.js` (debe ser `/api`)

### Sessions no funcionan
- Verifica que PHP tenga permisos de escritura en la carpeta temporal
- Verifica que session.save_path esté configurado correctamente

## Próximos Pasos

1. **Integra los APIs en tus páginas HTML** - Usa los ejemplos anteriores
2. **Personaliza la base de datos** - Añade más datos según sea necesario
3. **Implementa validaciones** - En el cliente y servidor
4. **Añade seguridad** - Implementa JWT o tokens de sesión

## Documentación Completa

- Ver `DEVLINK_IMPROVEMENTS.md` para arquitectura general
- Ver `MEJORAS_REALIZADAS.md` para lista de cambios
- Ver comentarios en los archivos PHP para detalles técnicos
