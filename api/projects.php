<?php
// api/projects.php
// Endpoints para proyectos

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listProjects($pdo);
            break;
            
        case 'get':
            getProject($pdo);
            break;
            
        case 'create':
            createProject($pdo);
            break;
            
        case 'update':
            updateProject($pdo);
            break;
            
        case 'delete':
            deleteProject($pdo);
            break;
            
        case 'search':
            searchProjects($pdo);
            break;
            
        case 'get-by-client':
            getProjectsByClient($pdo);
            break;
            
        case 'get-active':
            getActiveProjects($pdo);
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

function listProjects($pdo) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre as categoria, cp.nombre_empresa, u.nombre, u.apellido
        FROM proyectos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN clientes_perfiles cp ON p.cliente_id = cp.id
        LEFT JOIN usuarios u ON cp.usuario_id = u.id
        ORDER BY p.creado_en DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $projects = $stmt->fetchAll();
    
    // Contar total
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM proyectos");
    $countResult = $countStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'projects' => $projects,
        'total' => $countResult['total'],
        'page' => $page,
        'limit' => $limit
    ]);
}

function getProject($pdo) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de proyecto requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre as categoria, cp.nombre_empresa, u.nombre, u.apellido, u.email
        FROM proyectos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN clientes_perfiles cp ON p.cliente_id = cp.id
        LEFT JOIN usuarios u ON cp.usuario_id = u.id
        WHERE p.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        http_response_code(404);
        echo json_encode(['error' => 'Proyecto no encontrado']);
        return;
    }
    
    // Incrementar vistas
    $updateStmt = $pdo->prepare("UPDATE proyectos SET vistas = vistas + 1 WHERE id = :id");
    $updateStmt->execute([':id' => $id]);
    
    echo json_encode(['success' => true, 'project' => $project]);
}

function createProject($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Obtener cliente_id del usuario
    $clientStmt = $pdo->prepare("SELECT id FROM clientes_perfiles WHERE usuario_id = :usuario_id");
    $clientStmt->execute([':usuario_id' => $_SESSION['user_id']]);
    $client = $clientStmt->fetch();
    
    if (!$client) {
        http_response_code(400);
        echo json_encode(['error' => 'Usuario no es cliente']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO proyectos (cliente_id, categoria_id, titulo, descripcion, presupuesto_min, presupuesto_max, estado, requisitos)
        VALUES (:cliente_id, :categoria_id, :titulo, :descripcion, :presupuesto_min, :presupuesto_max, :estado, :requisitos)
    ");
    
    $stmt->execute([
        ':cliente_id' => $client['id'],
        ':categoria_id' => $data['categoria_id'] ?? null,
        ':titulo' => $data['titulo'] ?? '',
        ':descripcion' => $data['descripcion'] ?? '',
        ':presupuesto_min' => $data['presupuesto_min'] ?? 0,
        ':presupuesto_max' => $data['presupuesto_max'] ?? 0,
        ':estado' => $data['estado'] ?? 'borrador',
        ':requisitos' => $data['requisitos'] ?? ''
    ]);
    
    echo json_encode([
        'success' => true,
        'project_id' => $pdo->lastInsertId(),
        'message' => 'Proyecto creado exitosamente'
    ]);
}

function updateProject($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        return;
    }
    
    $updateFields = [];
    $params = [':id' => $id];
    
    if (isset($data['titulo'])) {
        $updateFields[] = 'titulo = :titulo';
        $params[':titulo'] = $data['titulo'];
    }
    if (isset($data['descripcion'])) {
        $updateFields[] = 'descripcion = :descripcion';
        $params[':descripcion'] = $data['descripcion'];
    }
    if (isset($data['estado'])) {
        $updateFields[] = 'estado = :estado';
        $params[':estado'] = $data['estado'];
    }
    if (isset($data['presupuesto_min'])) {
        $updateFields[] = 'presupuesto_min = :presupuesto_min';
        $params[':presupuesto_min'] = $data['presupuesto_min'];
    }
    if (isset($data['presupuesto_max'])) {
        $updateFields[] = 'presupuesto_max = :presupuesto_max';
        $params[':presupuesto_max'] = $data['presupuesto_max'];
    }
    
    if (empty($updateFields)) {
        echo json_encode(['success' => true, 'message' => 'Sin cambios']);
        return;
    }
    
    $sql = "UPDATE proyectos SET " . implode(', ', $updateFields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'message' => 'Proyecto actualizado']);
}

function deleteProject($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    echo json_encode(['success' => true, 'message' => 'Proyecto eliminado']);
}

function searchProjects($pdo) {
    $search = $_GET['q'] ?? '';
    $categoria = $_GET['categoria'] ?? '';
    $estado = $_GET['estado'] ?? '';
    $presupuestoMin = $_GET['presupuesto_min'] ?? '';
    $presupuestoMax = $_GET['presupuesto_max'] ?? '';
    
    $sql = "SELECT p.*, c.nombre as categoria FROM proyectos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (p.titulo LIKE :search OR p.descripcion LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($categoria)) {
        $sql .= " AND p.categoria_id = :categoria";
        $params[':categoria'] = $categoria;
    }
    
    if (!empty($estado)) {
        $sql .= " AND p.estado = :estado";
        $params[':estado'] = $estado;
    }
    
    if (!empty($presupuestoMin)) {
        $sql .= " AND p.presupuesto_max >= :presupuesto_min";
        $params[':presupuesto_min'] = $presupuestoMin;
    }
    
    if (!empty($presupuestoMax)) {
        $sql .= " AND p.presupuesto_min <= :presupuesto_max";
        $params[':presupuesto_max'] = $presupuestoMax;
    }
    
    $sql .= " ORDER BY p.creado_en DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'results' => $results]);
}

function getProjectsByClient($pdo) {
    $clientId = $_GET['client_id'] ?? '';
    
    if (empty($clientId)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de cliente requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM proyectos
        WHERE cliente_id = :cliente_id
        ORDER BY creado_en DESC
    ");
    $stmt->execute([':cliente_id' => $clientId]);
    $projects = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'projects' => $projects]);
}

function getActiveProjects($pdo) {
    $stmt = $pdo->query("
        SELECT p.*, c.nombre as categoria, u.nombre, u.apellido
        FROM proyectos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN clientes_perfiles cp ON p.cliente_id = cp.id
        LEFT JOIN usuarios u ON cp.usuario_id = u.id
        WHERE p.estado IN ('abierto', 'en_progreso')
        ORDER BY p.creado_en DESC
        LIMIT 20
    ");
    
    $projects = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'projects' => $projects]);
}
?>
