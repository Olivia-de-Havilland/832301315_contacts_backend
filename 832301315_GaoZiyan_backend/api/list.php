<?php
/**
 * 获取联系人列表API
 * 支持搜索功能
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
    // 获取数据库实例
    $db = DatabaseManager::getInstance();
    
    // 获取搜索关键词
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    
    // 构建SQL语句
    if (!empty($keyword)) {
        $sql = "SELECT * FROM contacts WHERE name LIKE ? OR phone LIKE ? ORDER BY id DESC";
        $params = ['%' . $keyword . '%', '%' . $keyword . '%'];
        $contacts = $db->query($sql, $params);
    } else {
        $sql = "SELECT * FROM contacts ORDER BY id DESC";
        $contacts = $db->query($sql);
    }
    
    // 返回成功响应
    echo json_encode([
        'success' => true,
        'data' => $contacts,
        'count' => count($contacts)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

