<?php
// api/proposals.php
// Endpoints para propuestas

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listProposals($pdo);
            break;
            
        case 'create':
            createProposal($pdo);
            break;
            
        case 'get-by-project':
            getProposalsByProject($pdo);
            break;
            
        case 'get-by-developer':
            getProposalsByDeveloper($pdo);
            break;
            
        case 'update-status':
            updateProposalStatus($pdo);
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

function listProposals($pdo) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    
    $stmt = $pdo->prepare("
        SELECT p.*, pr.titulo as proyecto_titulo, u.nombre, u.apellido, u.email
        FROM propuestas p
        LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
        LEFT JOIN desarrolladores_perfiles dp ON p.desarrollador_id = dp.id
        LEFT JOIN usuarios u ON dp.usuario_id = u.id
        ORDER BY p.creado_en DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $proposals = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'proposals' => $proposals,
        'page' => $page,
        'limit' => $limit
    ]);
}

function createProposal($pdo) {
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
    
    // Obtener perfil del desarrollador
    $devStmt = $pdo->prepare("SELECT id FROM desarrolladores_perfiles WHERE usuario_id = :usuario_id");
    $devStmt->execute([':usuario_id' => $_SESSION['user_id']]);
    $dev = $devStmt->fetch();
    
    if (!$dev) {
        http_response_code(400);
        echo json_encode(['error' => 'Usuario no es desarrollador']);
        return;
    }
    
    // Verificar que no haya propuesta previa
    $checkStmt = $pdo->prepare("
        SELECT id FROM propuestas
        WHERE proyecto_id = :proyecto_id AND desarrollador_id = :desarrollador_id
    ");
    $checkStmt->execute([
        ':proyecto_id' => $data['proyecto_id'],
        ':desarrollador_id' => $dev['id']
    ]);
    
    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Ya has enviado una propuesta para este proyecto']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO propuestas (proyecto_id, desarrollador_id, precio_propuesto, tiempo_estimado, descripcion, estado)
        VALUES (:proyecto_id, :desarrollador_id, :precio_propuesto, :tiempo_estimado, :descripcion, 'pendiente')
    ");
    
    $stmt->execute([
        ':proyecto_id' => $data['proyecto_id'],
        ':desarrollador_id' => $dev['id'],
        ':precio_propuesto' => $data['precio_propuesto'] ?? 0,
        ':tiempo_estimado' => $data['tiempo_estimado'] ?? '',
        ':descripcion' => $data['descripcion'] ?? ''
    ]);
    
    echo json_encode([
        'success' => true,
        'proposal_id' => $pdo->lastInsertId(),
        'message' => 'Propuesta enviada exitosamente'
    ]);
}

function getProposalsByProject($pdo) {
    $projectId = $_GET['project_id'] ?? '';
    
    if (empty($projectId)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de proyecto requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.nombre, u.apellido, u.email, dp.tarifa_hora, dp.calificacion_promedio
        FROM propuestas p
        LEFT JOIN desarrolladores_perfiles dp ON p.desarrollador_id = dp.id
        LEFT JOIN usuarios u ON dp.usuario_id = u.id
        WHERE p.proyecto_id = :proyecto_id
        ORDER BY p.creado_en DESC
    ");
    $stmt->execute([':proyecto_id' => $projectId]);
    $proposals = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'proposals' => $proposals]);
}

function getProposalsByDeveloper($pdo) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        return;
    }
    
    $devStmt = $pdo->prepare("SELECT id FROM desarrolladores_perfiles WHERE usuario_id = :usuario_id");
    $devStmt->execute([':usuario_id' => $_SESSION['user_id']]);
    $dev = $devStmt->fetch();
    
    if (!$dev) {
        http_response_code(400);
        echo json_encode(['error' => 'Usuario no es desarrollador']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT p.*, pr.titulo, pr.descripcion, cp.nombre_empresa, u.nombre, u.apellido
        FROM propuestas p
        LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
        LEFT JOIN clientes_perfiles cp ON pr.cliente_id = cp.id
        LEFT JOIN usuarios u ON cp.usuario_id = u.id
        WHERE p.desarrollador_id = :desarrollador_id
        ORDER BY p.creado_en DESC
    ");
    $stmt->execute([':desarrollador_id' => $dev['id']]);
    $proposals = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'proposals' => $proposals]);
}

function updateProposalStatus($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    $status = $data['status'] ?? '';
    
    if (empty($id) || empty($status)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID y estado requeridos']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE propuestas SET estado = :estado WHERE id = :id");
    $stmt->execute([':estado' => $status, ':id' => $id]);
    
    echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
}
?>
