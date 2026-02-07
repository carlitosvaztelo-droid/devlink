<?php
// api/developers.php
// Endpoints para desarrolladores

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listDevelopers($pdo);
            break;
            
        case 'get':
            getDeveloper($pdo);
            break;
            
        case 'get-profile':
            getDeveloperProfile($pdo);
            break;
            
        case 'get-top-rated':
            getTopRatedDevelopers($pdo);
            break;
            
        case 'update-profile':
            updateDeveloperProfile($pdo);
            break;
            
        case 'add-skill':
            addSkill($pdo);
            break;
            
        case 'remove-skill':
            removeSkill($pdo);
            break;
            
        case 'search':
            searchDevelopers($pdo);
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

function listDevelopers($pdo) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, u.apellido, u.email, dp.tarifa_hora, dp.calificacion_promedio, dp.proyectos_completados, dp.disponible,
               GROUP_CONCAT(h.nombre SEPARATOR ', ') as habilidades
        FROM usuarios u
        LEFT JOIN desarrolladores_perfiles dp ON u.id = dp.usuario_id
        LEFT JOIN desarrollador_habilidades dh ON dp.id = dh.desarrollador_id
        LEFT JOIN habilidades h ON dh.habilidad_id = h.id
        WHERE u.tipo = 'desarrollador'
        GROUP BY u.id
        ORDER BY dp.calificacion_promedio DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $developers = $stmt->fetchAll();
    
    // Contar total
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'desarrollador'");
    $countResult = $countStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'developers' => $developers,
        'total' => $countResult['total'],
        'page' => $page,
        'limit' => $limit
    ]);
}

function getDeveloper($pdo) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, u.apellido, u.email, u.telefono, u.imagen_perfil,
               dp.tarifa_hora, dp.descripcion, dp.experiencia_años, dp.calificacion_promedio, 
               dp.proyectos_completados, dp.tasa_exito, dp.disponible
        FROM usuarios u
        LEFT JOIN desarrolladores_perfiles dp ON u.id = dp.usuario_id
        WHERE u.id = :id AND u.tipo = 'desarrollador'
    ");
    $stmt->execute([':id' => $id]);
    $developer = $stmt->fetch();
    
    if (!$developer) {
        http_response_code(404);
        echo json_encode(['error' => 'Desarrollador no encontrado']);
        return;
    }
    
    // Obtener habilidades
    $skillsStmt = $pdo->prepare("
        SELECT h.id, h.nombre
        FROM desarrollador_habilidades dh
        LEFT JOIN habilidades h ON dh.habilidad_id = h.id
        WHERE dh.desarrollador_id = (SELECT id FROM desarrolladores_perfiles WHERE usuario_id = :usuario_id)
    ");
    $skillsStmt->execute([':usuario_id' => $id]);
    $developer['skills'] = $skillsStmt->fetchAll();
    
    echo json_encode(['success' => true, 'developer' => $developer]);
}

function getDeveloperProfile($pdo) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT dp.*, u.nombre, u.apellido, u.email, u.telefono, u.imagen_perfil
        FROM desarrolladores_perfiles dp
        LEFT JOIN usuarios u ON dp.usuario_id = u.id
        WHERE dp.usuario_id = :usuario_id
    ");
    $stmt->execute([':usuario_id' => $_SESSION['user_id']]);
    $profile = $stmt->fetch();
    
    if (!$profile) {
        http_response_code(404);
        echo json_encode(['error' => 'Perfil no encontrado']);
        return;
    }
    
    // Obtener habilidades
    $skillsStmt = $pdo->prepare("
        SELECT h.id, h.nombre
        FROM desarrollador_habilidades dh
        LEFT JOIN habilidades h ON dh.habilidad_id = h.id
        WHERE dh.desarrollador_id = :dev_id
    ");
    $skillsStmt->execute([':dev_id' => $profile['id']]);
    $profile['skills'] = $skillsStmt->fetchAll();
    
    echo json_encode(['success' => true, 'profile' => $profile]);
}

