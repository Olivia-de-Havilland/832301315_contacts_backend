<?php
/**
 * 删除联系人API
 * 删除前从数据库确认数据存在
 */

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// 处理OPTIONS请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 引入数据库配置（使用绝对路径）
$configFile = dirname(__DIR__) . '/config/database.php';
if (!file_exists($configFile)) {
    die(json_encode([
        'success' => false,
        'message' => '配置文件不存在'
    ]));
}
require_once $configFile;

try {
    // 只接受POST/DELETE请求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new Exception('请求方法错误');
    }
    
    // 获取POST数据
    $input = json_decode(file_get_contents('php://input'), true);
    
    // 验证ID
    if (empty($input['id']) || intval($input['id']) <= 0) {
        throw new Exception('无效的联系人ID');
    }
    
    $id = intval($input['id']);
    
    // 获取数据库实例
    $db = DatabaseManager::getInstance();
    
    // 从数据库确认数据存在（不使用缓存）
    $checkSql = "SELECT * FROM contacts WHERE id = ?";
    $contact = $db->fetchOne($checkSql, [$id]);
    
    if (!$contact) {
        throw new Exception('联系人不存在或已被删除');
    }
    
    // 执行删除
    $sql = "DELETE FROM contacts WHERE id = ?";
    $affectedRows = $db->execute($sql, [$id]);
    
    if ($affectedRows > 0) {
        echo json_encode([
            'success' => true,
            'message' => '删除成功'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('删除失败');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

