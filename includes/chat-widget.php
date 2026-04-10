<?php
/**
 * Floating Chat Widget
 * Include this in footer.php for logged-in users
 */
if (!isset($_SESSION)) session_start();
$chat_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user_role = $_SESSION['role'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);

// Hide floating bubble for farmers everywhere except dashboard
$hide_bubble = ($user_role === 'farmer' && $current_page !== 'vendor-dashboard.php');
?>

<!-- Chat Widget Styles -->
<style>
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.chat-bubble {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(46, 125, 50, 0.4);
    transition: transform 0.3s, box-shadow 0.3s;
}

.chat-bubble:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(46, 125, 50, 0.5);
}

.chat-bubble i {
    color: white;
    font-size: 26px;
}

.chat-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #e53e3e;
    color: white;
    font-size: 12px;
    font-weight: bold;
    min-width: 22px;
    height: 22px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.chat-panel {
    position: absolute;
    bottom: 75px;
    right: 0;
    width: 360px;
    max-width: calc(100vw - 40px);
    height: 500px;
    max-height: calc(100vh - 120px);
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.chat-panel.open { display: flex; }

.chat-header {
    background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-header h4 { margin: 0; font-size: 1.1em; }

.chat-header .back-btn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 1.2em;
    padding: 5px;
    display: none;
}

.chat-header .close-btn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 1.5em;
    line-height: 1;
}

.chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background: #f7fafc;
}

/* Conversation List */
.conv-list { display: block; }
.conv-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 10px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: background 0.2s;
    border: 1px solid #eee;
}

