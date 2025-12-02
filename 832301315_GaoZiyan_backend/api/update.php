<?php
/**
 * 修改联系人API
 * 修改时必须从后端数据库读取数据，禁止使用缓存数据
 */

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
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
    // 只接受POST/PUT请求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
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
    
    // 从数据库读取当前数据（确保数据是最新的，不使用缓存）
    $checkSql = "SELECT * FROM contacts WHERE id = ?";
    $currentData = $db->fetchOne($checkSql, [$id]);
    
    if (!$currentData) {
        throw new Exception('联系人不存在');
    }
    
    // 验证必填字段
    if (empty($input['name'])) {
        throw new Exception('姓名不能为空');
    }
    
    if (empty($input['phone'])) {
        throw new Exception('电话号码不能为空');
    }
    
    // 验证电话号码格式
    if (!preg_match('/^1[3-9]\d{9}$/', $input['phone'])) {
        throw new Exception('电话号码格式不正确');
    }
    
    // 检查电话号码是否被其他联系人使用
    $checkPhoneSql = "SELECT id FROM contacts WHERE phone = ? AND id != ?";
    $phoneExists = $db->fetchOne($checkPhoneSql, [$input['phone'], $id]);
    if ($phoneExists) {
        throw new Exception('该电话号码已被其他联系人使用');
    }
    
    // 更新数据（使用位置参数，避免参数混合使用）
    $sql = "UPDATE contacts SET name = ?, phone = ?, email = ?, address = ?, notes = ? WHERE id = ?";
    $params = [
        $input['name'],
        $input['phone'],
        isset($input['email']) ? $input['email'] : null,
        isset($input['address']) ? $input['address'] : null,
        isset($input['notes']) ? $input['notes'] : null,
        $id
    ];
    
    $affectedRows = $db->execute($sql, $params);
    
    echo json_encode([
        'success' => true,
        'message' => '修改成功',
        'affected_rows' => $affectedRows
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

