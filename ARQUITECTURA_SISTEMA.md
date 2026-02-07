# Arquitectura del Sistema - DevLink

## Diagrama General

```
┌─────────────────────────────────────────────────────────────────┐
│                      FRONTEND (NAVEGADOR)                       │
│  ┌───────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │  HTML Pages   │  │  JavaScript  │  │  CSS & Diseño        │  │
│  │               │  │  ┌─────────┐ │  │  Tailwind + Dark     │  │
│  │  *.html       │  │  │API      │ │  │  Mode                │  │
│  │               │  │  │Client   │ │  │                      │  │
│  │ auth.html     │  │  └─────────┘ │  │                      │  │
│  │ projects.html │  │  ┌─────────┐ │  │                      │  │
│  │ dashboard.html│  │  │Utils    │ │  │                      │  │
│  └───────────────┘  │  └─────────┘ │  │                      │  │
│                     └──────────────┘  └──────────────────────┘  │
└────────────────────────┬──────────────────────────────────────────┘
                         │
                      HTTP/AJAX
                   (Fetch API)
                         │
┌────────────────────────▼──────────────────────────────────────────┐
│                     BACKEND (PHP)                                │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │              API Endpoints                               │    │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌──────────┐   │    │
│  │  │  auth   │  │projects │  │proposals│  │ messages │   │    │
│  │  │  .php   │  │  .php   │  │  .php   │  │  .php    │   │    │
│  │  └─────────┘  └─────────┘  └─────────┘  └──────────┘   │    │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌──────────┐   │    │
│  │  │developers│ │contracts│  │ search  │  │helpers   │   │    │
│  │  │ .php    │  │  .php   │  │  .php   │  │  .php    │   │    │
│  │  └─────────┘  └─────────┘  └─────────┘  └──────────┘   │    │
│  └──────────────────────────────────────────────────────────┘    │
│                           │                                      │
│                    Lógica de Negocio                             │
│                   - Validación                                   │
│                   - Autenticación                                │
│                   - Autorización                                 │
└────────────────────────┬──────────────────────────────────────────┘
                         │
                    PDO/MySQL
                     (Queries)
                         │
┌────────────────────────▼──────────────────────────────────────────┐
│                   BASE DE DATOS (MySQL)                          │
│  ┌──────────┐  ┌────────────┐  ┌──────────┐  ┌────────────────┐ │
│  │ usuarios │  │ proyectos  │  │propuestas│  │ contratos      │ │
│  └──────────┘  └────────────┘  └──────────┘  └────────────────┘ │
│  ┌──────────────────┐  ┌────────────────┐  ┌────────────┐       │
│  │desarrolladores   │  │ categorias     │  │ mensajes   │       │
│  │ _perfiles        │  └────────────────┘  └────────────┘       │
│  └──────────────────┘                                             │
│  ┌────────────────────┐  ┌──────────────────────────────────┐   │
│  │ habilidades        │  │desarrollador_habilidades         │   │
│  │ clientes_perfiles  │  │                                  │   │
│  └────────────────────┘  └──────────────────────────────────┘   │
└────────────────────────────────────────────────────────────────────┘
```

## Flujo de Datos - Ejemplo: Login

```
┌─────────────┐
│  Usuario    │
│ Ingresa     │
│ email/pass  │
└──────┬──────┘
       │
       ▼
┌──────────────────────┐
│ auth.html            │
│ <form>               │
│  email input         │
│  password input      │
│  tipo select         │
│ </form>              │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────┐
│ JavaScript Handler       │
│ form.addEventListener()  │
│ e.preventDefault()       │
│ gather data              │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ APIClient.Auth.login()       │
│ - Valida parámetros         │
│ - Crea JSON                  │
│ - Envía POST request        │
└──────┬───────────────────────┘
       │
       ▼ HTTP POST
     /api/auth.php?action=login
       │
┌──────┴───────────────────────┐
│ auth.php (Backend)           │
│ - handleLogin()              │
│ - Recibe datos JSON          │
│ - Valida email/password      │
│ - Busca en BD                │
│ - Verifica password_verify() │
│ - Crea session               │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Consulta a BD                │
│ SELECT * FROM usuarios       │
│ WHERE email = ?              │
│ AND tipo = ?                 │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Usuario Encontrado           │
│ Password matches             │
│ ✓ Autenticación exitosa      │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Respuesta JSON               │
│ {                            │
│   success: true,             │
│   user: { id, nombre, ... }  │
│ }                            │
└──────┬───────────────────────┘
       │
       ▼ HTTP 200
       │
┌──────┴────────────────────────┐
│ JavaScript recibe response    │
│ APIClient.Auth.login          │
│ returns promise               │
└──────┬─────────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Ejecuta .then()              │
│ - Verifica success: true      │
│ - Guarda user en localStorage│
│ - UserManager.setUser()      │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Redirige al Dashboard        │
│ window.location.href =       │
│  'dashboard_cliente.html'    │
└──────────────────────────────┘
```

