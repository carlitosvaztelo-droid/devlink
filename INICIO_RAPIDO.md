# DevLink - Guía de Inicio Rápido

## 🚀 Empezar en 3 Pasos

### 1. Abrir la Plataforma
```
Abre index.html en tu navegador
```

### 2. Registrarse
```
Haz clic en "Registrarse"
- Selecciona: Cliente O Desarrollador
- Ingresa tu nombre y apellido
- Clic en "Crear Cuenta"
```

### 3. Navegar
```
Serás redirigido a tu dashboard
- Como Cliente: ves "Mis Publicaciones", "Buscar Talento", etc.
- Como Dev: ves "Explorar Proyectos", "Mis Postulaciones", etc.
```

---

## 🎯 Flujos de Usuario

### Cliente: Publicar un Proyecto
1. Dashboard → "Publicar Proyecto"
2. Ve a "Mis Publicaciones" para ver proyectos
3. Haz click en "Ver Candidatos" para revisar propuestas
4. Ir a "Contratos" para gestionar acuerdos
5. Ir a "Pagos" para ver inversión

### Desarrollador: Buscar Trabajo
1. Dashboard → "Buscar Trabajo"
2. Ve a "Mis Postulaciones" para trackear ofertas
3. Filtrar por habilidades y presupuesto
4. Enviar propuesta
5. Ir a "Contratos" una vez aprobada
6. Ir a "Pagos" para ver ingresos

---

## 💬 Características Principales

### Dashboard Dinámico
```
Tu dashboard se adapta según tu rol:
- Cliente: Ve métricas de inversión, proyectos activos
- Dev: Ve ingresos, proyectos en progreso
```

### Sistema de Búsqueda
```
Cliente:     Busca desarrolladores por habilidades
Desarrollador: Busca proyectos por categoría y presupuesto
```

### Mensajería
```
Contacta directamente con:
- Cliente contacta Dev para detalles
- Dev contacta Cliente para aclaraciones
```

### Gestión de Pagos (Escrow)
```
Sistema seguro:
1. Cliente deposita dinero
2. DevLink lo retiene
3. Se libera al completar trabajo
```

---

## 🔧 Funciones Importantes

### Cambiar Tema
En cualquier página: Haz clic en el icono de sol/luna en la esquina inferior izquierda

### Cambiar Perfil
Haz clic en tu avatar en la esquina superior derecha → "Mi Perfil"

### Cerrar Sesión
Haz clic en "Cerrar Sesión" en la barra lateral

### Navegar Rápido
Usa la barra lateral (izquierda) para navegar entre módulos

---

## 🎨 Interfaz

### Barra Lateral (Izquierda)
```
Narrow (móvil):  Solo iconos
Wide (desktop):  Iconos + Texto
```

### Colores
```
Modo Claro: Blanco y grises
Modo Oscuro: Azul oscuro (#0F1C3F) y grises
```

### Tipografía
```
Fuente: Inter
Tamaños: 14px (pequeño), 16px (normal), 24px (heading)
```

---

## 📱 Responsive Design

```
Móvil (< 768px):   Stack vertical, sidebar colapsada
Tablet (768-1024px): Grid 2 columnas, sidebar ancha
Desktop (> 1024px): Grid 3 columnas, sidebar completa
```

---

## 🔑 Atajos de Teclado

| Atajo | Acción |
|-------|--------|
| Click avatar | Ir a perfil |
| Click notificación | Ver mensajes |
| ESC | (Futuro) Cerrar modales |

---

## ⚠️ Notas Importantes

1. **Datos Locales**: Todo se guarda en tu navegador (localStorage)
2. **No requiere login real**: Demo solo usa localStorage
3. **Datos compartidos**: Los datos persisten entre páginas
4. **Tema persistente**: Tu preferencia de tema se guarda
5. **Role binding**: Una vez selecciones Cliente/Dev, se mantiene en todas las páginas

---

## 🆘 Solucionar Problemas

### "No aparece el sidebar"
```
Solución: Recarga la página (F5)
```

### "El avatar no es mi inicial"
```
Verifica que el nombre esté completo en el registro
```

### "El tema no cambió"
```
Asegúrate de hacer clic en el icono correcto
```

### "No veo mi rol correctamente"
```
Solución: 
1. Abre DevTools (F12)
2. Consola
3. Escribe: UserManager.getUser()
4. Verifica que "type" sea correcto
```

---

## 📊 Datos de Prueba

Puedes usar cualquier dato ficticio:

```javascript
Clientes:
- Nombre: Juan
- Apellido: Pérez
- Email: cualquiera@ejemplo.com

Desarrolladores:
- Nombre: Carlos
- Apellido: González
- Habilidades: PHP, React, SQL
```

---

## 🎓 Aprender Más

Consulta los archivos de documentación:
- `DEVLINK_IMPROVEMENTS.md` - Guía técnica detallada
- `MEJORAS_REALIZADAS.md` - Cambios implementados
- `INICIO_RAPIDO.md` - Este archivo

---

## 🚀 Próximos Pasos

Una vez familiariazado con la interfaz:

1. **Explorar Módulos**: Cada sección tiene funcionalidad completa
2. **Cambiar Entre Roles**: Registrate como Cliente y como Dev
3. **Probar Navegación**: Haz clic en diferentes botones
4. **Cambiar Tema**: Prueba el modo oscuro/claro
5. **Leer Código**: El sistema es muy legible con la refactorización

---

## 📞 Soporte

Si encuentras problemas:

1. Verifica que tengas JavaScript habilitado
2. Abre DevTools (F12) y revisa la consola
3. Recarga la página
4. Limpia el cache (Ctrl+Shift+Supr)

---

## ✅ Checklist de Prueba

- [ ] Puedo registrarme como Cliente
- [ ] Puedo registrarme como Dev
- [ ] Veo el sidebar correcto según mi rol
- [ ] Puedo cambiar el tema
- [ ] Puedo navegar entre páginas
- [ ] Mi nombre aparece en el welcome
- [ ] Los cambios persisten después de recargar
- [ ] El avatar se actualiza en todas las páginas

---

**¡Listo para explorar DevLink!**

Versión 1.0 - Febrero 2026
