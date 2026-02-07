# Ejemplos de Integración - DevLink API

## 1. Cargar Proyectos Dinámicamente

### HTML
```html
<div id="projects-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Los proyectos se cargarán aquí dinámicamente -->
</div>
```

### JavaScript
```javascript
async function loadProjects() {
    try {
        const response = await APIClient.Projects.list(1, 10);
        const projectsGrid = document.getElementById('projects-grid');
        
        if (response.success && response.projects.length > 0) {
            projectsGrid.innerHTML = response.projects.map(project => `
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg border">
                    <h3 class="font-bold text-lg mb-2">${project.titulo}</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">${project.descripcion}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-primary font-semibold">
                            $${project.presupuesto_min} - $${project.presupuesto_max}
                        </span>
                        <a href="project-detail.html?id=${project.id}" class="text-primary hover:underline">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            `).join('');
        } else {
            projectsGrid.innerHTML = '<p>No hay proyectos disponibles</p>';
        }
    } catch (error) {
        console.error('Error cargando proyectos:', error);
        alert('Error al cargar los proyectos');
    }
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', loadProjects);
```

## 2. Formulario de Login

### HTML
```html
<form id="login-form">
    <div class="mb-4">
        <label for="email" class="block mb-2">Email</label>
        <input type="email" id="email" name="email" required class="w-full p-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label for="password" class="block mb-2">Contraseña</label>
        <input type="password" id="password" name="password" required class="w-full p-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label class="block mb-2">¿Eres Cliente o Desarrollador?</label>
        <select id="userType" name="userType" required class="w-full p-2 border rounded">
            <option value="cliente">Cliente</option>
            <option value="desarrollador">Desarrollador</option>
        </select>
    </div>
    
    <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
        Iniciar Sesión
    </button>
</form>
```

### JavaScript
```javascript
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const userType = document.getElementById('userType').value;
    
    try {
        const response = await APIClient.Auth.login(email, password, userType);
        
        if (response.success) {
            // Guardar usuario en localStorage
            UserManager.setUser(response.user);
            
            // Redirigir al dashboard
            const dashboard = userType === 'cliente' ? 'dashboard_cliente.html' : 'dashboard_desarrollador.html';
            window.location.href = dashboard;
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
```

## 3. Crear Nuevo Proyecto (Cliente)

### HTML
```html
<form id="create-project-form">
    <div class="mb-4">
        <label for="titulo" class="block mb-2">Título del Proyecto</label>
        <input type="text" id="titulo" name="titulo" required class="w-full p-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label for="descripcion" class="block mb-2">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="5" required class="w-full p-2 border rounded"></textarea>
    </div>
    
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label for="presupuesto_min" class="block mb-2">Presupuesto Mínimo</label>
            <input type="number" id="presupuesto_min" name="presupuesto_min" required class="w-full p-2 border rounded">
        </div>
        <div>
            <label for="presupuesto_max" class="block mb-2">Presupuesto Máximo</label>
            <input type="number" id="presupuesto_max" name="presupuesto_max" required class="w-full p-2 border rounded">
        </div>
    </div>
    
    <div class="mb-4">
        <label for="categoria" class="block mb-2">Categoría</label>
        <select id="categoria" name="categoria" class="w-full p-2 border rounded"></select>
    </div>
    
    <button type="submit" class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">
        Crear Proyecto
    </button>
</form>
```

