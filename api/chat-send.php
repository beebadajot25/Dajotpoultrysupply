<?php
/**
 * Chat API: Send Message
 * POST: sender_id, receiver_id, message, listing_id (optional)
 */
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../includes/security.php';

// Sender identification (LoggedIn or Guest)
$user_id = $_SESSION['user_id'] ?? 0;
$guest_token = trim($_POST['guest_token'] ?? '');
$guest_name = trim($_POST['guest_name'] ?? '');

$receiver_id = intval($_POST['receiver_id'] ?? 0);
$conversation_id = intval($_POST['conversation_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$listing_id = !empty($_POST['listing_id']) ? intval($_POST['listing_id']) : null;

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Security validation failed. Please refresh.']);
    exit;
}

if (empty($message) || (!$receiver_id && !$conversation_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

if (!$user_id && !$guest_token) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required (User or Guest)']);
    exit;
}

// Find or Create Conversation
if (!$conversation_id) {
    if ($user_id) {
        $buyer_id = min($user_id, $receiver_id);
        $seller_id = max($user_id, $receiver_id);
        $conv_sql = "SELECT id FROM conversations WHERE buyer_id = $buyer_id AND seller_id = $seller_id";
    } else {
        $buyer_id = 'NULL';
        $seller_id = $receiver_id;
        $conv_sql = "SELECT id FROM conversations WHERE guest_token = '" . $conn->real_escape_string($guest_token) . "' AND seller_id = $seller_id";
    }

    if ($listing_id) $conv_sql .= " AND listing_id = $listing_id";
    $conv_sql .= " LIMIT 1";

    $conv_res = $conn->query($conv_sql);

    if ($conv_res && $conv_res->num_rows > 0) {
        $conversation_id = $conv_res->fetch_assoc()['id'];
    } else {
        // Create new conversation
        $listing_val = $listing_id ?: 'NULL';
        $g_token_val = $guest_token ? "'" . $conn->real_escape_string($guest_token) . "'" : 'NULL';
        $g_name_val = $guest_name ? "'" . $conn->real_escape_string($guest_name) . "'" : 'NULL';
        $buyer_id_val = $user_id ? min($user_id, $receiver_id) : 'NULL';
        $seller_id_val = $user_id ? max($user_id, $receiver_id) : $receiver_id;

        $create_sql = "INSERT INTO conversations (buyer_id, seller_id, guest_token, guest_name, listing_id) 
                       VALUES ($buyer_id_val, $seller_id_val, $g_token_val, $g_name_val, $listing_val)";
        if ($conn->query($create_sql)) {
            $conversation_id = $conn->insert_id;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create conversation: ' . $conn->error]);
            exit;
        }
    }
}

// Update guest name if it's provided and missing
if ($guest_name && !$user_id) {
    $conn->query("UPDATE conversations SET guest_name = '" . $conn->real_escape_string($guest_name) . "' WHERE id = $conversation_id AND guest_name IS NULL");
}

$sender_id = $user_id; // Will be 0 if Guest

// Insert message
$message_escaped = $conn->real_escape_string($message);
$msg_sql = "INSERT INTO messages (conversation_id, sender_id, message) 
            VALUES ($conversation_id, $sender_id, '$message_escaped')";

if ($conn->query($msg_sql)) {
    // Update conversation timestamp
    $conn->query("UPDATE conversations SET updated_at = NOW() WHERE id = $conversation_id");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent',
        'conversation_id' => $conversation_id,
        'message_id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message: ' . $conn->error]);
}
?>
