<?php
/**
 * 获取单个联系人信息API
 * 用于修改时从数据库读取最新数据
 */

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

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
    // 获取联系人ID
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        throw new Exception('无效的联系人ID');
    }
    
    // 获取数据库实例
    $db = DatabaseManager::getInstance();
    
    // 从数据库读取数据（禁止使用缓存）
    $sql = "SELECT * FROM contacts WHERE id = ?";
    $contact = $db->fetchOne($sql, [$id]);
    
    if (!$contact) {
        throw new Exception('联系人不存在');
    }
    
    // 返回成功响应
    echo json_encode([
        'success' => true,
        'data' => $contact
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