### JavaScript
```javascript
// Cargar categorías
async function loadCategories() {
    try {
        const response = await APIClient.Search.getCategories();
        const selectCategoria = document.getElementById('categoria');
        
        if (response.success) {
            selectCategoria.innerHTML = '<option value="">Selecciona una categoría</option>' +
                response.categories.map(cat => `
                    <option value="${cat.id}">${cat.nombre}</option>
                `).join('');
        }
    } catch (error) {
        console.error('Error cargando categorías:', error);
    }
}

// Manejar envío del formulario
document.getElementById('create-project-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        titulo: document.getElementById('titulo').value,
        descripcion: document.getElementById('descripcion').value,
        presupuesto_min: parseFloat(document.getElementById('presupuesto_min').value),
        presupuesto_max: parseFloat(document.getElementById('presupuesto_max').value),
        categoria_id: parseInt(document.getElementById('categoria').value),
        estado: 'abierto'
    };
    
    try {
        const response = await APIClient.Projects.create(formData);
        
        if (response.success) {
            alert('Proyecto creado exitosamente');
            // Limpiar formulario
            document.getElementById('create-project-form').reset();
            // Redirigir a proyectos
            window.location.href = 'projects.html';
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

// Ejecutar al cargar
document.addEventListener('DOMContentLoaded', loadCategories);
```

## 4. Enviar Propuesta (Desarrollador)

### HTML
```html
<form id="proposal-form">
    <input type="hidden" id="project_id" name="project_id">
    
    <div class="mb-4">
        <label for="precio_propuesto" class="block mb-2">Precio Propuesto ($)</label>
        <input type="number" id="precio_propuesto" name="precio_propuesto" step="0.01" required 
               class="w-full p-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label for="tiempo_estimado" class="block mb-2">Tiempo Estimado</label>
        <input type="text" id="tiempo_estimado" name="tiempo_estimado" placeholder="Ej: 2 semanas" 
               required class="w-full p-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label for="descripcion" class="block mb-2">Descripción de tu Propuesta</label>
        <textarea id="descripcion" name="descripcion" rows="5" required 
                  class="w-full p-2 border rounded"></textarea>
    </div>
    
    <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
        Enviar Propuesta
    </button>
</form>
```

### JavaScript
```javascript
// Obtener ID del proyecto de la URL
const urlParams = new URLSearchParams(window.location.search);
const projectId = urlParams.get('id');
document.getElementById('project_id').value = projectId;

// Manejar envío
document.getElementById('proposal-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const proposalData = {
        proyecto_id: parseInt(projectId),
        precio_propuesto: parseFloat(document.getElementById('precio_propuesto').value),
        tiempo_estimado: document.getElementById('tiempo_estimado').value,
        descripcion: document.getElementById('descripcion').value
    };
    
    try {
        const response = await APIClient.Proposals.create(proposalData);
        
        if (response.success) {
            alert('Propuesta enviada exitosamente');
            window.history.back();
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
```

## 5. Listar Propuestas de un Proyecto (Cliente)

### HTML
```html
<div id="proposals-list" class="space-y-4">
    <!-- Las propuestas se cargarán aquí -->
</div>
```

### JavaScript
```javascript
async function loadProposals() {
    const urlParams = new URLSearchParams(window.location.search);
    const projectId = urlParams.get('id');
    
    try {
        const response = await APIClient.Proposals.getByProject(projectId);
        const proposalsList = document.getElementById('proposals-list');
        
        if (response.success && response.proposals.length > 0) {
            proposalsList.innerHTML = response.proposals.map(proposal => `
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg border">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg">${proposal.nombre} ${proposal.apellido}</h3>
                            <p class="text-gray-600 dark:text-gray-400">${proposal.email}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-primary font-bold text-lg">$${proposal.precio_propuesto}</p>
                            <p class="text-sm text-gray-600">${proposal.tiempo_estimado}</p>
                        </div>
                    </div>
                    
                    <p class="mb-4">${proposal.descripcion}</p>
                    
                    <div class="flex gap-2">
                        <button onclick="acceptProposal(${proposal.id})" 
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Aceptar
                        </button>
                        <button onclick="rejectProposal(${proposal.id})" 
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Rechazar
                        </button>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error cargando propuestas:', error);
    }
}

