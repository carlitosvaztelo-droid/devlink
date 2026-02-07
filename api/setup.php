<?php
// api/setup.php
// Script simple para crear base de datos y tablas

header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../config/database.php';
    
    // Crear base de datos si no existe
    $dbName = 'devlink';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbName");
    $pdo->exec("USE $dbName");
    
    // Crear tablas de forma simple
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tipo ENUM('cliente', 'desarrollador') NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            contraseña VARCHAR(255) NOT NULL,
            cedula VARCHAR(50),
            telefono VARCHAR(20),
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categorias (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL UNIQUE,
            descripcion TEXT,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS habilidades (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL UNIQUE
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS proyectos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            cliente_id INT NOT NULL,
            categoria_id INT,
            titulo VARCHAR(255) NOT NULL,
            descripcion TEXT,
            presupuesto_min DECIMAL(10,2),
            presupuesto_max DECIMAL(10,2),
            estado ENUM('abierto', 'en_progreso', 'completado', 'cancelado') DEFAULT 'abierto',
            vistas INT DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            slug VARCHAR(255) DEFAULT 'proyecto',
            FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
            FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS desarrolladores_perfiles (
            id INT PRIMARY KEY AUTO_INCREMENT,
            usuario_id INT NOT NULL UNIQUE,
            tarifa_hora DECIMAL(10,2),
            descripcion TEXT,
            experiencia_años INT,
            calificacion_promedio DECIMAL(3,2) DEFAULT 0,
            proyectos_completados INT DEFAULT 0,
            tasa_exito INT DEFAULT 0,
            disponible BOOLEAN DEFAULT true,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clientes_perfiles (
            id INT PRIMARY KEY AUTO_INCREMENT,
            usuario_id INT NOT NULL UNIQUE,
            nombre_empresa VARCHAR(255),
            descripcion TEXT,
            sitio_web VARCHAR(255),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS propuestas (
            id INT PRIMARY KEY AUTO_INCREMENT,
            proyecto_id INT NOT NULL,
            desarrollador_id INT NOT NULL,
            monto_propuesto DECIMAL(10,2),
            tiempo_estimado VARCHAR(100),
            descripcion TEXT,
            estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'pendiente',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_proposal (proyecto_id, desarrollador_id),
            FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
            FOREIGN KEY (desarrollador_id) REFERENCES desarrolladores_perfiles(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contratos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            proyecto_id INT NOT NULL,
            desarrollador_id INT NOT NULL,
            monto_total DECIMAL(10,2),
            estado ENUM('en_revision', 'activo', 'completado', 'cancelado') DEFAULT 'en_revision',
            fecha_inicio DATE,
            fecha_fin_estimada DATE,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
            FOREIGN KEY (desarrollador_id) REFERENCES desarrolladores_perfiles(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mensajes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            remitente_id INT NOT NULL,
            destinatario_id INT NOT NULL,
            contenido TEXT,
            leido BOOLEAN DEFAULT false,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (remitente_id) REFERENCES usuarios(id),
            FOREIGN KEY (destinatario_id) REFERENCES usuarios(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS desarrollador_habilidades (
            desarrollador_id INT NOT NULL,
            habilidad_id INT NOT NULL,
            PRIMARY KEY (desarrollador_id, habilidad_id),
            FOREIGN KEY (desarrollador_id) REFERENCES desarrolladores_perfiles(id),
            FOREIGN KEY (habilidad_id) REFERENCES habilidades(id)
        )
    ");
    
    // Verificar si ya hay datos
    $check = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
    $result = $check->fetch();
    
    $dataInserted = false;
    
    if ($result['count'] == 0) {
        // Insertar datos de ejemplo
        insertSampleData($pdo);
        $dataInserted = true;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Base de datos configurada exitosamente',
        'data_inserted' => $dataInserted,
        'database' => 'devlink'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}

function insertSampleData($pdo) {
    // Clientes
    $password = password_hash('password123', PASSWORD_BCRYPT);
    
    $clients = [
        ['nombre' => 'Carlos', 'apellido' => 'Martínez', 'email' => 'carlos@example.com', 'cedula' => 'V-12345678'],
        ['nombre' => 'María', 'apellido' => 'García', 'email' => 'maria@example.com', 'cedula' => 'V-23456789'],
        ['nombre' => 'Juan', 'apellido' => 'López', 'email' => 'juan@example.com', 'cedula' => 'V-34567890'],
    ];
    
    $clientIds = [];
    foreach ($clients as $client) {
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (tipo, email, contraseña, cedula, telefono, nombre, apellido)
            VALUES ('cliente', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'cliente',
            $client['email'],
            $password,
            $client['cedula'],
            '+58412' . rand(1000000, 9999999),
            $client['nombre'],
            $client['apellido']
        ]);
        $clientIds[] = $pdo->lastInsertId();
    }
    
    // Desarrolladores
    $developers = [
        ['nombre' => 'Pedro', 'apellido' => 'Sánchez', 'email' => 'pedro@example.com', 'cedula' => 'V-45678901'],
        ['nombre' => 'Ana', 'apellido' => 'Rodríguez', 'email' => 'ana@example.com', 'cedula' => 'V-56789012'],
        ['nombre' => 'Luis', 'apellido' => 'Hernández', 'email' => 'luis@example.com', 'cedula' => 'V-67890123'],
    ];
    
    $devIds = [];
    foreach ($developers as $dev) {
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (tipo, email, contraseña, cedula, telefono, nombre, apellido)
            VALUES ('desarrollador', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'desarrollador',
            $dev['email'],
            $password,
            $dev['cedula'],
            '+58412' . rand(1000000, 9999999),
            $dev['nombre'],
            $dev['apellido']
        ]);
        $devIds[] = $pdo->lastInsertId();
    }
    
    // Categorías
    $categories = ['Web Development', 'Mobile App', 'Backend', 'UI/UX Design', 'DevOps'];
    $categoryIds = [];
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$cat, 'Categoría: ' . $cat]);
        $categoryIds[] = $pdo->lastInsertId();
    }
    
    // Habilidades
    $skills = ['PHP', 'JavaScript', 'Python', 'React', 'Vue.js', 'Node.js', 'MySQL', 'MongoDB', 'UI Design', 'UX Research'];
    $skillIds = [];
    foreach ($skills as $skill) {
        $stmt = $pdo->prepare("INSERT INTO habilidades (nombre) VALUES (?)");
        $stmt->execute([$skill]);
        $skillIds[] = $pdo->lastInsertId();
    }
    
    // Perfiles desarrolladores
    foreach ($devIds as $devId) {
        $stmt = $pdo->prepare("
            INSERT INTO desarrolladores_perfiles (usuario_id, tarifa_hora, descripcion, experiencia_años, calificacion_promedio, proyectos_completados, tasa_exito, disponible)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $devId,
            rand(20, 100),
            'Desarrollador con experiencia en proyectos web',
            rand(1, 10),
            rand(35, 50) / 10,
            rand(5, 30),
            rand(80, 100)
        ]);
        
        $devProfileId = $pdo->lastInsertId();
        
        // Asignar habilidades
        $randomSkills = array_rand($skillIds, rand(2, 4));
        foreach ((array)$randomSkills as $idx) {
            $stmt = $pdo->prepare("
                INSERT INTO desarrollador_habilidades (desarrollador_id, habilidad_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$devProfileId, $skillIds[$idx]]);
        }
    }
    
    // Perfiles clientes
    foreach ($clientIds as $clientId) {
        $stmt = $pdo->prepare("
            INSERT INTO clientes_perfiles (usuario_id, nombre_empresa, descripcion, sitio_web)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $clientId,
            'Empresa ' . rand(100, 999),
            'Empresa dedicada a servicios digitales',
            'https://empresa' . rand(100, 999) . '.com'
        ]);
    }
    
    // Proyectos
    $projects = [
        ['titulo' => 'Sistema de Gestión de Inventario', 'min' => 500, 'max' => 1500],
        ['titulo' => 'App Móvil de E-commerce', 'min' => 2000, 'max' => 5000],
        ['titulo' => 'Rediseño de Sitio Web', 'min' => 300, 'max' => 800],
        ['titulo' => 'API REST para Plataforma', 'min' => 1000, 'max' => 3000],
        ['titulo' => 'Aplicación de Streaming', 'min' => 5000, 'max' => 10000],
    ];
    
    $states = ['abierto', 'en_progreso', 'completado'];
    
    foreach ($projects as $index => $proj) {
        $stmt = $pdo->prepare("
            INSERT INTO proyectos (cliente_id, categoria_id, titulo, descripcion, presupuesto_min, presupuesto_max, estado, vistas, slug)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $clientIds[$index % count($clientIds)],
            $categoryIds[$index % count($categoryIds)],
            $proj['titulo'],
            'Descripción detallada: ' . $proj['titulo'],
            $proj['min'],
            $proj['max'],
            $states[$index % count($states)],
            rand(10, 100),
            strtolower(str_replace(' ', '-', $proj['titulo']))
        ]);
    }
}
?>
