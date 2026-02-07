<?php
// api/auth.php
// Endpoints para autenticación

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            handleLogin($pdo);
            break;
            
        case 'register':
            handleRegister($pdo);
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        case 'get-current-user':
            getCurrentUser();
            break;
            
        case 'update-profile':
            updateProfile($pdo);
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

function handleLogin($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $userType = $data['type'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email y contraseña requeridos']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT id, tipo, nombre, apellido, email, imagen_perfil, activo
        FROM usuarios
        WHERE email = :email AND tipo = :tipo AND activo = 1
    ");
    $stmt->execute([':email' => $email, ':tipo' => $userType]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['contraseña'] ?? '')) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales inválidas']);
        return;
    }
    
    // Actualizar último login
    $updateStmt = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id");
    $updateStmt->execute([':id' => $user['id']]);
    
    // Crear sesión
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['tipo'];
    $_SESSION['user_email'] = $user['email'];
    
    unset($user['contraseña']);
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'message' => 'Login exitoso'
    ]);
}

function handleRegister($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $nombre = $data['nombre'] ?? '';
    $apellido = $data['apellido'] ?? '';
    $cedula = $data['cedula'] ?? '';
    $telefono = $data['telefono'] ?? '';
    $userType = $data['type'] ?? 'desarrollador';
    
    // Validaciones
    if (empty($email) || empty($password) || empty($nombre) || empty($apellido)) {
        http_response_code(400);
        echo json_encode(['error' => 'Campos requeridos faltantes']);
        return;
    }
    
    // Verificar si email ya existe
    $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $checkStmt->execute([':email' => $email]);
    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'El email ya está registrado']);
        return;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (tipo, email, contraseña, cedula, telefono, nombre, apellido)
        VALUES (:tipo, :email, :password, :cedula, :telefono, :nombre, :apellido)
    ");
    
    $stmt->execute([
        ':tipo' => $userType,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':cedula' => $cedula,
        ':telefono' => $telefono,
        ':nombre' => $nombre,
        ':apellido' => $apellido
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Si es desarrollador, crear perfil
    if ($userType === 'desarrollador') {
        $profileStmt = $pdo->prepare("
            INSERT INTO desarrolladores_perfiles (usuario_id, tarifa_hora, descripcion, experiencia_años, disponible)
            VALUES (:usuario_id, :tarifa_hora, :descripcion, :experiencia, 1)
        ");
        $profileStmt->execute([
            ':usuario_id' => $userId,
            ':tarifa_hora' => 50,
            ':descripcion' => 'Perfil nuevo',
            ':experiencia' => 0
        ]);
    } else {
        // Si es cliente, crear perfil
        $profileStmt = $pdo->prepare("
            INSERT INTO clientes_perfiles (usuario_id, nombre_empresa, descripcion)
            VALUES (:usuario_id, :nombre_empresa, :descripcion)
        ");
        $profileStmt->execute([
            ':usuario_id' => $userId,
            ':nombre_empresa' => $nombre . ' ' . $apellido,
            ':descripcion' => 'Perfil nuevo'
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'user_id' => $userId,
        'message' => 'Registro exitoso'
    ]);
}

function handleLogout() {
    session_start();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout exitoso']);
}

function getCurrentUser() {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        return;
    }
    
    require_once '../config/database.php';
    
    $stmt = $pdo->prepare("
        SELECT id, tipo, nombre, apellido, email, imagen_perfil
        FROM usuarios
        WHERE id = :id
    ");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
}

function updateProfile($pdo) {
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
    $userId = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET nombre = :nombre, apellido = :apellido, telefono = :telefono
        WHERE id = :id
    ");
    
    $stmt->execute([
        ':nombre' => $data['nombre'] ?? '',
        ':apellido' => $data['apellido'] ?? '',
        ':telefono' => $data['telefono'] ?? '',
        ':id' => $userId
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Perfil actualizado']);
}
?>
