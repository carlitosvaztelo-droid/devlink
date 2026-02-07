# DevLink - Empezar Aquí

## Bienvenida ✨

Tu plataforma DevLink está **completamente funcional**. Solo necesitas 2 pasos para empezar.

---

## Paso 1: Configurar Base de Datos (1 minuto)

### Opción A: Automático (RECOMENDADO)
Abre en navegador:
```
http://localhost/devlink/api/setup.php
```

Deberías ver un JSON verde con "success": true

### Opción B: Manual
1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Importa el archivo `devlink.sql` (en la carpeta raíz)
3. Luego abre: `http://localhost/devlink/api/setup.php`

---

## Paso 2: Acceder a la Plataforma (1 minuto)

Abre en navegador:
```
http://localhost/devlink/status.html
```

O directamente:
```
http://localhost/devlink/auth.html
```

**Credenciales de prueba:**
- Email: `carlos@example.com` o `pedro@example.com`
- Contraseña: `password123`
- Tipo: `cliente` o `desarrollador`

---

## ¿Errores? Consulta Aquí

**Error al conectar BD:**
→ Lee: `SOLUCION_PROBLEMAS.md`

**¿Cómo funciona el API?**
→ Lee: `EJEMPLOS_INTEGRACION.md`

**¿Cómo está todo estructurado?**
→ Lee: `ARQUITECTURA_SISTEMA.md`

---

## Archivos Importantes

```
/api/
  ├── setup.php           ← Usar este para crear BD
  ├── auth.php            ← Autenticación
  ├── projects.php        ← Proyectos
  ├── developers.php      ← Desarrolladores
  └── ...                 ← 4 más

/js/
  ├── devlink-utils.js    ← Utilidades (temas, usuarios)
  └── api-client.js       ← Cliente API JavaScript

/*.html
  ├── auth.html           ← Login/Registro
  ├── dashboard_*.html    ← Dashboards
  ├── projects.html       ← Proyectos
  ├── status.html         ← Estado de instalación
  └── ...                 ← 5 más

/config/
  └── database.php        ← Conexión a BD

/devlink.sql             ← Base de datos completa
```

---

## Características Disponibles

✅ Autenticación (Login/Registro)
✅ Dashboard Cliente
✅ Dashboard Desarrollador
✅ Gestión de Proyectos
✅ Envío de Propuestas
✅ Perfiles de Desarrolladores
✅ Sistema de Mensajes
✅ Búsqueda Global
✅ Gestión de Contratos
✅ Gestión de Pagos

---

## Próximo Paso Recomendado

Una vez que todo funcione:

1. Abre `http://localhost/devlink/auth.html`
2. Haz login con: `carlos@example.com` / `password123`
3. Explora el dashboard
4. Abre la consola (F12) y ejecuta:
   ```javascript
   APIClient.Projects.list(1, 10)
   ```

---

## Documentación Disponible

| Documento | Contenido |
|-----------|----------|
| `README_INICIO.md` | Resumen general completo |
| `SOLUCION_PROBLEMAS.md` | Solucionar errores comunes |
| `INSTALACION_PHP.md` | Detalles técnicos |
| `EJEMPLOS_INTEGRACION.md` | 7 ejemplos de código |
| `ARQUITECTURA_SISTEMA.md` | Diagramas y flujos |
| `INDICE_DOCUMENTACION.md` | Tabla de contenidos |
| `CHECKLIST_VERIFICACION.md` | Tests automáticos |

---

## ¿Necesitas Ayuda?

1. **Abre `status.html`** para ver el estado actual
2. **Lee `SOLUCION_PROBLEMAS.md`** si hay errores
3. **Consulta `EJEMPLOS_INTEGRACION.md`** para ver código

---

**¡Listo! Tu plataforma DevLink está lista para usar. 🚀**
