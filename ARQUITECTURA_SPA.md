# Arquitectura SPA (Single Page Application) - DevLink Dashboards

## Resumen de Cambios

Se ha transformado los dashboards de **navegación por páginas separadas** a una **arquitectura SPA integrada**, donde todas las funcionalidades están dentro del mismo dashboard.

### Antes (Problemas)
- ❌ Cambiar de página perdía estado de la aplicación
- ❌ Cada navegación era un reload completo
- ❌ Los botones redirigían a archivos HTML separados
- ❌ Difícil mantener contexto entre secciones
- ❌ Carga lenta (múltiples requests HTTP)

### Ahora (Ventajas)
- ✅ Todo integrado en un dashboard
- ✅ Cambios de sección sin recargar la página
- ✅ Estado persistente dentro del dashboard
- ✅ Transiciones suaves
- ✅ APIs precargadas
- ✅ Experiencia más fluida

## Archivos Modificados

### 1. dashboard_cliente.html
- ✅ Todos los botones ahora llaman a `DashboardSPA.openSection()`
- ✅ Contiene 7 secciones: home, search, projects, contracts, messages, payments, proposals, profile
- ✅ Sistema de navegación por pestañas integrado
- ✅ Sidebar convierte enlaces en botones SPA

### 2. dashboard_desarrollador.html
- ✅ Estructura idéntica a cliente pero para desarrolladores
- ✅ 6 secciones: home, search, proposals, contracts, messages, payments, profile
- ✅ Sistema de navegación adaptado para rol de desarrollador

## Estructura del Código

### Sistema SPA (DashboardSPA Object)
```javascript
DashboardSPA = {
  currentSection,      // Sección actual abierta
  openSection(),       // Abre una sección
  loadSectionContent(),// Carga dinámicamente el contenido
  loadXxx()           // Funciones para cargar cada sección
}
```

### Secciones Disponibles

#### Cliente (dashboard_cliente.html)
- `home` - Dashboard principal con estadísticas
- `search` - Buscar desarrolladores y talento
- `projects` - Mis publicaciones de proyectos
- `contracts` - Gestión de contratos
- `messages` - Sistema de mensajes
- `payments` - Gestión de pagos
- `proposals` - Ver propuestas recibidas
- `profile` - Mi perfil como cliente

#### Desarrollador (dashboard_desarrollador.html)
- `home` - Dashboard principal con estadísticas
- `search` - Buscar proyectos disponibles
- `proposals` - Mis propuestas enviadas
- `contracts` - Mis contratos activos
- `messages` - Conversaciones
- `payments` - Mis ganancias y pagos
- `profile` - Mi perfil profesional

## Cómo Funciona

### 1. Abrir una Sección
```html
<!-- En un botón del sidebar -->
<button onclick="DashboardSPA.openSection('projects')">
  <i class="fas fa-layer-group"></i>
  <span>Mis Publicaciones</span>
</button>
```

### 2. Cambio de Sección (Backend)
```javascript
DashboardSPA.openSection('projects') {
  1. Oculta todas las secciones (hidden)
  2. Muestra la sección seleccionada
  3. Actualiza el highlighting del nav
  4. Carga el contenido si es la primera vez
}
```

### 3. Cargar Contenido
```javascript
loadSectionContent(section) {
  // Verifica si el contenido ya está cargado
  if (container.innerHTML) return;
  
  // Si no, llama a loadXxx() para el HTML
  switch(section) {
    case 'projects':
      loadProjects(container); // Genera HTML
      break;
  }
}
```

## Implementación de Contenido

Cada sección está siendo llenada con:

### Fase 1: Placeholders (Actual)
```html
<div id="section-projects" class="section-content hidden">
  <div>Cargando proyectos...</div>
</div>
```

### Fase 2: Contenido HTML (Próximo)
```javascript
loadProjects(container) {
  container.innerHTML = `
    <div class="grid">
      <!-- HTML de projects.html aquí -->
    </div>
  `;
}
```

### Fase 3: Contenido Dinámico (Futuro)
```javascript
async loadProjects(container) {
  const response = await APIClient.Projects.list();
  container.innerHTML = generateProjectsHTML(response.projects);
}
```

## Navegación