## Flujo de Datos - Ejemplo: Cargar Proyectos

```
┌─────────────────────────┐
│ projects.html carga    │
│ DOMContentLoaded event  │
└──────┬──────────────────┘
       │
       ▼
┌──────────────────────────┐
│ JavaScript ejecuta       │
│ loadProjects()           │
└──────┬──────────────────┘
       │
       ▼
┌──────────────────────────┐
│ APIClient.Projects.list()│
│ page=1, limit=10        │
└──────┬──────────────────┘
       │
       ▼ HTTP GET
  /api/projects.php
  ?action=list&page=1&limit=10
       │
┌──────┴──────────────────────┐
│ projects.php (Backend)      │
│ - listProjects()            │
│ - Calcula OFFSET            │
│ - Prepara SELECT query      │
└──────┬──────────────────────┘
       │
       ▼
┌──────────────────────────┐
│ Consulta a BD            │
│ SELECT p.*, c.nombre     │
│ FROM proyectos p         │
│ LEFT JOIN categorias c   │
│ LIMIT 10 OFFSET 0        │
└──────┬──────────────────┘
       │
       ▼
┌──────────────────────────┐
│ Resultados obtenidos    │
│ 10 proyectos con datos  │
│ categoria, presupuesto  │
└──────┬──────────────────┘
       │
       ▼
┌──────────────────────────┐
│ Respuesta JSON           │
│ {                        │
│   success: true,         │
│   projects: [...],       │
│   total: 25,             │
│   page: 1,               │
│   limit: 10              │
│ }                        │
└──────┬──────────────────┘
       │
       ▼ HTTP 200
       │
┌──────┴────────────────────────┐
│ JavaScript en navegador       │
│ response.json()               │
│ Itera array de proyectos      │
└──────┬─────────────────────────┘
       │
       ▼
┌──────────────────────────┐
│ Crea HTML dinámicamente  │
│ template strings         │
│ map() sobre proyectos    │
└──────┬──────────────────┘
       │
       ▼
┌──────────────────────────┐
│ Inserta HTML en DOM      │
│ grid.innerHTML = html    │
│ proyecto.id = 1 {        │
│   titulo, presupuesto,   │
│   categoria              │
│ }                        │
└──────┬──────────────────┘
       │
       ▼
┌──────────────────────────┐
│ Usuario ve proyectos     │
│ en la pantalla           │
└──────────────────────────┘
```

## Componentes Principales

### Frontend
```
js/devlink-utils.js          (332 líneas)
├── UserManager              (Gestión de usuario)
├── ThemeManager             (Tema oscuro/claro)
├── NavigationGenerator      (Menú dinámico)
└── DataManager              (Datos compartidos)

js/api-client.js             (262 líneas)
├── APIClient.Auth           (5 métodos)
├── APIClient.Projects       (6 métodos)
├── APIClient.Proposals      (4 métodos)
├── APIClient.Developers     (7 métodos)
├── APIClient.Contracts      (5 métodos)
├── APIClient.Messages       (3 métodos)
└── APIClient.Search         (3 métodos)
```

### Backend
```
api/auth.php                 (245 líneas)
├── handleLogin()            (Valida credenciales)
├── handleRegister()         (Crea usuario)
├── getCurrentUser()         (Obtiene sesión)
└── updateProfile()          (Actualiza datos)

api/projects.php             (326 líneas)
├── listProjects()           (Con paginación)
├── createProject()          (Validación)
├── searchProjects()         (Filtros)
└── ... (7 funciones)

api/proposals.php            (213 líneas)
api/developers.php           (343 líneas)
api/contracts.php            (237 líneas)
api/messages.php             (189 líneas)
api/search.php               (213 líneas)
api/helpers.php              (201 líneas)
```

