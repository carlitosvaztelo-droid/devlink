<?php
// api/contracts.php
// Endpoints para contratos

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listContracts($pdo);
            break;
            
        case 'create':
            createContract($pdo);
            break;
            
        case 'get':
            getContract($pdo);
            break;
            
        case 'update-status':
            updateContractStatus($pdo);
            break;
            
        case 'get-by-user':
            getContractsByUser($pdo);
            break;
            
        case 'get-by-project':
            getContractsByProject($pdo);
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

function listContracts($pdo) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    
    $stmt = $pdo->prepare("
        SELECT c.*, pr.titulo as proyecto_titulo, 
               u_dev.nombre as dev_nombre, u_dev.apellido as dev_apellido,
               u_cli.nombre as cliente_nombre, u_cli.apellido as cliente_apellido
        FROM contratos c
        LEFT JOIN proyectos pr ON c.proyecto_id = pr.id
        LEFT JOIN desarrolladores_perfiles dp ON c.desarrollador_id = dp.id
        LEFT JOIN usuarios u_dev ON dp.usuario_id = u_dev.id
        LEFT JOIN clientes_perfiles cp ON c.cliente_id = cp.id
        LEFT JOIN usuarios u_cli ON cp.usuario_id = u_cli.id
        ORDER BY c.creado_en DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $contracts = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'contracts' => $contracts,
        'page' => $page,
        'limit' => $limit
    ]);
}

function createContract($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("
        INSERT INTO contratos (proyecto_id, cliente_id, desarrollador_id, monto_total, estado, fecha_inicio)
        VALUES (:proyecto_id, :cliente_id, :desarrollador_id, :monto_total, 'activo', NOW())
    ");
    
    $stmt->execute([
        ':proyecto_id' => $data['proyecto_id'],
        ':cliente_id' => $data['cliente_id'],
        ':desarrollador_id' => $data['desarrollador_id'],
        ':monto_total' => $data['monto_total'] ?? 0
    ]);
    
    echo json_encode([
        'success' => true,
        'contract_id' => $pdo->lastInsertId(),
        'message' => 'Contrato creado exitosamente'
    ]);
}

function getContract($pdo) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT c.*, pr.titulo as proyecto_titulo, pr.descripcion as proyecto_descripcion,
               u_dev.nombre as dev_nombre, u_dev.apellido as dev_apellido, u_dev.email as dev_email,
               u_cli.nombre as cliente_nombre, u_cli.apellido as cliente_apellido
        FROM contratos c
        LEFT JOIN proyectos pr ON c.proyecto_id = pr.id
        LEFT JOIN desarrolladores_perfiles dp ON c.desarrollador_id = dp.id
        LEFT JOIN usuarios u_dev ON dp.usuario_id = u_dev.id
        LEFT JOIN clientes_perfiles cp ON c.cliente_id = cp.id
        LEFT JOIN usuarios u_cli ON cp.usuario_id = u_cli.id
        WHERE c.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $contract = $stmt->fetch();
    
    if (!$contract) {
        http_response_code(404);
        echo json_encode(['error' => 'Contrato no encontrado']);
        return;
    }
    
    echo json_encode(['success' => true, 'contract' => $contract]);
}

function updateContractStatus($pdo) {
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
    
    $updateData = [':id' => $id, ':estado' => $status];
    $sql = "UPDATE contratos SET estado = :estado";
    
    if ($status === 'completado') {
        $sql .= ", fecha_finalizacion = NOW()";
    }
    
    $sql .= " WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($updateData);
    
    echo json_encode(['success' => true, 'message' => 'Contrato actualizado']);
}

function getContractsByUser($pdo) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        return;
    }
    
    $userType = $_GET['type'] ?? '';
    
    if ($userType === 'desarrollador') {
        $stmt = $pdo->prepare("
            SELECT c.*, pr.titulo as proyecto_titulo, u_cli.nombre as cliente_nombre, u_cli.apellido as cliente_apellido
            FROM contratos c
            LEFT JOIN proyectos pr ON c.proyecto_id = pr.id
            LEFT JOIN clientes_perfiles cp ON c.cliente_id = cp.id
            LEFT JOIN usuarios u_cli ON cp.usuario_id = u_cli.id
            LEFT JOIN desarrolladores_perfiles dp ON c.desarrollador_id = dp.id
            WHERE dp.usuario_id = :usuario_id
            ORDER BY c.creado_en DESC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT c.*, pr.titulo as proyecto_titulo, u_dev.nombre as dev_nombre, u_dev.apellido as dev_apellido
            FROM contratos c
            LEFT JOIN proyectos pr ON c.proyecto_id = pr.id
            LEFT JOIN clientes_perfiles cp ON c.cliente_id = cp.id
            LEFT JOIN usuarios u_cli ON cp.usuario_id = u_cli.id
            LEFT JOIN desarrolladores_perfiles dp ON c.desarrollador_id = dp.id
            LEFT JOIN usuarios u_dev ON dp.usuario_id = u_dev.id
            WHERE u_cli.id = :usuario_id
            ORDER BY c.creado_en DESC
        ");
    }
    
    $stmt->execute([':usuario_id' => $_SESSION['user_id']]);
    $contracts = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'contracts' => $contracts]);
}

function getContractsByProject($pdo) {
    $projectId = $_GET['project_id'] ?? '';
    
    if (empty($projectId)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de proyecto requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT c.*, u_dev.nombre as dev_nombre, u_dev.apellido as dev_apellido, u_cli.nombre as cliente_nombre
        FROM contratos c
        LEFT JOIN desarrolladores_perfiles dp ON c.desarrollador_id = dp.id
        LEFT JOIN usuarios u_dev ON dp.usuario_id = u_dev.id
        LEFT JOIN clientes_perfiles cp ON c.cliente_id = cp.id
        LEFT JOIN usuarios u_cli ON cp.usuario_id = u_cli.id
        WHERE c.proyecto_id = :proyecto_id
    ");
    $stmt->execute([':proyecto_id' => $projectId]);
    $contracts = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'contracts' => $contracts]);
}
?>
