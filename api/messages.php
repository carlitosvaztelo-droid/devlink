<?php
// api/messages.php
// Endpoints para mensajes

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'send':
            sendMessage($pdo);
            break;
            
        case 'get-conversation':
            getConversation($pdo);
            break;
            
        case 'get-conversations':
            getConversations($pdo);
            break;
            
        case 'mark-as-read':
            markAsRead($pdo);
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

function sendMessage($pdo) {
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
        INSERT INTO mensajes (remitente_id, destinatario_id, contenido, proyecto_id)
        VALUES (:remitente_id, :destinatario_id, :contenido, :proyecto_id)
    ");
    
    $stmt->execute([
        ':remitente_id' => $_SESSION['user_id'],
        ':destinatario_id' => $data['destinatario_id'],
        ':contenido' => $data['contenido'] ?? '',
        ':proyecto_id' => $data['proyecto_id'] ?? null
    ]);
    
    echo json_encode([
        'success' => true,
        'message_id' => $pdo->lastInsertId(),
        'message' => 'Mensaje enviado'
    ]);
}

function getConversation($pdo) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        return;
    }
    
    $otherUserId = $_GET['user_id'] ?? '';
    
    if (empty($otherUserId)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT m.*, 
               u_rem.nombre as remitente_nombre, u_rem.apellido as remitente_apellido,
               u_des.nombre as destinatario_nombre, u_des.apellido as destinatario_apellido
        FROM mensajes m
        LEFT JOIN usuarios u_rem ON m.remitente_id = u_rem.id
        LEFT JOIN usuarios u_des ON m.destinatario_id = u_des.id
        WHERE (m.remitente_id = :usuario_id AND m.destinatario_id = :otro_usuario_id)
           OR (m.remitente_id = :otro_usuario_id AND m.destinatario_id = :usuario_id)
        ORDER BY m.creado_en ASC
        LIMIT 50
    ");
    
    $stmt->execute([
        ':usuario_id' => $_SESSION['user_id'],
        ':otro_usuario_id' => $otherUserId
    ]);
    
    $messages = $stmt->fetchAll();
    
    // Marcar como leídos
    $updateStmt = $pdo->prepare("
        UPDATE mensajes
        SET leido = 1
        WHERE destinatario_id = :usuario_id AND remitente_id = :otro_usuario_id AND leido = 0
    ");
    $updateStmt->execute([
        ':usuario_id' => $_SESSION['user_id'],
        ':otro_usuario_id' => $otherUserId
    ]);
    
    echo json_encode(['success' => true, 'messages' => $messages]);
}

function getConversations($pdo) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        return;
    }
    
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN remitente_id = {$_SESSION['user_id']} THEN destinatario_id
                ELSE remitente_id
            END as otro_usuario_id,
            MAX(creado_en) as ultimo_mensaje,
            SUM(CASE WHEN leido = 0 AND destinatario_id = {$_SESSION['user_id']} THEN 1 ELSE 0 END) as no_leidos,
            u.nombre, u.apellido, u.email
        FROM mensajes m
        LEFT JOIN usuarios u ON CASE 
            WHEN remitente_id = {$_SESSION['user_id']} THEN u.id = destinatario_id
            ELSE u.id = remitente_id
        END
        WHERE remitente_id = {$_SESSION['user_id']} OR destinatario_id = {$_SESSION['user_id']}
        GROUP BY otro_usuario_id, u.id, u.nombre, u.apellido, u.email
        ORDER BY ultimo_mensaje DESC
    ");
    
    $conversations = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'conversations' => $conversations]);
}

function markAsRead($pdo) {
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
    $messageId = $data['message_id'] ?? '';
    
    if (empty($messageId)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de mensaje requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        UPDATE mensajes
        SET leido = 1
        WHERE id = :id AND destinatario_id = :usuario_id
    ");
    $stmt->execute([':id' => $messageId, ':usuario_id' => $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Mensaje marcado como leído']);
}
?>
