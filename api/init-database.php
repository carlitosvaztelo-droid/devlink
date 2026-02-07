<?php
// api/init-database.php
// Script para inicializar la base de datos con datos de ejemplo

header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../config/database.php';
    
    // Determinar la ruta correcta del archivo SQL
    $sqlFilePath = __DIR__ . '/../devlink.sql';
    
    if (!file_exists($sqlFilePath)) {
        throw new Exception("Archivo SQL no encontrado en: $sqlFilePath");
    }
    
    // Leer el archivo SQL
    $sqlFile = file_get_contents($sqlFilePath);
    
    // Dividir por puntos y comas para ejecutar cada sentencia
    $statements = array_filter(array_map('trim', explode(';', $sqlFile)));
    
    $count = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $count++;
            } catch (PDOException $e) {
                // Ignorar errores de tablas/vistas que ya existen
                $errorMsg = $e->getMessage();
                
                // Saltar si la tabla ya existe
                if (stripos($errorMsg, 'already exists') !== false || 
                    stripos($errorMsg, 'Duplicate') !== false) {
                    continue;
                }
                
                // Para campos sin valor por defecto, intentar insertar con slug
                if (stripos($errorMsg, "Field 'slug' doesn't have a default value") !== false) {
                    error_log('Nota: Problema con slug, continuando...');
                    continue;
                }
                
                $errors[] = $errorMsg;
                error_log('SQL Error: ' . $errorMsg);
            }
        }
    }
    
    // Insertar datos de ejemplo
    try {
        insertSampleData($pdo);
        $sampleDataInserted = true;
    } catch (PDOException $e) {
        $sampleDataInserted = false;
        error_log('Error insertando datos de ejemplo: ' . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Base de datos inicializada exitosamente',
        'statements_executed' => $count,
        'sample_data_inserted' => $sampleDataInserted,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function insertSampleData($pdo) {
    // Verificar si ya hay datos
    $check = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
    $result = $check->fetch();
    
    if ($result['count'] > 0) {
        return; // Datos ya existen
    }
    
    // Insertar usuarios clientes
    $clients = [
        ['nombre' => 'Carlos', 'apellido' => 'Martínez', 'email' => 'carlos@example.com', 'cedula' => 'V-12345678'],
        ['nombre' => 'María', 'apellido' => 'García', 'email' => 'maria@example.com', 'cedula' => 'V-23456789'],
        ['nombre' => 'Juan', 'apellido' => 'López', 'email' => 'juan@example.com', 'cedula' => 'V-34567890'],
    ];
    
    $clientIds = [];
    foreach ($clients as $client) {
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (tipo, email, contraseña, cedula, telefono, nombre, apellido)
            VALUES ('cliente', :email, :password, :cedula, :telefono, :nombre, :apellido)
        ");
        $stmt->execute([
            ':email' => $client['email'],
            ':password' => $password,
            ':cedula' => $client['cedula'],
            ':telefono' => '+58412' . rand(1000000, 9999999),
            ':nombre' => $client['nombre'],
            ':apellido' => $client['apellido']
        ]);
        $clientIds[] = $pdo->lastInsertId();
    }
    
    // Insertar desarrolladores
    $developers = [
        ['nombre' => 'Pedro', 'apellido' => 'Sánchez', 'email' => 'pedro@example.com', 'cedula' => 'V-45678901'],
        ['nombre' => 'Ana', 'apellido' => 'Rodríguez', 'email' => 'ana@example.com', 'cedula' => 'V-56789012'],
        ['nombre' => 'Luis', 'apellido' => 'Hernández', 'email' => 'luis@example.com', 'cedula' => 'V-67890123'],
    ];
    
    $devIds = [];
    foreach ($developers as $dev) {
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (tipo, email, contraseña, cedula, telefono, nombre, apellido)
            VALUES ('desarrollador', :email, :password, :cedula, :telefono, :nombre, :apellido)
        ");
        $stmt->execute([
            ':email' => $dev['email'],
            ':password' => $password,
            ':cedula' => $dev['cedula'],
            ':telefono' => '+58412' . rand(1000000, 9999999),
            ':nombre' => $dev['nombre'],
            ':apellido' => $dev['apellido']
        ]);
        $devIds[] = $pdo->lastInsertId();
    }
    
    // Insertar categorías
    $categories = ['Web Development', 'Mobile App', 'Backend', 'UI/UX Design', 'DevOps'];
    $categoryIds = [];
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->execute([':nombre' => $cat, ':descripcion' => 'Categoría: ' . $cat]);
        $categoryIds[] = $pdo->lastInsertId();
    }
    
    // Insertar habilidades
    $skills = ['PHP', 'JavaScript', 'Python', 'React', 'Vue.js', 'Node.js', 'MySQL', 'MongoDB', 'UI Design', 'UX Research'];
    $skillIds = [];
    foreach ($skills as $skill) {
        $stmt = $pdo->prepare("INSERT INTO habilidades (nombre) VALUES (:nombre)");
        $stmt->execute([':nombre' => $skill]);
        $skillIds[] = $pdo->lastInsertId();
    }
    
    // Insertar perfiles de desarrolladores
    foreach ($devIds as $devId) {
        $stmt = $pdo->prepare("
            INSERT INTO desarrolladores_perfiles (usuario_id, tarifa_hora, descripcion, experiencia_años, calificacion_promedio, proyectos_completados, tasa_exito, disponible)
            VALUES (:usuario_id, :tarifa_hora, :descripcion, :experiencia, :calificacion, :proyectos, :tasa_exito, 1)
        ");
        $stmt->execute([
            ':usuario_id' => $devId,
            ':tarifa_hora' => rand(20, 100),
            ':descripcion' => 'Desarrollador con experiencia en proyectos web',
            ':experiencia' => rand(1, 10),
            ':calificacion' => rand(35, 50) / 10, // 3.5 a 5.0
            ':proyectos' => rand(5, 30),
            ':tasa_exito' => rand(80, 100)
        ]);
        
        $devProfileId = $pdo->lastInsertId();
        
        // Asignar habilidades aleatorias
        $randomSkills = array_rand($skillIds, rand(2, 4));
        foreach ((array)$randomSkills as $skillIndex) {
            $stmt = $pdo->prepare("
                INSERT INTO desarrollador_habilidades (desarrollador_id, habilidad_id)
                VALUES (:dev_id, :skill_id)
            ");
            $stmt->execute([
                ':dev_id' => $devProfileId,
                ':skill_id' => $skillIds[$skillIndex]
            ]);
        }
    }
    
    // Insertar perfiles de clientes
    foreach ($clientIds as $clientId) {
        $stmt = $pdo->prepare("
            INSERT INTO clientes_perfiles (usuario_id, nombre_empresa, descripcion, sitio_web)
            VALUES (:usuario_id, :nombre_empresa, :descripcion, :sitio_web)
        ");
        $stmt->execute([
            ':usuario_id' => $clientId,
            ':nombre_empresa' => 'Empresa ' . rand(100, 999),
            ':descripcion' => 'Empresa dedicada a servicios digitales',
            ':sitio_web' => 'https://empresa' . rand(100, 999) . '.com'
        ]);
    }
    
    // Insertar proyectos
    $projects = [
        ['titulo' => 'Sistema de Gestión de Inventario', 'presupuesto_min' => 500, 'presupuesto_max' => 1500],
        ['titulo' => 'App Móvil de E-commerce', 'presupuesto_min' => 2000, 'presupuesto_max' => 5000],
        ['titulo' => 'Rediseño de Sitio Web', 'presupuesto_min' => 300, 'presupuesto_max' => 800],
        ['titulo' => 'API REST para Plataforma', 'presupuesto_min' => 1000, 'presupuesto_max' => 3000],
        ['titulo' => 'Aplicación de Streaming', 'presupuesto_min' => 5000, 'presupuesto_max' => 10000],
    ];
    
    $states = ['abierto', 'en_progreso', 'completado'];
    
    foreach ($projects as $index => $project) {
        $stmt = $pdo->prepare("
            INSERT INTO proyectos (cliente_id, categoria_id, titulo, descripcion, presupuesto_min, presupuesto_max, estado, vistas)
            VALUES (:cliente_id, :categoria_id, :titulo, :descripcion, :presupuesto_min, :presupuesto_max, :estado, :vistas)
        ");
        $stmt->execute([
            ':cliente_id' => $clientIds[$index % count($clientIds)],
            ':categoria_id' => $categoryIds[$index % count($categoryIds)],
            ':titulo' => $project['titulo'],
            ':descripcion' => 'Descripción detallada del proyecto: ' . $project['titulo'],
            ':presupuesto_min' => $project['presupuesto_min'],
            ':presupuesto_max' => $project['presupuesto_max'],
            ':estado' => $states[$index % count($states)],
            ':vistas' => rand(10, 100)
        ]);
    }
}
?>
