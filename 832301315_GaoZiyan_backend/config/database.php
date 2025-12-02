<?php
/**
 * 数据库管理配置文件
 * 统一的数据库连接管理类
 */

// 数据库配置常量
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'contacts_db');
define('DB_CHARSET', 'utf8mb4');

// 开发环境配置
define('DEBUG', true);

// 错误处理配置
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

/**
 * 数据库管理类
 * 使用单例模式，确保全局只有一个数据库连接
 */
class DatabaseManager {
    private static $instance = null;
    private $connection;
    
    /**
     * 私有构造函数，防止外部实例化
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG) {
                die(json_encode([
                    'success' => false,
                    'message' => '数据库连接失败: ' . $e->getMessage()
                ]));
            } else {
                die(json_encode([
                    'success' => false,
                    'message' => '数据库连接失败，请联系管理员'
                ]));
            }
        }
    }
    
    /**
     * 获取数据库管理器实例
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 获取PDO连接对象
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * 执行查询（SELECT）
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return array 查询结果
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            if (DEBUG) {
                throw new Exception("查询失败: " . $e->getMessage());
            } else {
                throw new Exception("查询失败");
            }
        }
    }
    
    /**
     * 执行插入/更新/删除操作
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return bool|int 成功返回影响行数或插入ID，失败返回false
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            
            // 如果是INSERT操作，返回插入的ID
            if (stripos($sql, 'INSERT') === 0) {
                return $this->connection->lastInsertId();
            }
            
            // 其他操作返回影响的行数
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if (DEBUG) {
                throw new Exception("执行失败: " . $e->getMessage());
            } else {
                throw new Exception("执行失败");
            }
        }
    }
    
    /**
     * 获取单条记录
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return array|false 查询结果
     */
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            if (DEBUG) {
                throw new Exception("查询失败: " . $e->getMessage());
            } else {
                throw new Exception("查询失败");
            }
        }
    }
    
    /**
     * 防止克隆
     */
    private function __clone() {}
}

