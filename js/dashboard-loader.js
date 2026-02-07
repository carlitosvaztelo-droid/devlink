/**
 * Dashboard Data Loader
 * Carga y renderiza datos reales desde la API en las secciones SPA
 */

const DashboardLoader = {
    currentUser: null,
    cache: {},
    
    // Inicializar con datos del usuario actual
    async init(userType) {
        try {
            const userResponse = await APIClient.Auth.getCurrentUser();
            this.currentUser = userResponse.user || UserManager.getUser();
        } catch (error) {
            console.error('[DashboardLoader] Error cargando usuario:', error);
            this.currentUser = UserManager.getUser();
        }
    },
    
    // Renderizar sección HOME con estadísticas reales
    async renderHome(container, userType) {
        try {
            container.innerHTML = '<div class="text-center py-12"><p>Cargando datos del dashboard...</p></div>';
            
            let stats = { projects: 0, proposals: 0, contracts: 0, balance: '0' };
            let projects = [];
            
            if (userType === 'cliente') {
                // Para clientes: proyectos publicados y propuestas recibidas
                try {
                    const projectsData = await APIClient.Projects.list(1, 3);
                    projects = projectsData.projects || [];
                    stats.projects = projects.length;
                } catch (e) {
                    console.log('[DashboardLoader] Projects list error:', e);
                }
                
                try {
                    const proposalsData = await APIClient.Proposals.list(1, 50);
                    stats.proposals = (proposalsData.proposals || []).length;
                } catch (e) {
                    console.log('[DashboardLoader] Proposals error:', e);
                }
            } else {
                // Para desarrolladores: propuestas enviadas y contratos
                try {
                    const proposalsData = await APIClient.Proposals.list(1, 50);
                    stats.proposals = (proposalsData.proposals || []).length;
                } catch (e) {
                    console.log('[DashboardLoader] Proposals error:', e);
                }
                
                try {
                    const contractsData = await APIClient.Contracts.list(1, 50);
                    stats.contracts = (contractsData.contracts || []).length;
                } catch (e) {
                    console.log('[DashboardLoader] Contracts error:', e);
                }
            }
            
            // Actualizar estadísticas en el DOM
            const statElements = document.querySelectorAll('[id^="stat-"]');
            statElements.forEach(el => {
                if (el.id === 'stat-projects') el.textContent = stats.projects;
                if (el.id === 'stat-proposals') el.textContent = stats.proposals;
                if (el.id === 'stat-contracts') el.textContent = stats.contracts;
                if (el.id === 'stat-balance') el.textContent = 'REF ' + stats.balance + 'k';
            });
            
            // Renderizar proyectos recientes si existen
            if (projects.length > 0) {
                const recentProjectsHtml = projects.map(project => `
                    <div class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-2xl transition-colors cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 text-primary rounded-xl flex items-center justify-center text-xl">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">${project.titulo || 'Sin título'}</h4>
                                <p class="text-xs text-gray-500">${project.descripcion ? project.descripcion.substring(0, 50) + '...' : 'Sin descripción'}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">${project.estado || 'ACTIVO'}</span>
                    </div>
                `).join('');
                
                const projectsContainer = document.getElementById('recent-projects') || document.getElementById('pending-deliveries');
                if (projectsContainer) {
                    projectsContainer.innerHTML = recentProjectsHtml;
                }
            }
            
        } catch (error) {
            console.error('[DashboardLoader] Error en renderHome:', error);
            container.innerHTML = '<div class="text-center py-12 text-red-500"><p>Error cargando datos del dashboard</p></div>';
        }
    },
    
    // Renderizar sección SEARCH
    async renderSearch(container, userType) {
        try {
            container.innerHTML = `
                <div class="max-w-7xl mx-auto">
                    <h2 class="text-2xl font-bold mb-8">Búsqueda</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <input type="text" id="search-input" placeholder="Buscar..." class="col-span-2 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                        <button onclick="DashboardLoader.performSearch('${userType}')" class="px-4 py-3 bg-primary text-white rounded-lg font-bold hover:bg-primary-dark transition-colors">
                            Buscar
                        </button>
                    </div>
                    <div id="search-results" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <p class="text-gray-500">Escribe algo y presiona buscar</p>
                    </div>
                </div>
            `;
        } catch (error) {
            container.innerHTML = '<div class="text-center py-12 text-red-500"><p>Error en búsqueda</p></div>';
        }
    },
    
    // Realizar búsqueda
    async performSearch(userType) {
        const query = document.getElementById('search-input')?.value || '';
        const resultsContainer = document.getElementById('search-results');
        
        if (!query.trim()) {
            resultsContainer.innerHTML = '<p class="text-gray-500">Por favor ingresa un término de búsqueda</p>';
            return;
        }
        
        resultsContainer.innerHTML = '<p class="text-gray-500">Buscando...</p>';
        
        try {
            const results = await APIClient.Search.search(query);
            
            let html = '';
            if (results.results && results.results.length > 0) {
                html = results.results.map(item => `
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-800 hover:shadow-lg transition-shadow">
                        <h3 class="font-bold text-lg mb-2">${item.titulo || item.nombre}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">${item.descripcion || ''}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">${item.tipo || 'Item'}</span>
                            ${item.presupuesto ? `<span class="font-bold">$${item.presupuesto}</span>` : ''}
                        </div>
                    </div>
                `).join('');
            } else {
                html = '<p class="text-gray-500 col-span-2">No se encontraron resultados</p>';
            }
            
            resultsContainer.innerHTML = html;
        } catch (error) {
            resultsContainer.innerHTML = '<p class="text-red-500">Error en la búsqueda</p>';
        }
    },
    
    // Renderizar sección PROJECTS
    async renderProjects(container) {
        try {
            container.innerHTML = '<div class="text-center py-12"><p>Cargando proyectos...</p></div>';
            
            const response = await APIClient.Projects.list(1, 20);
            const projects = response.projects || [];
            
            let html = `
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-2xl font-bold">Mis Publicaciones</h2>
                        <button class="px-4 py-2 bg-primary text-white rounded-lg font-bold hover:bg-primary-dark">
                            + Nuevo Proyecto
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            `;
            
            if (projects.length > 0) {
                html += projects.map(project => `
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-800 hover:shadow-lg transition-shadow">
                        <h3 class="font-bold text-lg mb-2">${project.titulo}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">${project.descripcion ? project.descripcion.substring(0, 100) + '...' : ''}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">${project.categoria || 'General'}</span>
                            <span class="font-bold">$${project.presupuesto || '0'}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">${project.estado || 'Activo'}</p>
                    </div>
                `).join('');
            } else {
                html += '<p class="col-span-3 text-center text-gray-500">No hay proyectos publicados</p>';
            }
            
            html += '</div></div>';
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = '<div class="text-center py-12 text-red-500"><p>Error cargando proyectos</p></div>';
        }
    },
    
    // Renderizar sección CONTRACTS
    async renderContracts(container) {
        try {
            container.innerHTML = '<div class="text-center py-12"><p>Cargando contratos...</p></div>';
            
            const response = await APIClient.Contracts.list(1, 20);
            const contracts = response.contracts || [];
            
            let html = `
                <div class="max-w-7xl mx-auto">
                    <h2 class="text-2xl font-bold mb-8">Contratos</h2>
                    <div class="grid grid-cols-1 gap-4">
            `;
            
            if (contracts.length > 0) {
                html += contracts.map(contract => `
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-800">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg">${contract.titulo || 'Contrato'}</h3>
                                <p class="text-sm text-gray-500">${contract.descripcion || ''}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-bold rounded-full ${this.getStatusColor(contract.estado)}">
                                ${contract.estado || 'Pendiente'}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div><p class="text-gray-500">Monto</p><p class="font-bold">$${contract.monto || '0'}</p></div>
                            <div><p class="text-gray-500">Fecha Inicio</p><p class="font-bold">${contract.fecha_inicio || 'N/A'}</p></div>
                            <div><p class="text-gray-500">Fecha Fin</p><p class="font-bold">${contract.fecha_fin || 'N/A'}</p></div>
                        </div>
                    </div>
                `).join('');
            } else {
                html += '<p class="col-span-3 text-center text-gray-500">No hay contratos</p>';
            }
            
            html += '</div></div>';
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = '<div class="text-center py-12 text-red-500"><p>Error cargando contratos</p></div>';
        }
    },
    
    // Renderizar sección MESSAGES
    async renderMessages(container) {
        try {
            container.innerHTML = '<div class="text-center py-12"><p>Cargando mensajes...</p></div>';
            
            const response = await APIClient.Messages.list(1, 50);
            const messages = response.messages || [];
            
            let html = `
                <div class="max-w-7xl mx-auto">
                    <h2 class="text-2xl font-bold mb-8">Mensajes</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="font-bold">Conversaciones</h3>
                            </div>
                            <div id="conversations-list" class="divide-y divide-gray-200 dark:divide-gray-800">
            `;
            
            if (messages.length > 0) {
                html += messages.slice(0, 10).map((msg, idx) => `
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer border-l-4 ${idx === 0 ? 'border-primary' : 'border-transparent'}">
                        <p class="font-bold text-sm">${msg.de_usuario || 'Usuario'}</p>
                        <p class="text-xs text-gray-500 truncate">${msg.mensaje ? msg.mensaje.substring(0, 40) + '...' : ''}</p>
                    </div>
                `).join('');
            } else {
                html += '<p class="p-4 text-gray-500 text-sm">No hay mensajes</p>';
            }
            
            html += `
                            </div>
                        </div>
                        <div class="md:col-span-2 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-6 flex flex-col">
                            <div class="flex-1">
                                <p class="text-gray-500 text-center">Selecciona una conversación</p>
                            </div>
                            <div class="mt-4 border-t border-gray-200 dark:border-gray-800 pt-4">
                                <div class="flex gap-2">
                                    <input type="text" placeholder="Escribe un mensaje..." class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                    <button class="px-4 py-2 bg-primary text-white rounded-lg font-bold hover:bg-primary-dark">Enviar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = '<div class="text-center py-12 text-red-500"><p>Error cargando mensajes</p></div>';
        }
    },
    
    // Renderizar sección PROPOSALS
    async renderProposals(container) {
        try {
            container.innerHTML = '<div class="text-center py-12"><p>Cargando propuestas...</p></div>';
            
            const response = await APIClient.Proposals.list(1, 20);
            const proposals = response.proposals || [];
            
            let html = `
                <div class="max-w-7xl mx-auto">
                    <h2 class="text-2xl font-bold mb-8">Propuestas</h2>
                    <div class="grid grid-cols-1 gap-4">
            `;
            
            if (proposals.length > 0) {
                html += proposals.map(proposal => `
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-800">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-lg">${proposal.titulo || 'Propuesta'}</h3>
                                <p class="text-sm text-gray-500 mb-2">${proposal.descripcion || ''}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-bold rounded-full ${this.getStatusColor(proposal.estado)}">
                                ${proposal.estado || 'Pendiente'}
                            </span>
                        </div>
                        <p class="font-bold text-primary mt-4">$${proposal.monto || '0'}</p>
                    </div>
                `).join('');
            } else {
                html += '<p class="col-span-3 text-center text-gray-500">No hay propuestas</p>';
            }
            
            html += '</div></div>';
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = '<div class="text-center py-12 text-red-500"><p>Error cargando propuestas</p></div>';
        }
    },
    
    // Renderizar sección PAYMENTS
    async renderPayments(container) {
        try {
            container.innerHTML = '<div class="text-center py-12"><p>Cargando información de pagos...</p></div>';
            
            let html = `
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-2xl font-bold mb-8">Pagos</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-800">
                            <p class="text-gray-500 text-sm">Balance Disponible</p>
                            <p class="text-3xl font-bold text-primary">$0.00</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-800">
                            <p class="text-gray-500 text-sm">Ingresos Totales</p>
                            <p class="text-3xl font-bold">$0.00</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-800">
                            <p class="text-gray-500 text-sm">Pagos Realizados</p>
                            <p class="text-3xl font-bold">$0.00</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-800">
                        <h3 class="font-bold text-lg mb-4">Historial de Transacciones</h3>
                        <p class="text-gray-500 text-center py-8">No hay transacciones</p>
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = '<div class="text-center py-12 text-red-500"><p>Error cargando información de pagos</p></div>';
        }
    },
    
    // Renderizar sección PROFILE
    async renderProfile(container) {
        try {
            container.innerHTML = '<div class="text-center py-12"><p>Cargando perfil...</p></div>';
            
            const user = this.currentUser || UserManager.getUser();
            
            let html = `
                <div class="max-w-2xl mx-auto">
                    <h2 class="text-2xl font-bold mb-8">Mi Perfil</h2>
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-8">
                        <div class="flex items-center gap-6 mb-8">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-primary to-purple-500 flex items-center justify-center text-white text-2xl font-bold">
                                ${user.nombre ? user.nombre[0].toUpperCase() : 'U'}
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">${user.nombre || 'Usuario'} ${user.apellido || ''}</h3>
                                <p class="text-gray-500">${user.email || 'email@example.com'}</p>
                                <p class="text-xs text-gray-500 mt-2">Rol: <span class="font-bold capitalize">${user.tipo || 'usuario'}</span></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold mb-2">Nombre</label>
                                <input type="text" value="${user.nombre || ''}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Apellido</label>
                                <input type="text" value="${user.apellido || ''}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary" />
                            </div>
                        </div>
                        <div class="mt-6">
                            <label class="block text-sm font-bold mb-2">Email</label>
                            <input type="email" value="${user.email || ''}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary" />
                        </div>
                        <button class="mt-8 w-full px-6 py-3 bg-primary text-white rounded-lg font-bold hover:bg-primary-dark transition-colors">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = '<div class="text-center py-12 text-red-500"><p>Error cargando perfil</p></div>';
        }
    },
    
    // Utilidad: obtener color por estado
    getStatusColor(estado) {
        const colors = {
            'activo': 'bg-green-100 text-green-700',
            'pendiente': 'bg-yellow-100 text-yellow-700',
            'aceptado': 'bg-blue-100 text-blue-700',
            'rechazado': 'bg-red-100 text-red-700',
            'completado': 'bg-green-100 text-green-700',
            'en proceso': 'bg-blue-100 text-blue-700'
        };
        return colors[estado?.toLowerCase()] || 'bg-gray-100 text-gray-700';
    }
};