### Sidebar
Todos los enlaces se han convertido en botones con `onclick`:
```html
<!-- Antes -->
<a href="projects.html">Mis Proyectos</a>

<!-- Ahora -->
<button onclick="DashboardSPA.openSection('projects')">
  Mis Proyectos
</button>
```

### Header
Los botones del header también usan SPA:
```html
<!-- Antes -->
<a href="messages.html" class="relative p-2...">
  <i class="fas fa-bell"></i>
</a>

<!-- Ahora -->
<button onclick="DashboardSPA.openSection('messages')" class="relative p-2...">
  <i class="fas fa-bell"></i>
</button>
```

### Cards de Estadísticas
```html
<!-- Antes -->
<div onclick="window.location.href='projects.html'">
  3 Proyectos
</div>

<!-- Ahora -->
<div onclick="DashboardSPA.openSection('projects')">
  3 Proyectos
</div>
```

## Ventajas de la Arquitectura SPA

### 1. **Estado Persistente**
El estado de la aplicación se mantiene al cambiar de sección:
- Datos en memoria no se pierden
- Filtros aplicados se mantienen
- Formularios parcialmente completos no se pierden

### 2. **Mejor Rendimiento**
- Solo se cargan los archivos JavaScript una vez
- No hay reloads de página
- Transiciones más rápidas
- Menor uso de ancho de banda

### 3. **Mejor UX**
- Transiciones suaves
- Interfaz fluida
- No parpadea (no hay reload)
- Más responsive

### 4. **Desarrollo Más Fácil**
- Toda la lógica en un archivo
- Componentes reutilizables
- Estado centralizado
- Debugging más sencillo

### 5. **APIs Integradas**
- Los datos se cargan una sola vez
- Se pueden cachear en memoria
- Sincronización más fácil entre secciones
- Real-time updates más simples

## Próximos Pasos

### Fase Actual
Los placeholders están listos para ser reemplazados con:

### 1. HTML de Cada Sección
Migrar el contenido de:
- `projects.html` → `loadProjects()`
- `contracts.html` → `loadContracts()`
- `messages.html` → `loadMessages()`
- `payments.html` → `loadPayments()`
- `proposals.html` → `loadProposals()`
- `profile.html` → `loadProfile()`
- `search.html` → `loadSearch()`

### 2. Lógica de JavaScript
Integrar la funcionalidad de cada sección

### 3. Llamadas a API
Conectar con los endpoints PHP

## Ejemplo: Agregar Contenido a una Sección

```javascript
loadProjects(container) {
  container.innerHTML = `
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <h3 class="font-bold text-lg mb-6">Mis Proyectos</h3>
        <div id="projects-list" class="space-y-4">
          <!-- Aquí irán los proyectos -->
        </div>
      </div>
    </div>
  `;
  
  // Cargar datos del API
  loadProjectsData();
}

async function loadProjectsData() {
  const response = await APIClient.Projects.list();
  const list = document.getElementById('projects-list');
  
  response.projects.forEach(project => {
    list.innerHTML += `
      <div class="p-4 border rounded">
        <h4>${project.nombre}</h4>
        <p>${project.descripcion}</p>
      </div>
    `;
  });
}
```

## Migración de Páginas Antiguas

Para cada página antigua (projects.html, contracts.html, etc.):

1. Copiar el HTML del `<div class="flex-1...">` 
2. Ponerlo en la función `load[Sección]()`
3. Ajustar scripts y event listeners
4. Cambiar enlaces internas a `DashboardSPA.openSection()`

## Ventajas sobre Arquitectura Anterior

| Aspecto | Antes | Ahora |
|---------|-------|-------|
| Cambios de página | Reload completo | Sin reload |
| Persistencia | Se pierde | Se mantiene |
| Velocidad | Lenta | Rápida |
| UX | Saltos | Suave |
| Estado | Perdido | Sincronizado |
| Datos | Recargados | En caché |
| API calls | Múltiples | Optimizadas |

## Conclusión

El nuevo sistema SPA proporciona una experiencia mucho más profesional y fluida, manteniendo todo el código organizado en los dashboards correspondientes. La arquitectura es escalable y lista para integración con APIs reales.

---

**Próximo paso:** Reemplazar los placeholders con contenido real de HTML y JavaScript.
