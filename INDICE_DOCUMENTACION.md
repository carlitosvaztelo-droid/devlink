# Índice de Documentación - DevLink

Bienvenido a DevLink. Esta documentación te guiará para entender y utilizar la plataforma completa.

## 1. Comenzar (Para Principiantes)

Si es tu primera vez con DevLink, comienza aquí:

### 📘 [INSTALACION_PHP.md](INSTALACION_PHP.md)
- Requisitos del sistema
- Configuración de base de datos
- Estructura de archivos
- Instalación paso a paso
- **Lee esto primero**

### 🚀 [INICIO_RAPIDO.md](INICIO_RAPIDO.md)
- Guía rápida (5 minutos)
- Primeras pruebas
- Login de ejemplo
- Crear tu primer proyecto

### ✅ [CHECKLIST_VERIFICACION.md](CHECKLIST_VERIFICACION.md)
- Verificar que todo funciona
- Test de cada endpoint
- Resolución de problemas
- **Ejecuta esto después de instalar**

## 2. Entender la Arquitectura

### 📐 [DEVLINK_IMPROVEMENTS.md](DEVLINK_IMPROVEMENTS.md)
- Arquitectura general
- Mejoras realizadas
- Estructura de código
- Patrones implementados

### 📋 [MEJORAS_REALIZADAS.md](MEJORAS_REALIZADAS.md)
- Resumen ejecutivo
- Cambios por módulo
- Beneficios de la refactorización
- Métricas de mejora

### 🔧 [RESUMEN_INTEGRACION_PHP.md](RESUMEN_INTEGRACION_PHP.md)
- Backend PHP completo
- Endpoints disponibles
- Características implementadas
- Estadísticas de código

## 3. Integración con HTML

### 💡 [EJEMPLOS_INTEGRACION.md](EJEMPLOS_INTEGRACION.md)
- 7 ejemplos completos
- Cómo cargar proyectos
- Cómo hacer login
- Cómo crear proyectos
- Cómo enviar propuestas
- Cómo enviar mensajes
- Cómo buscar desarrolladores
- **Copia y pega estos ejemplos**

### 📚 Archivos de referencia
- `js/devlink-utils.js` - Utilidades compartidas
- `js/api-client.js` - Cliente API JavaScript
- `config/database.php` - Configuración de BD

## 4. API Reference

### Autenticación
- Login
- Registro
- Logout
- Obtener usuario actual

### Proyectos
- Listar proyectos
- Crear proyecto
- Actualizar proyecto
- Buscar proyectos
- Filtrar por cliente

### Propuestas
- Enviar propuesta
- Obtener propuestas
- Actualizar estado
- Listar por proyecto

### Desarrolladores
- Listar desarrolladores
- Obtener perfil
- Top desarrolladores
- Añadir habilidades

### Contratos
- Crear contrato
- Actualizar estado
- Listar contratos
- Por proyecto

### Mensajes
- Enviar mensaje
- Obtener conversación
- Listar mensajes
- Marcar como leído

### Búsqueda
- Búsqueda global
- Por proyectos
- Por desarrolladores
- Filtros avanzados

## 5. Datos de Ejemplo

### Usuarios Predefinidos

**Clientes:**
- carlos@example.com / password123
- maria@example.com / password123
- juan@example.com / password123

**Desarrolladores:**
- pedro@example.com / password123
- ana@example.com / password123
- luis@example.com / password123

### Datos de Prueba
- 5+ Proyectos
- 5 Categorías
- 10+ Habilidades
- 3+ Desarrolladores
- Múltiples propuestas

## 6. Estructura del Proyecto

```
/devlink/
├── config/
│   └── database.php              ← Configuración de BD
├── api/
│   ├── auth.php                  ← Autenticación
│   ├── projects.php              ← Proyectos
│   ├── proposals.php             ← Propuestas
│   ├── developers.php            ← Desarrolladores
│   ├── contracts.php             ← Contratos
│   ├── messages.php              ← Mensajes
│   ├── search.php                ← Búsqueda
│   ├── helpers.php               ← Utilidades PHP
│   └── init-database.php         ← Inicializador
├── js/
│   ├── devlink-utils.js          ← Utilidades JS
│   └── api-client.js             ← Cliente API
├── Pantallas HTML (*.html)
├── devlink.sql                   ← Script SQL
└── Documentación (*.md)
```

## 7. Guías Específicas

### ¿Cómo hacer login?
1. Ver ejemplos en `EJEMPLOS_INTEGRACION.md` sección "2. Formulario de Login"
2. Usar `APIClient.Auth.login(email, password, type)`
3. Guardar usuario con `UserManager.setUser()`

### ¿Cómo cargar proyectos?
1. Ver ejemplos en `EJEMPLOS_INTEGRACION.md` sección "1. Cargar Proyectos"
2. Usar `APIClient.Projects.list(page, limit)`
3. Renderizar HTML dinámicamente

