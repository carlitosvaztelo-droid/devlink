<?php
// api/search.php
// Endpoint de búsqueda unificada

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

$action = $_GET['action'] ?? '';
$query = $_GET['q'] ?? '';

try {
    switch ($action) {
        case 'global':
            globalSearch($pdo, $query);
            break;
            
        case 'projects':
            searchProjects($pdo, $query);
            break;
            
        case 'developers':
            searchDevelopers($pdo, $query);
            break;
            
        case 'categories':
            getCategories($pdo);
            break;
            
        case 'skills':
            getSkills($pdo);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function globalSearch($pdo, $query) {
    if (empty($query)) {
        echo json_encode([
            'success' => true,
            'projects' => [],
            'developers' => [],
            'query' => $query
        ]);
        return;
    }
    
    $searchTerm = "%$query%";
    
    // Buscar proyectos
    $projectsStmt = $pdo->prepare("
        SELECT p.*, c.nombre as categoria
        FROM proyectos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE (p.titulo LIKE :search OR p.descripcion LIKE :search)
        AND p.estado IN ('abierto', 'en_progreso')
        LIMIT 5
    ");
    $projectsStmt->execute([':search' => $searchTerm]);
    $projects = $projectsStmt->fetchAll();
    
    // Buscar desarrolladores
    $developersStmt = $pdo->prepare("
        SELECT u.id, u.nombre, u.apellido, u.email, dp.calificacion_promedio, dp.tarifa_hora,
               GROUP_CONCAT(h.nombre SEPARATOR ', ') as habilidades
        FROM usuarios u
        LEFT JOIN desarrolladores_perfiles dp ON u.id = dp.usuario_id
        LEFT JOIN desarrollador_habilidades dh ON dp.id = dh.desarrollador_id
        LEFT JOIN habilidades h ON dh.habilidad_id = h.id
        WHERE u.tipo = 'desarrollador' AND (u.nombre LIKE :search OR u.apellido LIKE :search)
        GROUP BY u.id
        LIMIT 5
    ");
    $developersStmt->execute([':search' => $searchTerm]);
    $developers = $developersStmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'projects' => $projects,
        'developers' => $developers,
        'query' => $query
    ]);
}

function searchProjects($pdo, $query) {
    if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetro de búsqueda requerido']);
        return;
    }
    
    $filters = [
        'categoria' => $_GET['categoria'] ?? '',
        'estado' => $_GET['estado'] ?? '',
        'presupuesto_min' => $_GET['presupuesto_min'] ?? '',
        'presupuesto_max' => $_GET['presupuesto_max'] ?? ''
    ];
    
    $sql = "
        SELECT p.*, c.nombre as categoria
        FROM proyectos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE (p.titulo LIKE :search OR p.descripcion LIKE :search)
    ";
    $params = [':search' => "%$query%"];
    
    if (!empty($filters['categoria'])) {
        $sql .= " AND p.categoria_id = :categoria";
        $params[':categoria'] = $filters['categoria'];
    }
    
    if (!empty($filters['estado'])) {
        $sql .= " AND p.estado = :estado";
        $params[':estado'] = $filters['estado'];
    }
    
    if (!empty($filters['presupuesto_min'])) {
        $sql .= " AND p.presupuesto_max >= :presupuesto_min";
        $params[':presupuesto_min'] = $filters['presupuesto_min'];
    }
    
    if (!empty($filters['presupuesto_max'])) {
        $sql .= " AND p.presupuesto_min <= :presupuesto_max";
        $params[':presupuesto_max'] = $filters['presupuesto_max'];
    }
    
    $sql .= " ORDER BY p.creado_en DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'count' => count($results)
    ]);
}

function searchDevelopers($pdo, $query) {
    if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetro de búsqueda requerido']);
        return;
    }
    
    $filters = [
        'skill' => $_GET['skill'] ?? '',
        'min_rating' => $_GET['min_rating'] ?? '',
        'max_price' => $_GET['max_price'] ?? ''
    ];
    
    $sql = "
        SELECT u.id, u.nombre, u.apellido, u.email, u.telefono,
               dp.calificacion_promedio, dp.tarifa_hora, dp.experiencia_años, dp.disponible,
               GROUP_CONCAT(h.nombre SEPARATOR ', ') as habilidades
        FROM usuarios u
        LEFT JOIN desarrolladores_perfiles dp ON u.id = dp.usuario_id
        LEFT JOIN desarrollador_habilidades dh ON dp.id = dh.desarrollador_id
        LEFT JOIN habilidades h ON dh.habilidad_id = h.id
        WHERE u.tipo = 'desarrollador' AND (u.nombre LIKE :search OR u.apellido LIKE :search OR u.email LIKE :search)
    ";
    $params = [':search' => "%$query%"];
    
    if (!empty($filters['skill'])) {
        $sql .= " AND h.nombre = :skill";
        $params[':skill'] = $filters['skill'];
    }
    
    if (!empty($filters['min_rating'])) {
        $sql .= " AND dp.calificacion_promedio >= :min_rating";
        $params[':min_rating'] = $filters['min_rating'];
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND dp.tarifa_hora <= :max_price";
        $params[':max_price'] = $filters['max_price'];
    }
    
    $sql .= " GROUP BY u.id ORDER BY dp.calificacion_promedio DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'count' => count($results)
    ]);
}

function getCategories($pdo) {
    $stmt = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
    $categories = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'categories' => $categories]);
}

function getSkills($pdo) {
    $stmt = $pdo->query("SELECT id, nombre FROM habilidades ORDER BY nombre ASC");
    $skills = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'skills' => $skills]);
}
?>