.conv-item:hover { background: #f0fff4; border-color: #c6f6d5; }

.conv-avatar {
    width: 45px;
    height: 45px;
    background: #2e7d32;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1em;
    flex-shrink: 0;
}

.conv-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.conv-info { flex: 1; min-width: 0; }
.conv-name { font-weight: 600; color: #1a202c; margin-bottom: 3px; }
.conv-preview { font-size: 0.85em; color: #718096; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-meta { text-align: right; flex-shrink: 0; }
.conv-time { font-size: 0.75em; color: #a0aec0; }
.conv-unread {
    background: #e53e3e;
    color: white;
    font-size: 0.7em;
    padding: 2px 6px;
    border-radius: 10px;
    margin-top: 3px;
    display: inline-block;
}

/* Messages View */
.messages-view { display: none; flex-direction: column; height: 100%; }
.messages-view.active { display: flex; }

.messages-list {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message-bubble {
    max-width: 80%;
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 0.95em;
    line-height: 1.4;
}

.message-bubble.sent {
    background: #2e7d32;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}

.message-bubble.received {
    background: white;
    color: #1a202c;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
    border: 1px solid #e2e8f0;
}

.message-time {
    font-size: 0.7em;
    opacity: 0.7;
    margin-top: 4px;
}

.chat-input-area {
    padding: 12px;
    background: white;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 10px;
}

.chat-input-area input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    font-size: 0.95em;
    outline: none;
}

.chat-input-area input:focus { border-color: #2e7d32; }

.chat-input-area button {
    background: #2e7d32;
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-input-area button:hover { background: #1b5e20; }

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #718096;
}

.empty-state i {
    font-size: 3em;
    color: #cbd5e0;
    margin-bottom: 15px;
}
</style>

<!-- Chat Widget HTML -->
<div class="chat-widget" id="chatWidget">
    <!-- Floating Bubble -->
    <div class="chat-bubble" onclick="toggleChatPanel()" style="display: <?= $hide_bubble ? 'none' : 'flex' ?>; gap: 8px; padding: 0 20px; width: auto; border-radius: 30px;">
        <i class="fas fa-comments"></i>
        <span style="color: white; font-weight: 600; font-size: 0.9em; white-space: nowrap;">Marketplace Chat</span>
        <span class="chat-badge" id="chatBadge" style="display:none; position: static;">0</span>
    </div>
    
    <!-- Chat Panel -->
    <div class="chat-panel" id="chatPanel">
        <div class="chat-header">
            <button class="back-btn" id="chatBackBtn" onclick="showConversationList()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h4 id="chatTitle">Messages</h4>
            <button class="close-btn" onclick="toggleChatPanel()">&times;</button>
        </div>
        
        <div class="chat-body">
            <!-- Conversation List -->
            <div class="conv-list" id="convList">
                <div class="empty-state" id="emptyConv">
                    <i class="fas fa-inbox"></i>
                    <p>No messages yet.<br>Start a conversation from a product page!</p>
                </div>
            </div>
            
            <!-- Messages View -->
            <div class="messages-view" id="messagesView">
                <div class="messages-list" id="messagesList"></div>
            </div>
        </div>
        
        <!-- Input Area (hidden until in conversation) -->
        <div class="chat-input-area" id="chatInputArea" style="display:none;">
            <input type="text" id="chatInput" placeholder="Type a message..." autocomplete="off" onkeydown="if(event.key==='Enter')sendMessage()">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<!-- Chat Widget JavaScript -->
<script>
const CHAT_USER_ID = <?php echo $chat_user_id; ?>;
let currentConversationId = null;
let currentReceiverId = null;
let lastMessageId = 0;
let pollInterval = null;

// Guest Identity Management
let guestToken = localStorage.getItem('dajot_guest_token');
if (!guestToken && CHAT_USER_ID === 0) {
    guestToken = 'g_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
    localStorage.setItem('dajot_guest_token', guestToken);
}
let guestName = localStorage.getItem('dajot_guest_name');
if (!guestName && CHAT_USER_ID === 0) {
    guestName = 'Buyer_' + Math.random().toString(36).substr(2, 4).toUpperCase();
    localStorage.setItem('dajot_guest_name', guestName);
}
let currentListingId = null;

function focusChatInput() {
    setTimeout(() => {
        const input = document.getElementById('chatInput');
        if (input) {
            input.focus();
            input.click(); // Some browsers need a click
        }
    }, 100);
}

// Toggle panel
function toggleChatPanel() {
    // Guest access now allowed
    document.getElementById('chatPanel').classList.toggle('open');
    if (document.getElementById('chatPanel').classList.contains('open')) {
        loadConversations();
        startPolling();
        if (document.getElementById('messagesView').classList.contains('active')) {
            focusChatInput();
        }
    } else {
        stopPolling();
    }
}

// Load conversations
function loadConversations() {
    const url = `/dajotpoultrysupply/api/chat-conversations.php?guest_token=${guestToken || ''}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                renderConversations(data.conversations);
                updateBadge(data.total_unread);
            }
        })
        .catch(console.error);
}

function renderConversations(convs) {
    const list = document.getElementById('convList');
    const empty = document.getElementById('emptyConv');
    
    if (convs.length === 0) {
        empty.style.display = 'block';
        return;
    }
    
    empty.style.display = 'none';
    let html = '';
    
    convs.forEach(c => {
        const initial = c.other_user_name ? c.other_user_name.charAt(0).toUpperCase() : '?';
        const avatar = c.other_user_photo 
            ? `<img src="${c.other_user_photo}" alt="">` 
            : initial;
        
        html += `
            <div class="conv-item" onclick="openConversation(${c.id}, ${c.other_user_id}, '${c.other_user_name.replace(/'/g, "\\'")}')">
                <div class="conv-avatar">${avatar}</div>
                <div class="conv-info">
                    <div class="conv-name">${c.other_user_name}</div>
                    <div class="conv-preview">${c.product_name ? '📦 ' + c.product_name + ' - ' : ''}${c.last_message}</div>
                </div>
                <div class="conv-meta">
                    <div class="conv-time">${c.last_message_time}</div>
                    ${c.unread_count > 0 ? `<span class="conv-unread">${c.unread_count}</span>` : ''}
                </div>
            </div>
        `;
    });
    
    list.innerHTML = html + empty.outerHTML;
}

function updateBadge(count) {
    const badge = document.getElementById('chatBadge');
    if (count > 0) {
        badge.textContent = count > 9 ? '9+' : count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
    
    // Dispatch event for header sync
    document.dispatchEvent(new CustomEvent('chatBadgeUpdate', { detail: { count: count } }));
}

// Open conversation
function openConversation(convId, receiverId, receiverName) {
    currentConversationId = convId;
    currentReceiverId = receiverId;
    lastMessageId = 0;
    
    document.getElementById('convList').style.display = 'none';
    document.getElementById('messagesView').classList.add('active');
    document.getElementById('chatInputArea').style.display = 'flex';
    document.getElementById('chatBackBtn').style.display = 'block';
    document.getElementById('chatTitle').textContent = receiverName;
    
    loadMessages();
    startPolling();
}

function showConversationList() {
    stopPolling();
    currentConversationId = null;
    
    document.getElementById('convList').style.display = 'block';
    document.getElementById('messagesView').classList.remove('active');
    document.getElementById('chatInputArea').style.display = 'none';
    document.getElementById('chatBackBtn').style.display = 'none';
    document.getElementById('chatTitle').textContent = 'Messages';
    document.getElementById('messagesList').innerHTML = '';
    
    loadConversations();
}

// Load messages
function loadMessages() {
    if (!currentConversationId) return;
    const url = `/dajotpoultrysupply/api/chat-fetch.php?conversation_id=${currentConversationId}&last_id=${lastMessageId}&guest_token=${guestToken || ''}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.messages && data.messages.length > 0) {
                    appendMessages(data.messages);
                } else if (lastMessageId === 0 && !document.querySelector('.empty-state')) {
                    // Still no messages after loading
                    document.getElementById('messagesList').innerHTML = `
                        <div class="empty-state" style="padding:20px; text-align:center;">
                            <p>Start your conversation about<br><strong>this product</strong></p>
                        </div>
                    `;
                }
            }
        })
        .catch(console.error);
}

function appendMessages(messages) {
    if (!messages || messages.length === 0) return;
    
    const list = document.getElementById('messagesList');
    
    // Clear initial placeholders or empty states on first load
    if (lastMessageId === 0) {
        list.innerHTML = '';
    }
    
    messages.forEach(m => {
        if (m.id > lastMessageId) lastMessageId = m.id;
        
        const div = document.createElement('div');
        div.className = `message-bubble ${m.is_mine ? 'sent' : 'received'}`;
        div.innerHTML = `
            ${m.message}
            <div class="message-time">${m.created_at}</div>
        `;
        list.appendChild(div);
    });
    
    list.scrollTop = list.scrollHeight;
}

// Send message
function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;
    
    input.value = '';
    
    const formData = new FormData();
    formData.append('receiver_id', currentReceiverId || 0);
    formData.append('conversation_id', currentConversationId || 0);
    formData.append('message', message);
    formData.append('guest_token', guestToken || '');
    formData.append('guest_name', guestName || '');
    if (currentListingId) formData.append('listing_id', currentListingId);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    formData.append('csrf_token', csrfToken);
    
    fetch('/dajotpoultrysupply/api/chat-send.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            if (!currentConversationId) {
                currentConversationId = data.conversation_id;
            }
            loadMessages();
            focusChatInput();
        } else {
            alert("Error: " + data.message);
            focusChatInput();
        }
    })
    .catch(err => {
        console.error(err);
        alert("Failed to send message. Please check your connection.");
        focusChatInput();
    });
}

