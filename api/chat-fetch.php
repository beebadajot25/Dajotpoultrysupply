<?php
/**
 * Chat API: Fetch Messages
 * GET: conversation_id, last_id (optional, for polling)
 */
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
$guest_token = $_GET['guest_token'] ?? '';
$conversation_id = intval($_GET['conversation_id'] ?? 0);
$last_id = intval($_GET['last_id'] ?? 0);

if (!$conversation_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing conversation_id']);
    exit;
}

// Verify user/guest is part of this conversation
$token_check = $guest_token ? "OR guest_token = '" . $conn->real_escape_string($guest_token) . "'" : "";
$check_sql = "SELECT * FROM conversations WHERE id = $conversation_id 
              AND (buyer_id = $user_id OR seller_id = $user_id $token_check)";
$check_res = $conn->query($check_sql);

if (!$check_res || $check_res->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

$conversation = $check_res->fetch_assoc();

// Fetch messages
$msg_sql = "SELECT m.*, u.username as sender_name,
            CASE WHEN m.sender_id = 0 THEN c.guest_name ELSE u.username END as display_name
            FROM messages m 
            JOIN conversations c ON m.conversation_id = c.id
            LEFT JOIN users u ON m.sender_id = u.id 
            WHERE m.conversation_id = $conversation_id";
if ($last_id > 0) {
    $msg_sql .= " AND m.id > $last_id";
}
$msg_sql .= " ORDER BY m.created_at ASC";

$msg_res = $conn->query($msg_sql);
$messages = [];

if ($msg_res) {
    while ($row = $msg_res->fetch_assoc()) {
        // Logic: Who "owns" this message in the current view?
        $is_mine = false;
        if ($user_id > 0 && $row['sender_id'] == $user_id) {
            $is_mine = true;
        } elseif (!$user_id && $guest_token && $row['sender_id'] == 0) {
            // Guest viewing their own messages
            $is_mine = true;
        }

        $messages[] = [
            'id' => $row['id'],
            'sender_id' => $row['sender_id'],
            'sender_name' => $row['display_name'] ?: 'Guest',
            'message' => htmlspecialchars($row['message']),
            'is_mine' => $is_mine,
            'created_at' => date('M d, g:i A', strtotime($row['created_at']))
        ];
    }
    
    // Mark messages as read
    $reader_id = $user_id ?: '0';
    $conn->query("UPDATE messages SET is_read = 1 
                  WHERE conversation_id = $conversation_id AND sender_id != $reader_id");
}

echo json_encode([
    'status' => 'success',
    'conversation' => $conversation,
    'messages' => $messages
]);
?>