### ¿Cómo crear un proyecto?
1. Ver ejemplos en `EJEMPLOS_INTEGRACION.md` sección "3. Crear Proyecto"
2. Usar `APIClient.Projects.create(datos)`
3. Validar datos antes de enviar

### ¿Cómo enviar una propuesta?
1. Ver ejemplos en `EJEMPLOS_INTEGRACION.md` sección "4. Enviar Propuesta"
2. Usar `APIClient.Proposals.create(datos)`
3. Manejar errores

### ¿Cómo buscar?
1. Ver ejemplos en `EJEMPLOS_INTEGRACION.md` sección "6. Buscar Desarrolladores"
2. Usar `APIClient.Search.global(query)`
3. Implementar debounce

## 8. Desarrollo y Personalización

### Agregar nuevo endpoint
1. Crear función en `/api/`
2. Agregar método en `APIClient` en `js/api-client.js`
3. Usar en tu HTML

### Cambiar configuración de BD
1. Editar `config/database.php`
2. Verificar credenciales
3. Probar conexión

### Agregar más datos
1. Ejecutar scripts SQL adicionales
2. O usar `api/init-database.php` nuevamente

## 9. Troubleshooting

### Problema: "Error de conexión a BD"
**Solución:** Ver `INSTALACION_PHP.md` sección "Solución de Problemas"

### Problema: "404 en API"
**Solución:** Verifica que los archivos PHP existan en `/api/`

### Problema: "Credenciales inválidas"
**Solución:** Usa los datos de ejemplo predefinidos

### Problema: "Sessions no funcionan"
**Solución:** Ver `INSTALACION_PHP.md` sección "Solución de Problemas"

## 10. Recursos Rápidos

### Archivo de Configuración
```php
// config/database.php
$host = 'localhost';        // Cambiar si es necesario
$dbname = 'devlink';
$username = 'root';
$password = '';
```

### Cliente API Básico
```javascript
// En tu HTML
<script src="js/devlink-utils.js"></script>
<script src="js/api-client.js"></script>

// Usar en tu código
const projects = await APIClient.Projects.list();
```

### URL del API
```
Base: http://localhost/devlink/api/
Endpoints: [action].php?action=[nombre]
```

## 11. Tabla de Contenidos Completa

| Documento | Propósito | Para quién |
|-----------|----------|-----------|
| INSTALACION_PHP.md | Instalar el proyecto | Principiantes |
| INICIO_RAPIDO.md | Empezar en 5 minutos | Todos |
| CHECKLIST_VERIFICACION.md | Verificar funcionalidad | Instaladores |
| EJEMPLOS_INTEGRACION.md | Código listo para usar | Desarrolladores |
| RESUMEN_INTEGRACION_PHP.md | Visión general técnica | Arquitectos |
| DEVLINK_IMPROVEMENTS.md | Mejoras implementadas | Técnicos |
| MEJORAS_REALIZADAS.md | Resumen de cambios | Todos |
| INDICE_DOCUMENTACION.md | Este archivo | Navegación |

## 12. Próximos Pasos

1. **Hoy:**
   - [ ] Leer `INSTALACION_PHP.md`
   - [ ] Ejecutar `api/init-database.php`
   - [ ] Verificar con `CHECKLIST_VERIFICACION.md`

2. **Mañana:**
   - [ ] Integrar APIs en HTML
   - [ ] Usar ejemplos de `EJEMPLOS_INTEGRACION.md`
   - [ ] Probar login y proyectos

3. **Esta semana:**
   - [ ] Completar integración de todas las páginas
   - [ ] Agregar validaciones
   - [ ] Implementar loading states

4. **Futuro:**
   - [ ] Implementar JWT para seguridad
   - [ ] Agregar notificaciones en tiempo real
   - [ ] Implementar pagos
   - [ ] Agregar sistema de rating

## 13. Ayuda y Contacto

Si tienes problemas:

1. **Lee la documentación relevante**
   - `INSTALACION_PHP.md` para problemas de instalación
   - `CHECKLIST_VERIFICACION.md` para tests

2. **Verifica los ejemplos**
   - `EJEMPLOS_INTEGRACION.md` tiene soluciones completas

3. **Revisa la consola del navegador**
   - F12 en tu navegador
   - Busca errores en rojo

4. **Revisa los logs de PHP**
   - Apache: `/var/log/apache2/`
   - PHP: `php_error.log`

5. **Busca en comentarios de código**
   - Los archivos PHP tienen comentarios detallados

## 14. Versión del Proyecto

- **Versión:** 2.0.0
- **Estado:** Full-Stack Completo
- **Backend:** PHP + MySQL
- **Frontend:** HTML + JavaScript
- **Última actualización:** Febrero 2026

---

**¡Felicidades! Ya tienes DevLink completo y funcional. Comienza con el PASO 1 en la sección de Comenzar.**
