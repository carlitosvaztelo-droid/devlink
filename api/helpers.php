<?php
// api/helpers.php
// Funciones auxiliares compartidas para el API

class APIResponse {
    public static function success($data = [], $message = 'Operación exitosa', $code = 200) {
        http_response_code($code);
        return json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    public static function error($message = 'Error en la operación', $code = 400, $error = null) {
        http_response_code($code);
        $response = [
            'success' => false,
            'error' => $message
        ];
        if ($error) {
            $response['details'] = $error;
        }
        return json_encode($response);
    }
}

class Validator {
    public static function required($value, $field = 'Campo') {
        if (empty($value)) {
            throw new Exception("$field es requerido");
        }
        return $value;
    }
    
    public static function email($value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }
        return $value;
    }
    
    public static function numeric($value, $field = 'Valor') {
        if (!is_numeric($value)) {
            throw new Exception("$field debe ser numérico");
        }
        return $value;
    }
    
    public static function minLength($value, $min, $field = 'Campo') {
        if (strlen($value) < $min) {
            throw new Exception("$field debe tener al menos $min caracteres");
        }
        return $value;
    }
}

class Database {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(function($key) { return ':' . $key; }, array_keys($data)));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }
    
    public function update($table, $data, $where) {
        $setClause = implode(', ', array_map(function($key) { return "$key = :$key"; }, array_keys($data)));
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->pdo->exec($sql);
    }
    
    public function select($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function count($table, $where = '') {
        $sql = "SELECT COUNT(*) as count FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        $result = $this->selectOne($sql);
        return $result['count'];
    }
}

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        self::start();
        session_destroy();
    }
    
    public static function isAuthenticated() {
        return self::has('user_id');
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
    
    public static function getUserType() {
        return self::get('user_type');
    }
}

function corsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true);
}

function slug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

?>
