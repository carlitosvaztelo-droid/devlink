/**
 * DevLink API Client
 * Cliente unificado para todas las llamadas API
 */

const APIClient = {
    baseUrl: '/api',
    
    // Headers predeterminados
    getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
    },
    
    // Método genérico para llamadas API
    async request(endpoint, method = 'GET', data = null) {
        const url = `${this.baseUrl}/${endpoint}`;
        const options = {
            method,
            headers: this.getHeaders()
        };
        
        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (!response.ok && !result.success) {
                throw new Error(result.error || 'Error en la solicitud');
            }
            
            return result;
        } catch (error) {
            console.error('[API Error]', error);
            throw error;
        }
    },
    
    // ============ AUTENTICACIÓN ============
    Auth: {
        async login(email, password, type) {
            return APIClient.request('auth.php?action=login', 'POST', {
                email, password, type
            });
        },
        
        async register(datos) {
            return APIClient.request('auth.php?action=register', 'POST', datos);
        },
        
        async logout() {
            return APIClient.request('auth.php?action=logout', 'GET');
        },
        
        async getCurrentUser() {
            return APIClient.request('auth.php?action=get-current-user', 'GET');
        },
        
        async updateProfile(datos) {
            return APIClient.request('auth.php?action=update-profile', 'POST', datos);
        }
    },
    
    // ============ PROYECTOS ============
    Projects: {
        async list(page = 1, limit = 10) {
            return APIClient.request(`projects.php?action=list&page=${page}&limit=${limit}`, 'GET');
        },
        
        async get(id) {
            return APIClient.request(`projects.php?action=get&id=${id}`, 'GET');
        },
        
        async create(datos) {
            return APIClient.request('projects.php?action=create', 'POST', datos);
        },
        
        async update(datos) {
            return APIClient.request('projects.php?action=update', 'POST', datos);
        },
        
        async delete(id) {
            return APIClient.request('projects.php?action=delete', 'POST', { id });
        },
        
        async search(query, filtros = {}) {
            let url = `projects.php?action=search&q=${encodeURIComponent(query)}`;
            if (filtros.categoria) url += `&categoria=${filtros.categoria}`;
            if (filtros.estado) url += `&estado=${filtros.estado}`;
            if (filtros.presupuesto_min) url += `&presupuesto_min=${filtros.presupuesto_min}`;
            if (filtros.presupuesto_max) url += `&presupuesto_max=${filtros.presupuesto_max}`;
            
            return APIClient.request(url, 'GET');
        },
        
        async getByClient(clientId) {
            return APIClient.request(`projects.php?action=get-by-client&client_id=${clientId}`, 'GET');
        },
        
        async getActive() {
            return APIClient.request('projects.php?action=get-active', 'GET');
        }
    },
    
    // ============ PROPUESTAS ============
    Proposals: {
        async list(page = 1, limit = 10) {
            return APIClient.request(`proposals.php?action=list&page=${page}&limit=${limit}`, 'GET');
        },
        
        async create(datos) {
            return APIClient.request('proposals.php?action=create', 'POST', datos);
        },
        
        async getByProject(projectId) {
            return APIClient.request(`proposals.php?action=get-by-project&project_id=${projectId}`, 'GET');
        },
        
        async listByProject() {
            return APIClient.request('proposals.php?action=get-by-project', 'GET');
        },
        
        async getByDeveloper() {
            return APIClient.request('proposals.php?action=get-by-developer', 'GET');
        },
        
        async updateStatus(id, status) {
            return APIClient.request('proposals.php?action=update-status', 'POST', { id, status });
        }
    },
    
    // ============ DESARROLLADORES ============
    Developers: {
        async list(page = 1, limit = 10) {
            return APIClient.request(`developers.php?action=list&page=${page}&limit=${limit}`, 'GET');
        },
        
        async get(id) {
            return APIClient.request(`developers.php?action=get&id=${id}`, 'GET');
        },
        
        async getProfile() {
            return APIClient.request('developers.php?action=get-profile', 'GET');
        },
        
        async getTopRated(limit = 10) {
            return APIClient.request(`developers.php?action=get-top-rated&limit=${limit}`, 'GET');
        },
        
        async updateProfile(datos) {
            return APIClient.request('developers.php?action=update-profile', 'POST', datos);
        },
        
        async addSkill(skillId) {
            return APIClient.request('developers.php?action=add-skill', 'POST', { skill_id: skillId });
        },
        
        async removeSkill(skillId) {
            return APIClient.request('developers.php?action=remove-skill', 'POST', { skill_id: skillId });
        },
        
        async search(query, filtros = {}) {
            let url = `developers.php?action=search&q=${encodeURIComponent(query)}`;
            if (filtros.skill) url += `&skill=${encodeURIComponent(filtros.skill)}`;
            if (filtros.min_rating) url += `&min_rating=${filtros.min_rating}`;
            if (filtros.max_price) url += `&max_price=${filtros.max_price}`;
            
            return APIClient.request(url, 'GET');
        }
    },
    
    // ============ CONTRATOS ============
    Contracts: {
        async list(page = 1, limit = 10) {
            return APIClient.request(`contracts.php?action=list&page=${page}&limit=${limit}`, 'GET');
        },
        
        async create(datos) {
            return APIClient.request('contracts.php?action=create', 'POST', datos);
        },
        
        async get(id) {
            return APIClient.request(`contracts.php?action=get&id=${id}`, 'GET');
        },
        
        async updateStatus(id, status) {
            return APIClient.request('contracts.php?action=update-status', 'POST', { id, status });
        },
        
        async getByUser(type) {
            return APIClient.request(`contracts.php?action=get-by-user&type=${type}`, 'GET');
        },
        
        async getByProject(projectId) {
            return APIClient.request(`contracts.php?action=get-by-project&project_id=${projectId}`, 'GET');
        }
    },
    
    // ============ MENSAJES ============
    Messages: {
        async list(page = 1, limit = 50) {
            return APIClient.request(`messages.php?action=list&page=${page}&limit=${limit}`, 'GET');
        },
        
        async send(destinatarioId, contenido, proyectoId = null) {
            return APIClient.request('messages.php?action=send', 'POST', {
                destinatario_id: destinatarioId,
                contenido,
                proyecto_id: proyectoId
            });
        },
        
        async getConversation(userId) {
            return APIClient.request(`messages.php?action=get-conversation&user_id=${userId}`, 'GET');
        },
        
        async getConversations() {
            return APIClient.request('messages.php?action=get-conversations', 'GET');
        },
        
        async markAsRead(messageId) {
            return APIClient.request('messages.php?action=mark-as-read', 'POST', { message_id: messageId });
        }
    },
    
    // ============ BÚSQUEDA ============
    Search: {
        async search(query) {
            return APIClient.request(`search.php?action=global&q=${encodeURIComponent(query)}`, 'GET');
        },
        
        async global(query) {
            return APIClient.request(`search.php?action=global&q=${encodeURIComponent(query)}`, 'GET');
        },
        
        async projects(query, filtros = {}) {
            let url = `search.php?action=projects&q=${encodeURIComponent(query)}`;
            if (filtros.categoria) url += `&categoria=${filtros.categoria}`;
            if (filtros.estado) url += `&estado=${filtros.estado}`;
            if (filtros.presupuesto_min) url += `&presupuesto_min=${filtros.presupuesto_min}`;
            if (filtros.presupuesto_max) url += `&presupuesto_max=${filtros.presupuesto_max}`;
            
            return APIClient.request(url, 'GET');
        },
        
        async developers(query, filtros = {}) {
            let url = `search.php?action=developers&q=${encodeURIComponent(query)}`;
            if (filtros.skill) url += `&skill=${encodeURIComponent(filtros.skill)}`;
            if (filtros.min_rating) url += `&min_rating=${filtros.min_rating}`;
            if (filtros.max_price) url += `&max_price=${filtros.max_price}`;
            
            return APIClient.request(url, 'GET');
        },
        
        async getCategories() {
            return APIClient.request('search.php?action=categories', 'GET');
        },
        
        async getSkills() {
            return APIClient.request('search.php?action=skills', 'GET');
        }
    },
    
    // ============ PAGOS ============
    Payments: {
        async getBalance() {
            return APIClient.request('payments.php?action=get-balance', 'GET');
        },
        
        async getTransactions(page = 1, limit = 20) {
            return APIClient.request(`payments.php?action=get-transactions&page=${page}&limit=${limit}`, 'GET');
        },
        
        async getTotalEarnings() {
            return APIClient.request('payments.php?action=get-total-earnings', 'GET');
        }
    }
};

// Hacer disponible globalmente
if (typeof module !== 'undefined' && module.exports) {
    module.exports = APIClient;
}