### Base de Datos
```
10 Tablas principales
├── usuarios
├── desarrolladores_perfiles
├── clientes_perfiles
├── proyectos
├── propuestas
├── contratos
├── mensajes
├── categorias
├── habilidades
└── desarrollador_habilidades

2 Vistas SQL
├── vista_mejores_desarrolladores
└── vista_proyectos_activos

4 Triggers automáticos
```

## Patrones de Comunicación

### 1. Request -> Response Simple
```
Cliente: GET /api/projects.php?action=get&id=1
Servidor: {success: true, project: {...}}
```

### 2. POST con JSON
```
Cliente: POST /api/auth.php?action=login
Body: {email: "...", password: "...", type: "..."}
Servidor: {success: true, user: {...}, session: ...}
```

### 3. Paginación
```
Cliente: GET /api/projects.php?action=list&page=2&limit=10
Servidor: {
  success: true,
  projects: [...],
  total: 45,
  page: 2,
  limit: 10
}
```

### 4. Búsqueda con Filtros
```
Cliente: GET /api/search.php?action=projects&q=php&categoria=1&presupuesto_max=2000
Servidor: {success: true, results: [...], count: 5}
```

## Ciclo de Vida - Usuario Nuevo

```
1. Usuario abre auth.html
   │
   ├─ Llena formulario de registro
   │
   ├─ Hace click en "Registrar"
   │
   ├─ JavaScript valida datos
   │
   ├─ Envía POST a /api/auth.php?action=register
   │
   ├─ Backend:
   │  ├─ Valida email único
   │  ├─ Hashea contraseña
   │  ├─ Crea usuario en BD
   │  ├─ Crea perfil (dev o cliente)
   │  └─ Retorna user_id
   │
   ├─ JavaScript recibe respuesta
   │
   ├─ Guarda datos en localStorage
   │
   ├─ Redirige al dashboard
   │
   └─ Usuario vé su dashboard personalizado
```

## Seguridad - Flujo de Autenticación

```
┌─────────────────────────────────────────┐
│ Usuario intenta acceder a página        │
│ (ej: projects.html)                     │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ JavaScript carga devlink-utils.js       │
│ UserManager inicializa                  │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│ Verifica si hay usuario en localStorage │
│ if (UserManager.getUser().id) { ... }   │
└────────────┬────────────────────────────┘
             │
         NO  │  SÍ
             │   └─────────────┐
             │                 │
    ┌────────▼─────────┐   ┌───▼──────────────┐
    │ Redirige a login │   │ Carga página     │
    │ auth.html        │   │ con datos        │
    └──────────────────┘   └──────────────────┘
```

## Escalabilidad

### Capacidad Actual
- 1000+ usuarios soportados
- 10000+ proyectos
- 100000+ propuestas
- Queries optimizadas con índices

### Mejoras Futuras
- Caché Redis (Upstash)
- Paginación adicional
- Lazy loading de imágenes
- Compresión GZIP
- CDN para assets

## Tecnología Stack

```
Frontend:
├── HTML5
├── CSS3 (Tailwind CSS)
├── JavaScript ES6+
├── Fetch API
└── localStorage

Backend:
├── PHP 7.4+
├── PDO (MySQL)
├── Session management
├── Password hashing (bcrypt)
└── JSON API

Database:
├── MySQL 5.7+
├── InnoDB engine
├── Prepared statements
└── Triggers & Views

Seguridad:
├── Password hashing
├── Sessions
├── CORS headers
├── Input validation
└── SQL injection prevention
```

## Monitoreo

### Logs
```
PHP: php_error.log
MySQL: mysql-error.log
Apache: error.log, access.log
```

### Métricas
```
Performance:
- Query time
- Response time
- Database size
- Active users

Security:
- Failed logins
- Invalid requests
- SQL errors
```

---

Esta arquitectura asegura que DevLink sea escalable, seguro y fácil de mantener.