async function acceptProposal(proposalId) {
    try {
        await APIClient.Proposals.updateStatus(proposalId, 'aceptada');
        alert('Propuesta aceptada');
        loadProposals();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function rejectProposal(proposalId) {
    try {
        await APIClient.Proposals.updateStatus(proposalId, 'rechazada');
        alert('Propuesta rechazada');
        loadProposals();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

document.addEventListener('DOMContentLoaded', loadProposals);
```

## 6. Buscar Desarrolladores

### HTML
```html
<div class="mb-4">
    <input type="text" id="search-input" placeholder="Buscar desarrolladores..." 
           class="w-full p-2 border rounded">
</div>

<div id="developers-list" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Los desarrolladores se mostrarán aquí -->
</div>
```

### JavaScript
```javascript
let searchTimeout;

document.getElementById('search-input').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(async () => {
        const query = e.target.value;
        
        if (query.length < 2) {
            document.getElementById('developers-list').innerHTML = '';
            return;
        }
        
        try {
            const response = await APIClient.Developers.search(query);
            const list = document.getElementById('developers-list');
            
            if (response.success && response.results.length > 0) {
                list.innerHTML = response.results.map(dev => `
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg border">
                        <h3 class="font-bold text-lg">${dev.nombre} ${dev.apellido}</h3>
                        <p class="text-gray-600 mb-2">${dev.email}</p>
                        <p class="text-yellow-500 mb-2">★ ${dev.calificacion_promedio}/5.0</p>
                        <p class="text-primary font-semibold mb-2">$${dev.tarifa_hora}/hora</p>
                        <p class="text-sm text-gray-600 mb-4">Habilidades: ${dev.habilidades}</p>
                        <a href="developer-profile.html?id=${dev.id}" 
                           class="text-primary hover:underline">Ver Perfil</a>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<p>No se encontraron desarrolladores</p>';
            }
        } catch (error) {
            console.error('Error buscando:', error);
        }
    }, 300);
});
```

## 7. Enviar Mensaje

### HTML
```html
<div id="chat-container" class="border rounded p-4 mb-4 h-96 overflow-y-auto">
    <div id="messages-list"></div>
</div>

<form id="message-form" class="flex gap-2">
    <input type="text" id="message-input" placeholder="Escribe tu mensaje..." 
           class="flex-1 p-2 border rounded" required>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Enviar
    </button>
</form>
```

### JavaScript
```javascript
const urlParams = new URLSearchParams(window.location.search);
const userId = urlParams.get('user_id');

async function loadMessages() {
    try {
        const response = await APIClient.Messages.getConversation(userId);
        const messagesList = document.getElementById('messages-list');
        
        if (response.success) {
            messagesList.innerHTML = response.messages.map(msg => `
                <div class="mb-4 ${msg.remitente_id === UserManager.getUser().id ? 'text-right' : 'text-left'}">
                    <div class="inline-block bg-gray-200 dark:bg-gray-700 p-3 rounded-lg">
                        <p>${msg.contenido}</p>
                        <small class="text-gray-600 dark:text-gray-400">
                            ${new Date(msg.creado_en).toLocaleTimeString()}
                        </small>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error cargando mensajes:', error);
    }
}

document.getElementById('message-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const contenido = document.getElementById('message-input').value;
    
    try {
        await APIClient.Messages.send(userId, contenido);
        document.getElementById('message-input').value = '';
        loadMessages();
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

// Cargar mensajes iniciales y actualizar cada 2 segundos
document.addEventListener('DOMContentLoaded', () => {
    loadMessages();
    setInterval(loadMessages, 2000);
});
```

## Tips y Buenas Prácticas

1. **Siempre verifica `response.success`** antes de usar los datos
2. **Usa try-catch** para manejar errores correctamente
3. **Valida datos en el cliente** antes de enviar al servidor
4. **Muestra mensajes de error** al usuario de forma clara
5. **Usa loading states** mientras esperas respuestas del API
6. **Actualiza dinámicamente** sin recargar la página completa
