/**
 * DevLink Shared Utilities
 * Gestión centralizada de usuarios, temas, navegación y estado global
 */

// ============================================
// 1. GESTIÓN DE USUARIO (localStorage)
// ============================================
const UserManager = {
    storageKey: 'devlink_demo_user',
    
    getUser() {
        const stored = localStorage.getItem(this.storageKey);
        return stored ? JSON.parse(stored) : this.getDefaultUser();
    },
    
    getDefaultUser() {
        return { 
            type: 'dev', 
            nombre: 'Usuario', 
            apellido: 'Demo',
            id: 'user-001',
            email: 'usuario@devlink.ve',
            experiencia: 'intermedio',
            ubicacion: 'Guanare'
        };
    },
    
    setUser(userData) {
        const user = { ...this.getDefaultUser(), ...userData };
        localStorage.setItem(this.storageKey, JSON.stringify(user));
        window.dispatchEvent(new CustomEvent('userChanged', { detail: user }));
        return user;
    },
    
    isClient() {
        return this.getUser().type === 'client';
    },
    
    isDeveloper() {
        return this.getUser().type === 'dev';
    },
    
    logout() {
        localStorage.removeItem(this.storageKey);
        localStorage.removeItem('color-theme');
        window.location.href = 'auth.html';
    },
    
    getNombreCompleto() {
        const user = this.getUser();
        return `${user.nombre} ${user.apellido}`;
    },
    
    getInitials() {
        const user = this.getUser();
        return `${user.nombre[0]}${user.apellido[0]}`.toUpperCase();
    }
};

// ============================================
// 2. GESTIÓN DE TEMA (Dark/Light)
// ============================================
const ThemeManager = {
    storageKey: 'color-theme',
    
    init() {
        const isDark = localStorage.getItem(this.storageKey) === 'dark' ||
                      (!localStorage.getItem(this.storageKey) && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        if (isDark) {
            this.setDark();
        } else {
            this.setLight();
        }
    },
    
    isDark() {
        return document.documentElement.classList.contains('dark');
    },
    
    setDark() {
        document.documentElement.classList.add('dark');
        localStorage.setItem(this.storageKey, 'dark');
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: 'dark' }));
    },
    
    setLight() {
        document.documentElement.classList.remove('dark');
        localStorage.setItem(this.storageKey, 'light');
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: 'light' }));
    },
    
    toggle() {
        if (this.isDark()) {
            this.setLight();
        } else {
            this.setDark();
        }
    }
};

// ============================================
// 3. GENERADOR DE NAVEGACIÓN
// ============================================
const NavigationGenerator = {
    generateSidebar(currentPage = '') {
        const user = UserManager.getUser();
        const isClient = user.type === 'client';
        const dashboardUrl = isClient ? 'dashboard_cliente.html' : 'dashboard_desarrollador.html';
        
        const nav = {
            client: [
                { icon: 'fa-th-large', label: 'Dashboard', url: 'dashboard_cliente.html' },
                { icon: 'fa-search', label: 'Buscar Talento', url: 'search.html' },
                { icon: 'fa-layer-group', label: 'Mis Publicaciones', url: 'projects.html' },
                { icon: 'fa-file-alt', label: 'Candidatos', url: 'proposals.html' },
                { icon: 'fa-file-signature', label: 'Contratos', url: 'contracts.html' },
                { icon: 'fa-comment-dots', label: 'Mensajes', url: 'messages.html' },
                { icon: 'fa-wallet', label: 'Pagos', url: 'payments.html' },
                { icon: 'fa-user-circle', label: 'Mi Perfil', url: 'profile.html' },
                { icon: 'fa-question-circle', label: 'Ayuda', url: 'help.html' }
            ],
            dev: [
                { icon: 'fa-th-large', label: 'Dashboard', url: 'dashboard_desarrollador.html' },
                { icon: 'fa-search', label: 'Buscar Proyectos', url: 'search.html' },
                { icon: 'fa-paper-plane', label: 'Mis Postulaciones', url: 'proposals.html' },
                { icon: 'fa-file-signature', label: 'Contratos', url: 'contracts.html' },
                { icon: 'fa-comment-dots', label: 'Mensajes', url: 'messages.html' },
                { icon: 'fa-wallet', label: 'Pagos', url: 'payments.html' },
                { icon: 'fa-user-circle', label: 'Mi Perfil', url: 'profile.html' },
                { icon: 'fa-question-circle', label: 'Ayuda', url: 'help.html' }
            ]
        };
        
        const navItems = isClient ? nav.client : nav.dev;
        let html = '';
        
        navItems.forEach(item => {
            const isActive = currentPage && currentPage.includes(item.url.split('.')[0]);
            const activeClass = isActive ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-gray-500 hover:text-primary hover:bg-blue-50 dark:hover:bg-gray-800';
            
            html += `
                <a href="${item.url}" class="flex items-center gap-3 px-4 py-3 ${activeClass} rounded-xl transition-all">
                    <i class="fas ${item.icon} w-5"></i>
                    <span class="font-medium hidden lg:block">${item.label}</span>
                </a>
            `;
        });
        
        return html;
    },
    
    initSidebar(currentPage = '') {
        const navElement = document.getElementById('sidebar-nav');
        if (navElement) {
            navElement.innerHTML = this.generateSidebar(currentPage);
        }
    }
};

