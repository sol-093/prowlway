<?php
/**
 * Database Connection and Helper Functions
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Get database connection
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Execute a query and return results
 */
function dbQuery($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch all results
 */
function dbFetchAll($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Fetch single row
 */
function dbFetchOne($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

/**
 * Insert and return last insert ID
 */
function dbInsert($table, $data) {
    $db = getDB();
    $fields = array_keys($data);
    $placeholders = ':' . implode(', :', $fields);
    $fieldsList = implode(', ', $fields);
    
    $sql = "INSERT INTO `{$table}` ({$fieldsList}) VALUES ({$placeholders})";
    
    try {
        $stmt = $db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Database Insert Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update record
 */
function dbUpdate($table, $data, $where, $whereParams = []) {
    $db = getDB();
    $fields = [];
    foreach (array_keys($data) as $field) {
        $fields[] = "`{$field}` = :{$field}";
    }
    $fieldsList = implode(', ', $fields);
    
    $sql = "UPDATE `{$table}` SET {$fieldsList} WHERE {$where}";
    
    try {
        $stmt = $db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database Update Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete record
 */
function dbDelete($table, $where, $whereParams = []) {
    $db = getDB();
    $sql = "DELETE FROM `{$table}` WHERE {$where}";
    
    try {
        $stmt = $db->prepare($sql);
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database Delete Error: " . $e->getMessage());
        return false;
    }
}

?>