// Polling
function startPolling() {
    pollInterval = setInterval(loadMessages, 5000);
}

function stopPolling() {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

// Start a new conversation (called from product pages)
function startChat(receiverId, receiverName, listingId, productName) {
    // Access allowed for guests
    currentReceiverId = receiverId;
    currentListingId = listingId;
    
    // Open panel
    document.getElementById('chatPanel').classList.add('open');
    
    // Show message view
    document.getElementById('convList').style.display = 'none';
    document.getElementById('messagesView').classList.add('active');
    document.getElementById('chatInputArea').style.display = 'flex';
    document.getElementById('chatBackBtn').style.display = 'block';
    document.getElementById('chatTitle').textContent = receiverName;
    
    // Reset state for new chat
    lastMessageId = 0;
    currentConversationId = null;
    document.getElementById('messagesList').innerHTML = '<div class="empty-state" style="padding:20px; color:#666; text-align:center;">Loading conversation...</div>';
    
    // Auto-focus input
    focusChatInput();

    // Check for existing conversation or start fresh
    fetch(`/dajotpoultrysupply/api/chat-conversations.php?guest_token=${guestToken || ''}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const existing = data.conversations.find(c => c.other_user_id == receiverId);
                if (existing) {
                    currentConversationId = existing.id;
                    loadMessages();
                    startPolling();
                } else {
                    // New conversation - will be created on first message
                    currentConversationId = null;
                    document.getElementById('messagesList').innerHTML = `
                        <div class="empty-state" style="padding:20px;">
                            <p>Start your conversation about<br><strong>${productName || 'this product'}</strong></p>
                        </div>
                    `;
                }
            }
        });
}

// Initial load for badge count
document.addEventListener('DOMContentLoaded', function() {
    const url = `/dajotpoultrysupply/api/chat-conversations.php?guest_token=${guestToken || ''}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                updateBadge(data.total_unread);
            }
        })
        .catch(() => {});
});
</script>
