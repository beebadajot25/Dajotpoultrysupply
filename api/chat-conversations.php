<?php
/**
 * Chat API: List Conversations
 * Returns all conversations for the current user with last message preview
 */
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
$guest_token = $_GET['guest_token'] ?? '';

if (!$user_id && !$guest_token) {
    echo json_encode(['status' => 'success', 'conversations' => [], 'total_unread' => 0]);
    exit;
}

// Build Search Condition
if ($user_id) {
    $where = "(c.buyer_id = $user_id OR c.seller_id = $user_id)";
} else {
    $where = "c.guest_token = '" . $conn->real_escape_string($guest_token) . "'";
}

// Get all conversations with guest info
$sql = "SELECT c.*, 
        u.username as other_user_name,
        u.profile_photo as other_user_photo,
        u.farm_name as other_farm_name,
        l.product_name,
        l.image as product_image,
        (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
        (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != $user_id AND is_read = 0) as unread_count
        FROM conversations c
        LEFT JOIN users u ON (CASE WHEN c.buyer_id = $user_id THEN c.seller_id ELSE c.buyer_id END) = u.id
        LEFT JOIN listings l ON c.listing_id = l.id
        WHERE $where
        ORDER BY c.updated_at DESC";

$result = $conn->query($sql);
$conversations = [];
$total_unread = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $total_unread += $row['unread_count'];
        
        $other_name = $row['other_farm_name'] ?: $row['other_user_name'];
        if (!$other_name && $row['guest_name']) {
            $other_name = "Guest: " . $row['guest_name'];
        }

        $conversations[] = [
            'id' => $row['id'],
            'other_user_id' => $row['buyer_id'] == $user_id ? $row['seller_id'] : ($row['buyer_id'] ?: 0),
            'other_user_name' => $other_name ?: 'Unknown User',
            'other_user_photo' => $row['other_user_photo'],
            'product_name' => $row['product_name'],
            'product_image' => $row['product_image'],
            'last_message' => $row['last_message'] ? substr($row['last_message'], 0, 50) . (strlen($row['last_message']) > 50 ? '...' : '') : 'No messages yet',
            'last_message_time' => $row['last_message_time'] ? date('M d', strtotime($row['last_message_time'])) : '',
            'unread_count' => $row['unread_count']
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'conversations' => $conversations,
    'total_unread' => $total_unread
]);
?>
