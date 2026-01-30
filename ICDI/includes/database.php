<?php
/**
 * Database Connection and Helper Functions
 * 
 * Provides a singleton database connection pattern and helper functions
 * for executing queries, fetching results, and performing CRUD operations.
 * 
 * @package PROWLWAY
 * @subpackage Includes
 * @author ICDISG Development Team
 * @since 1.0.0
 */

require_once __DIR__ . '/config.php';

/**
 * Database Singleton Class
 * 
 * Manages a single PDO database connection instance using the singleton pattern.
 * Ensures only one database connection exists throughout the application lifecycle.
 * 
 * @package PROWLWAY
 * @subpackage Includes
 */
class Database {
    /** @var Database|null Singleton instance */
    private static $instance = null;
    
    /** @var PDO Database connection */
    private $connection;
    
    /**
     * Private constructor to prevent direct instantiation
     * 
     * Creates a PDO connection with error handling and UTF-8 charset.
     * Connection settings:
     * - Error mode: Exception (throws exceptions on errors)
     * - Fetch mode: Associative array
     * - Prepared statements: Native (not emulated)
     * 
     * @throws PDOException If database connection fails
     */
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
    
    /**
     * Get singleton instance of Database
     * 
     * Creates a new instance if one doesn't exist, otherwise returns the existing instance.
     * This ensures only one database connection is maintained throughout the application.
     * 
     * @return Database Singleton instance
     * @throws Exception If database connection fails
     */
    public static function getInstance() {
        if (self::$instance === null) {
            try {
                self::$instance = new self();
            } catch (Exception $e) {
                error_log("Database::getInstance() error: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$instance;
    }
    
    /**
     * Get PDO database connection
     * 
     * Returns the PDO connection object for executing queries.
     * 
     * @return PDO PDO database connection object
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prevent cloning of singleton instance
     * 
     * @return void
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of singleton instance
     * 
     * @throws Exception Always throws exception to prevent unserialization
     * @return void
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Get database connection instance
 * 
 * Convenience function to retrieve the PDO database connection.
 * Returns null if connection fails, allowing calling code to handle errors gracefully.
 * 
 * @return PDO|null PDO connection object, or null on failure
 * 
 * @example
 * $db = getDB();
 * if ($db === null) {
 *     // Handle connection failure
 *     return;
 * }
 * $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
 */
function getDB() {
    try {
        return Database::getInstance()->getConnection();
    } catch (Exception $e) {
        error_log("getDB() error: " . $e->getMessage());
        // Return null if connection fails - let calling code handle gracefully
        return null;
    }
}

/**
 * Execute a prepared SQL query
 * 
 * Prepares and executes a SQL query with optional parameters.
 * Uses prepared statements to prevent SQL injection attacks.
 * 
 * @param string $sql SQL query string with placeholders (e.g., "SELECT * FROM users WHERE id = :id")
 * @param array $params Associative array of parameters (e.g., ['id' => 1])
 * 
 * @return PDOStatement|false PDO statement object on success, false on failure
 * 
 * @example
 * $stmt = dbQuery("SELECT * FROM announcements WHERE status = :status", ['status' => 'published']);
 * if ($stmt !== false) {
 *     $results = $stmt->fetchAll();
 * }
 */
function dbQuery($sql, $params = []) {
    try {
        $db = getDB();
        if ($db === null) {
            error_log("dbQuery: Database connection is null");
            return false;
        }
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            error_log("dbQuery: Prepare failed for query: " . substr($sql, 0, 100));
            return false;
        }
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . substr($sql, 0, 100));
        return false;
    } catch (Exception $e) {
        error_log("Database Query Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch all rows from a query result
 * 
 * Executes a query and returns all matching rows as an associative array.
 * Returns an empty array on failure or if no rows are found.
 * 
 * @param string $sql SQL query string with placeholders
 * @param array $params Associative array of parameters
 * 
 * @return array Array of associative arrays, each representing a row. Empty array on failure.
 * 
 * @example
 * $announcements = dbFetchAll(
 *     "SELECT * FROM announcements WHERE status = :status ORDER BY created_at DESC",
 *     ['status' => 'published']
 * );
 */
function dbFetchAll($sql, $params = []) {
    try {
        $stmt = dbQuery($sql, $params);
        if ($stmt === false) {
            error_log("dbFetchAll failed for query: " . substr($sql, 0, 100));
            return [];
        }
        $result = $stmt->fetchAll();
        return $result !== false ? $result : [];
    } catch (Exception $e) {
        error_log("dbFetchAll exception: " . $e->getMessage() . " | Query: " . substr($sql, 0, 100));
        return [];
    }
}

/**
 * Fetch a single row from a query result
 * 
 * Executes a query and returns the first matching row as an associative array.
 * Returns null if no row is found or on failure.
 * 
 * @param string $sql SQL query string with placeholders
 * @param array $params Associative array of parameters
 * 
 * @return array|null Associative array representing the row, or null if not found/failed
 * 
 * @example
 * $admin = dbFetchOne(
 *     "SELECT * FROM admins WHERE email = :email",
 *     ['email' => 'admin@example.com']
 * );
 */
function dbFetchOne($sql, $params = []) {
    try {
        $stmt = dbQuery($sql, $params);
        if ($stmt === false) {
            error_log("dbFetchOne failed for query: " . substr($sql, 0, 100));
            return null;
        }
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    } catch (Exception $e) {
        error_log("dbFetchOne exception: " . $e->getMessage() . " | Query: " . substr($sql, 0, 100));
        return null;
    }
}

/**
 * Insert a new record into a table
 * 
 * Inserts a new row into the specified table with the provided data.
 * Automatically escapes table and column names to prevent SQL injection.
 * 
 * @param string $table Table name (will be escaped with backticks)
 * @param array $data Associative array of column => value pairs
 * 
 * @return int|false Last insert ID on success, false on failure
 * 
 * @example
 * $id = dbInsert('announcements', [
 *     'title' => 'New Announcement',
 *     'description' => 'Description text',
 *     'status' => 'draft',
 *     'created_by' => 1
 * ]);
 * if ($id !== false) {
 *     echo "Inserted with ID: $id";
 * }
 */
function dbInsert($table, $data) {
    $db = getDB();
    if ($db === null) {
        $errorMsg = "Database connection failed. Please check your database configuration.";
        error_log("dbInsert: Database connection is null");
        global $lastDbError;
        $lastDbError = $errorMsg;
        return false;
    }
    
    $fields = array_keys($data);
    $placeholders = ':' . implode(', :', $fields);
    $fieldsList = implode(', ', $fields);
    
    $sql = "INSERT INTO `{$table}` ({$fieldsList}) VALUES ({$placeholders})";
    
    try {
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            $errorInfo = $db->errorInfo();
            error_log("dbInsert: Prepare failed for table {$table}. Error: " . ($errorInfo[2] ?? 'Unknown error'));
            throw new PDOException($errorInfo[2] ?? 'Failed to prepare INSERT statement');
        }
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Database Insert Error: " . $e->getMessage() . " | SQL: " . substr($sql, 0, 200));
        // Store error for retrieval
        global $lastDbError;
        $lastDbError = $e->getMessage();
        return false;
    } catch (Exception $e) {
        error_log("Database Insert Exception: " . $e->getMessage());
        global $lastDbError;
        $lastDbError = $e->getMessage();
        return false;
    }
}

/**
 * Update records in a table
 * 
 * Updates rows matching the WHERE clause with the provided data.
 * Uses prepared statements for both SET and WHERE clauses.
 * 
 * @param string $table Table name (will be escaped with backticks)
 * @param array $data Associative array of column => value pairs to update
 * @param string $where WHERE clause with placeholders (e.g., "id = :id AND status = :status")
 * @param array $whereParams Associative array of parameters for WHERE clause
 * 
 * @return bool True on success, false on failure
 * 
 * @example
 * $success = dbUpdate(
 *     'announcements',
 *     ['status' => 'published', 'updated_at' => date('Y-m-d H:i:s')],
 *     'id = :id',
 *     ['id' => 5]
 * );
 */
function dbUpdate($table, $data, $where, $whereParams = []) {
    $db = getDB();
    if ($db === null) {
        $errorMsg = "Database connection failed. Please check your database configuration.";
        error_log("dbUpdate: Database connection is null");
        global $lastDbError;
        $lastDbError = $errorMsg;
        return false;
    }
    
    $fields = [];
    foreach (array_keys($data) as $field) {
        $fields[] = "`{$field}` = :{$field}";
    }
    $fieldsList = implode(', ', $fields);
    
    $sql = "UPDATE `{$table}` SET {$fieldsList} WHERE {$where}";
    
    try {
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            $errorInfo = $db->errorInfo();
            error_log("dbUpdate: Prepare failed for table {$table}. Error: " . ($errorInfo[2] ?? 'Unknown error'));
            throw new PDOException($errorInfo[2] ?? 'Failed to prepare UPDATE statement');
        }
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database Update Error: " . $e->getMessage() . " | SQL: " . substr($sql, 0, 200));
        // Store error for retrieval
        global $lastDbError;
        $lastDbError = $e->getMessage();
        return false;
    } catch (Exception $e) {
        error_log("Database Update Exception: " . $e->getMessage());
        global $lastDbError;
        $lastDbError = $e->getMessage();
        return false;
    }
}

/**
 * Delete records from a table
 * 
 * Deletes rows matching the WHERE clause.
 * Uses prepared statements to prevent SQL injection.
 * 
 * @param string $table Table name (will be escaped with backticks)
 * @param string $where WHERE clause with placeholders (e.g., "id = :id")
 * @param array $whereParams Associative array of parameters for WHERE clause
 * 
 * @return bool True on success, false on failure
 * 
 * @example
 * $success = dbDelete('announcements', 'id = :id', ['id' => 5]);
 * 
 * @warning Use with caution - this permanently deletes data. Consider soft deletes instead.
 */
function dbDelete($table, $where, $whereParams = []) {
    $db = getDB();
    if ($db === null) {
        error_log("dbDelete: Database connection is null");
        return false;
    }
    
    $sql = "DELETE FROM `{$table}` WHERE {$where}";
    
    try {
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            $errorInfo = $db->errorInfo();
            error_log("dbDelete: Prepare failed for table {$table}. Error: " . ($errorInfo[2] ?? 'Unknown error'));
            throw new PDOException($errorInfo[2] ?? 'Failed to prepare DELETE statement');
        }
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database Delete Error: " . $e->getMessage() . " | SQL: " . substr($sql, 0, 200));
        global $lastDbError;
        $lastDbError = $e->getMessage();
        return false;
    } catch (Exception $e) {
        error_log("Database Delete Exception: " . $e->getMessage());
        global $lastDbError;
        $lastDbError = $e->getMessage();
        return false;
    }
}

/**
 * Get the last database error message
 * @return string|null Last database error message or null if none
 */
function getLastDbError() {
    global $lastDbError;
    return $lastDbError ?? null;
}

?>