function getTopRatedDevelopers($pdo) {
    $limit = $_GET['limit'] ?? 10;
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, u.apellido, u.email, dp.tarifa_hora, dp.calificacion_promedio, dp.proyectos_completados,
               GROUP_CONCAT(h.nombre SEPARATOR ', ') as habilidades
        FROM usuarios u
        LEFT JOIN desarrolladores_perfiles dp ON u.id = dp.usuario_id
        LEFT JOIN desarrollador_habilidades dh ON dp.id = dh.desarrollador_id
        LEFT JOIN habilidades h ON dh.habilidad_id = h.id
        WHERE u.tipo = 'desarrollador' AND dp.calificacion_promedio >= 4.0
        GROUP BY u.id
        ORDER BY dp.calificacion_promedio DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $developers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'developers' => $developers]);
}

function updateDeveloperProfile($pdo) {
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
    
    $stmt = $pdo->prepare("
        UPDATE desarrolladores_perfiles
        SET descripcion = :descripcion, tarifa_hora = :tarifa_hora, experiencia_años = :experiencia
        WHERE usuario_id = :usuario_id
    ");
    
    $stmt->execute([
        ':descripcion' => $data['descripcion'] ?? '',
        ':tarifa_hora' => $data['tarifa_hora'] ?? 0,
        ':experiencia' => $data['experiencia_años'] ?? 0,
        ':usuario_id' => $_SESSION['user_id']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Perfil actualizado']);
}

function addSkill($pdo) {
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
    $skillId = $data['skill_id'] ?? '';
    
    if (empty($skillId)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de habilidad requerido']);
        return;
    }
    
    // Obtener perfil del desarrollador
    $devStmt = $pdo->prepare("SELECT id FROM desarrolladores_perfiles WHERE usuario_id = :usuario_id");
    $devStmt->execute([':usuario_id' => $_SESSION['user_id']]);
    $dev = $devStmt->fetch();
    
    if (!$dev) {
        http_response_code(400);
        echo json_encode(['error' => 'Usuario no es desarrollador']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO desarrollador_habilidades (desarrollador_id, habilidad_id)
        VALUES (:dev_id, :skill_id)
    ");
    
    $stmt->execute([':dev_id' => $dev['id'], ':skill_id' => $skillId]);
    
    echo json_encode(['success' => true, 'message' => 'Habilidad añadida']);
}

function removeSkill($pdo) {
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
    $skillId = $data['skill_id'] ?? '';
    
    // Obtener perfil del desarrollador
    $devStmt = $pdo->prepare("SELECT id FROM desarrolladores_perfiles WHERE usuario_id = :usuario_id");
    $devStmt->execute([':usuario_id' => $_SESSION['user_id']]);
    $dev = $devStmt->fetch();
    
    $stmt = $pdo->prepare("
        DELETE FROM desarrollador_habilidades
        WHERE desarrollador_id = :dev_id AND habilidad_id = :skill_id
    ");
    
    $stmt->execute([':dev_id' => $dev['id'], ':skill_id' => $skillId]);
    
    echo json_encode(['success' => true, 'message' => 'Habilidad eliminada']);
}

function searchDevelopers($pdo) {
    $search = $_GET['q'] ?? '';
    $skill = $_GET['skill'] ?? '';
    $minRating = $_GET['min_rating'] ?? '';
    $maxPrice = $_GET['max_price'] ?? '';
    
    $sql = "
        SELECT u.id, u.nombre, u.apellido, u.email, dp.tarifa_hora, dp.calificacion_promedio, dp.proyectos_completados,
               GROUP_CONCAT(h.nombre SEPARATOR ', ') as habilidades
        FROM usuarios u
        LEFT JOIN desarrolladores_perfiles dp ON u.id = dp.usuario_id
        LEFT JOIN desarrollador_habilidades dh ON dp.id = dh.desarrollador_id
        LEFT JOIN habilidades h ON dh.habilidad_id = h.id
        WHERE u.tipo = 'desarrollador'
    ";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (u.nombre LIKE :search OR u.apellido LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($skill)) {
        $sql .= " AND h.nombre = :skill";
        $params[':skill'] = $skill;
    }
    
    if (!empty($minRating)) {
        $sql .= " AND dp.calificacion_promedio >= :min_rating";
        $params[':min_rating'] = $minRating;
    }
    
    if (!empty($maxPrice)) {
        $sql .= " AND dp.tarifa_hora <= :max_price";
        $params[':max_price'] = $maxPrice;
    }
    
    $sql .= " GROUP BY u.id ORDER BY dp.calificacion_promedio DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'results' => $results]);
}
?>