// ============================================
// 4. GESTIÓN DE DATOS SIMULADOS
// ============================================
const DataManager = {
    mockProjects: [
        {
            id: 1,
            titulo: 'Desarrollo de App Delivery para Guanare',
            descripcion: 'Buscamos un desarrollador fullstack para crear una aplicación móvil de delivery local con geolocalización...',
            presupuesto: { min: 800, max: 1200 },
            habilidades: ['Mobile Dev', 'Geolocalización', 'Backend'],
            estado: 'abierto',
            propuestas: 12,
            icono: 'fa-shopping-cart'
        },
        {
            id: 2,
            titulo: 'Sistema de Inventario IUTEPI',
            descripcion: 'Desarrollo de un sistema web para gestión de inventario con reportes y estadísticas...',
            presupuesto: { min: 500, max: 800 },
            habilidades: ['PHP', 'Laravel', 'SQL'],
            estado: 'en-progreso',
            icono: 'fa-boxes'
        }
    ],
    
    mockDevelopers: [
        {
            id: 1,
            nombre: 'Juan',
            apellido: 'Sánchez',
            experiencia: 'senior',
            habilidades: ['React', 'Node.js', 'MongoDB'],
            calificacion: 4.9,
            ubicacion: 'Guanare',
            tarifa: 50
        },
        {
            id: 2,
            nombre: 'María',
            apellido: 'García',
            experiencia: 'intermedio',
            habilidades: ['PHP', 'Laravel', 'MySQL'],
            calificacion: 4.8,
            ubicacion: 'Guanare',
            tarifa: 35
        }
    ],
    
    mockMessages: [
        {
            id: 1,
            from: 'Juan Sánchez',
            fromType: 'dev',
            content: 'Hola, ¿cómo vas con los avances del Hito 1?',
            timestamp: '10:45 AM'
        }
    ],
    
    getProjects() {
        return this.mockProjects;
    },
    
    getDevelopers() {
        return this.mockDevelopers;
    },
    
    getMessages() {
        return this.mockMessages;
    }
};

// ============================================
// 5. INICIALIZACIÓN GLOBAL
// ============================================
const DevLinkInit = {
    init() {
        // Inicializar tema
        ThemeManager.init();
        
        // Configurar listeners de tema
        const themeToggleButtons = document.querySelectorAll('#theme-toggle, [data-theme-toggle]');
        themeToggleButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                ThemeManager.toggle();
                this.updateThemeUI();
            });
        });
        
        // Configurar botón de logout
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => UserManager.logout());
        }
        
        // Actualizar avatar y nombre de usuario en header
        this.updateUserUI();
        
        // Escuchar cambios de usuario
        window.addEventListener('userChanged', () => this.updateUserUI());
    },
    
    updateUserUI() {
        const user = UserManager.getUser();
        
        // Actualizar nombre en bienvenida
        const welcomeElement = document.getElementById('welcome-name');
        if (welcomeElement) {
            welcomeElement.textContent = user.nombre;
        }
        
        // Actualizar avatar
        const avatarElements = document.querySelectorAll('#user-avatar, [data-user-avatar]');
        avatarElements.forEach(el => {
            el.textContent = user.nombre[0];
        });
        
        // Actualizar nombre completo en formularios
        const fullNameElements = document.querySelectorAll('[data-user-fullname]');
        fullNameElements.forEach(el => {
            el.textContent = UserManager.getNombreCompleto();
        });
    },
    
    updateThemeUI() {
        // Actualizar iconos de tema
        const sunIcon = document.getElementById('theme-icon-sun');
        const moonIcon = document.getElementById('theme-icon-moon');
        const themeIcon = document.getElementById('theme-icon');
        const themeText = document.getElementById('theme-text');
        
        if (sunIcon && moonIcon) {
            if (ThemeManager.isDark()) {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            } else {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            }
        }
        
        if (themeIcon && themeText) {
            if (ThemeManager.isDark()) {
                themeIcon.className = 'fas fa-sun w-5';
                themeText.textContent = 'Modo Claro';
            } else {
                themeIcon.className = 'fas fa-moon w-5';
                themeText.textContent = 'Modo Oscuro';
            }
        }
    }
};

// ============================================
// 6. UTILIDADES DE NAVEGACIÓN
// ============================================
const NavigationUtil = {
    goToProject(projectId) {
        localStorage.setItem('selectedProjectId', projectId);
        window.location.href = 'projects.html';
    },
    
    goToDeveloper(developerId) {
        localStorage.setItem('selectedDeveloperId', developerId);
        window.location.href = 'profile.html';
    },
    
    goToContract(contractId) {
        localStorage.setItem('selectedContractId', contractId);
        window.location.href = 'contracts.html';
    },
    
    goToMessages(userId) {
        localStorage.setItem('selectedChatUserId', userId);
        window.location.href = 'messages.html';
    },
    
    goToDashboard() {
        const user = UserManager.getUser();
        const dashboardUrl = user.type === 'client' ? 'dashboard_cliente.html' : 'dashboard_desarrollador.html';
        window.location.href = dashboardUrl;
    }
};

// ============================================
// 7. INICIALIZACIÓN AUTOMÁTICA
// ============================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        DevLinkInit.init();
    });
} else {
    DevLinkInit.init();
}
