<?php
/**
 * Chat API - Tanah Suci Travel
 * File: chat_api.php
 * API untuk kirim/terima pesan chat
 */

header('Content-Type: application/json');

require_once 'config/database.php';

session_start();
$db = getDB();

// Cek apakah user login
if (!isset($_SESSION['chat_user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['chat_user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Handle offline status
if ($action === 'offline') {
    $stmt = $db->prepare("UPDATE chat_users SET is_online = 0 WHERE id = ?");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true]);
    exit;
}

// Handle send message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'send') {
        $message = trim($data['message'] ?? '');
        
        if (empty($message)) {
            echo json_encode(['error' => 'Pesan tidak boleh kosong']);
            exit;
        }
        
        if (strlen($message) > 500) {
            echo json_encode(['error' => 'Pesan terlalu panjang']);
            exit;
        }
        
        try {
            // Update online status
            $stmt = $db->prepare("UPDATE chat_users SET is_online = 1, last_seen = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
            
            // Insert message
            $stmt = $db->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
            $stmt->execute([$userId, $message]);
            
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Gagal mengirim pesan']);
        }
        exit;
    }
}

// Handle get messages
if ($action === 'get') {
    $lastId = intval($_GET['last_id'] ?? 0);
    
    try {
        // Update online status
        $stmt = $db->prepare("UPDATE chat_users SET is_online = 1, last_seen = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        
        // Set offline users yang tidak aktif > 1 menit
        $db->exec("UPDATE chat_users SET is_online = 0 WHERE last_seen < DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
        
        // Get new messages
        $stmt = $db->prepare("
            SELECT m.id, m.user_id, m.message, m.created_at, u.username, u.color
            FROM chat_messages m
            JOIN chat_users u ON m.user_id = u.id
            WHERE m.id > ?
            ORDER BY m.id ASC
            LIMIT 50
        ");
        $stmt->execute([$lastId]);
        $messages = $stmt->fetchAll();
        
        // Get online users list
        $onlineUsers = $db->query("SELECT id, username, color FROM chat_users WHERE is_online = 1 ORDER BY username")->fetchAll();
        
        // Get online count
        $onlineCount = count($onlineUsers);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages,
            'online_count' => $onlineCount,
            'online_users' => $onlineUsers
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Gagal memuat pesan']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
