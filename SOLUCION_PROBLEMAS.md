# Solución de Problemas - DevLink

## Error: "Failed to open stream: No such file or directory"

**Problema:** El script no encuentra el archivo `devlink.sql`

**Solución:**
1. Verifica que el archivo `devlink.sql` esté en la carpeta raíz del proyecto (`/devlink/`)
2. Usa este script alternativo que no requiere importar SQL externo:
   ```
   http://localhost/devlink/api/setup.php
   ```

## Error: "Field 'slug' doesn't have a default value"

**Problema:** El campo `slug` en la tabla `proyectos` no tiene valor por defecto

**Solución:**
- El nuevo script `setup.php` maneja esto automáticamente
- O modifica el SQL para agregar `DEFAULT 'proyecto'` al campo slug

## Error: "SQLSTATE[HY000]: General error: 1364"

**Problema:** Algún campo no tiene valor por defecto en la inserción

**Solución:**
1. Usa `api/setup.php` en lugar de `init-database.php`
2. O verifica que todos los campos requeridos se están completando

## Pasos para Configurar la BD Correctamente

### Opción 1: Usar el nuevo script setup.php (RECOMENDADO)

```bash
1. Abre: http://localhost/devlink/api/setup.php
2. Debería ver un JSON de éxito
3. Listo para usar
```

### Opción 2: Importar SQL manualmente

```bash
1. Abre phpMyAdmin
2. Ve a "Importar"
3. Selecciona el archivo devlink.sql (en la carpeta raíz)
4. Luego abre: http://localhost/devlink/api/setup.php
5. Para insertar datos de ejemplo
```

### Opción 3: Crear tablas manualmente

Si nada funciona, ejecuta estas consultas en phpMyAdmin:

```sql
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS devlink;
USE devlink;

-- Usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('cliente', 'desarrollador') NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    cedula VARCHAR(50),
    telefono VARCHAR(20),
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categorías
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Habilidades
CREATE TABLE habilidades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Proyectos
CREATE TABLE proyectos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    categoria_id INT,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    presupuesto_min DECIMAL(10,2),
    presupuesto_max DECIMAL(10,2),
    estado ENUM('abierto', 'en_progreso', 'completado', 'cancelado') DEFAULT 'abierto',
    vistas INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    slug VARCHAR(255) DEFAULT 'proyecto',
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Desarrolladores Perfiles
CREATE TABLE desarrolladores_perfiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL UNIQUE,
    tarifa_hora DECIMAL(10,2),
    descripcion TEXT,
    experiencia_años INT,
    calificacion_promedio DECIMAL(3,2) DEFAULT 0,
    proyectos_completados INT DEFAULT 0,
    tasa_exito INT DEFAULT 0,
    disponible BOOLEAN DEFAULT true,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Clientes Perfiles
CREATE TABLE clientes_perfiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL UNIQUE,
    nombre_empresa VARCHAR(255),
    descripcion TEXT,
    sitio_web VARCHAR(255),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Propuestas
CREATE TABLE propuestas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proyecto_id INT NOT NULL,
    desarrollador_id INT NOT NULL,
    monto_propuesto DECIMAL(10,2),
    tiempo_estimado VARCHAR(100),
    descripcion TEXT,
    estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_proposal (proyecto_id, desarrollador_id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    FOREIGN KEY (desarrollador_id) REFERENCES desarrolladores_perfiles(id)
);

-- Contratos
CREATE TABLE contratos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proyecto_id INT NOT NULL,
    desarrollador_id INT NOT NULL,
    monto_total DECIMAL(10,2),
    estado ENUM('en_revision', 'activo', 'completado', 'cancelado') DEFAULT 'en_revision',
    fecha_inicio DATE,
    fecha_fin_estimada DATE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    FOREIGN KEY (desarrollador_id) REFERENCES desarrolladores_perfiles(id)
);

-- Mensajes
CREATE TABLE mensajes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    remitente_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    contenido TEXT,
    leido BOOLEAN DEFAULT false,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (remitente_id) REFERENCES usuarios(id),
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id)
);

-- Desarrollador Habilidades
CREATE TABLE desarrollador_habilidades (
    desarrollador_id INT NOT NULL,
    habilidad_id INT NOT NULL,
    PRIMARY KEY (desarrollador_id, habilidad_id),
    FOREIGN KEY (desarrollador_id) REFERENCES desarrolladores_perfiles(id),
    FOREIGN KEY (habilidad_id) REFERENCES habilidades(id)
);
```

Luego accede a:
```
http://localhost/devlink/api/setup.php
```

## Errores de Conexión a BD

**Problema:** "Connection refused" o "Cannot connect to database"

**Verificar:**
1. ¿Está MySQL corriendo? (phpmyadmin debe funcionar)
2. ¿Son correctos los datos en `config/database.php`?
   - host: localhost
   - usuario: root (o tu usuario)
   - contraseña: (o tu contraseña)
   - puerto: 3306

## Credenciales de Prueba

Una vez se cree la BD con datos de ejemplo, puedes loguearte con:

**Cliente:**
- Email: `carlos@example.com`
- Password: `password123`
- Tipo: cliente

**Desarrollador:**
- Email: `pedro@example.com`
- Password: `password123`
- Tipo: desarrollador

## Verificar que Todo Funciona

Abre la consola del navegador (F12) y ejecuta:

```javascript
// Verificar que el API cliente está cargado
console.log(APIClient);

// Probar una búsqueda
APIClient.Projects.list(1, 10).then(r => console.log(r));

// Probar login
APIClient.Auth.login('carlos@example.com', 'password123', 'cliente')
  .then(r => console.log(r))
  .catch(e => console.log(e));
```

## Más Ayuda

Revisa:
- `INSTALACION_PHP.md` - Instalación detallada
- `ARQUITECTURA_SISTEMA.md` - Cómo funciona el sistema
- `EJEMPLOS_INTEGRACION.md` - Ejemplos de código
