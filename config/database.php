<?php
// config/database.php

// Configuración para Laragon (por defecto)
$host = 'localhost';
$dbname = 'devlink';
$username = 'root';
$password = ''; // Laragon generalmente no tiene contraseña por defecto

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Verificar conexión
    $pdo->query("SELECT 1");
    
    error_log('Conexión a BD exitosa');
    
} catch (PDOException $e) {
    error_log('Error de conexión a BD: ' . $e->getMessage());
    die(json_encode([
        'success' => false, 
        'error' => 'Error de conexión a la base de datos: ' . $e->getMessage()
    ]));
}