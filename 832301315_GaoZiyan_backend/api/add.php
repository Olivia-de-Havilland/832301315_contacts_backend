<?php
/**
 * 新增联系人API
 */

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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
    // 只接受POST请求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('请求方法错误');
    }
    
    // 获取POST数据
    $input = json_decode(file_get_contents('php://input'), true);
    
    // 验证必填字段
    if (empty($input['name'])) {
        throw new Exception('姓名不能为空');
    }
    
    if (empty($input['phone'])) {
        throw new Exception('电话号码不能为空');
    }
    
    // 验证电话号码格式（简单验证）
    if (!preg_match('/^1[3-9]\d{9}$/', $input['phone'])) {
        throw new Exception('电话号码格式不正确');
    }
    
    // 获取数据库实例
    $db = DatabaseManager::getInstance();
    
    // 检查电话号码是否已存在
    $checkSql = "SELECT id FROM contacts WHERE phone = ?";
    $exists = $db->fetchOne($checkSql, [$input['phone']]);
    if ($exists) {
        throw new Exception('该电话号码已存在');
    }
    
    // 插入数据
    $sql = "INSERT INTO contacts (name, phone, email, address, notes) VALUES (?, ?, ?, ?, ?)";
    $params = [
        $input['name'],
        $input['phone'],
        isset($input['email']) ? $input['email'] : null,
        isset($input['address']) ? $input['address'] : null,
        isset($input['notes']) ? $input['notes'] : null
    ];
    
    $insertId = $db->execute($sql, $params);
    
    if ($insertId) {
        echo json_encode([
            'success' => true,
            'message' => '添加成功',
            'id' => $insertId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('添加失败');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

