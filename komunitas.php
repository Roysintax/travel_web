<?php
/**
 * Group Chat - Tanah Suci Travel
 * File: chat.php
 * Fitur chat grup seperti WhatsApp
 */

require_once 'config/database.php';

$db = getDB();
$error = '';
$currentUser = null;

// Cek session nama
session_start();

// WAJIB LOGIN ULANG: Clear session chat jika bukan POST request (halaman baru dibuka)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Hapus session chat setiap kali halaman dibuka
    unset($_SESSION['chat_user_id'], $_SESSION['chat_username'], $_SESSION['chat_color']);
}


// Handle form input nama
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'join') {
        $username = trim($_POST['username'] ?? '');
        
        if (empty($username)) {
            $error = 'Nama tidak boleh kosong!';
        } elseif (strlen($username) < 2 || strlen($username) > 30) {
            $error = 'Nama harus 2-30 karakter!';
        } else {
            // Cek apakah nama sudah ada
            $stmt = $db->prepare("SELECT * FROM chat_users WHERE username = ?");
            $stmt->execute([$username]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                // User sudah ada, gunakan yang ada
                $_SESSION['chat_user_id'] = $existingUser['id'];
                $_SESSION['chat_username'] = $existingUser['username'];
                $_SESSION['chat_color'] = $existingUser['color'];
            } else {
                // Generate random color
                $colors = ['#D4AF37', '#22C55E', '#3B82F6', '#EF4444', '#A855F7', '#EC4899', '#F59E0B', '#14B8A6'];
                $color = $colors[array_rand($colors)];
                
                // Buat user baru
                $stmt = $db->prepare("INSERT INTO chat_users (username, color, is_online) VALUES (?, ?, 1)");
                $stmt->execute([$username, $color]);
                
                $_SESSION['chat_user_id'] = $db->lastInsertId();
                $_SESSION['chat_username'] = $username;
                $_SESSION['chat_color'] = $color;
            }
            
            // Update status online
            $stmt = $db->prepare("UPDATE chat_users SET is_online = 1 WHERE id = ?");
            $stmt->execute([$_SESSION['chat_user_id']]);
        }
    } elseif ($_POST['action'] === 'leave') {
        // Set offline dan hapus session
        if (isset($_SESSION['chat_user_id'])) {
            $stmt = $db->prepare("UPDATE chat_users SET is_online = 0 WHERE id = ?");
            $stmt->execute([$_SESSION['chat_user_id']]);
        }
        unset($_SESSION['chat_user_id'], $_SESSION['chat_username'], $_SESSION['chat_color']);
    }
}

// Cek apakah sudah login
$isLoggedIn = isset($_SESSION['chat_user_id']);
if ($isLoggedIn) {
    $currentUser = [
        'id' => $_SESSION['chat_user_id'],
        'username' => $_SESSION['chat_username'],
        'color' => $_SESSION['chat_color']
    ];
}

// Get site settings
$siteName = getSetting('site_name', 'TanahSuci');
$primaryColor = getSetting('primary_color', '#0F172A');
$accentColor = getSetting('accent_color', '#D4AF37');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?= htmlspecialchars($siteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: <?= htmlspecialchars($primaryColor) ?>;
            --accent: <?= htmlspecialchars($accentColor) ?>;
            --bg-dark: #0F172A;
            --bg-chat: #1E293B;
            --text-light: #F8FAFC;
            --text-muted: #94A3B8;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Login Form */
        .login-container {
            background: var(--bg-chat);
            border-radius: 1.5rem;
            padding: 3rem;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        
        .login-container h1 {
            color: var(--text-light);
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .login-container h1 span { color: var(--accent); }
        
        .login-container p {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 2px solid var(--bg-dark);
            background: var(--bg-dark);
            color: var(--text-light);
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .btn-primary {
            background: var(--accent);
            color: var(--primary);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
        }
        
        .error {
            background: rgba(239, 68, 68, 0.2);
            color: #EF4444;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .back-link:hover { color: var(--accent); }

        /* Chat Container */
        .chat-container {
            width: 100%;
            max-width: 600px;
            height: 100vh;
            max-height: 800px;
            display: flex;
            flex-direction: column;
            background: var(--bg-chat);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .chat-container {
                max-width: 100%;
                height: 100vh;
                max-height: none;
                border-radius: 0;
            }
        }
        
        /* Chat Header */
        .chat-header {
            background: var(--primary);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .chat-header-info h2 {
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .chat-header-info h2 span { color: var(--accent); }
        
        .chat-header-info p {
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        
        .online-count {
            color: #22C55E;
            font-size: 0.85rem;
        }
        
        .chat-header-actions {
            display: flex;
            gap: 1rem;
        }
        
        .header-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.1rem;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .header-btn:hover { color: var(--accent); }
        
        /* Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            background: linear-gradient(180deg, var(--bg-dark) 0%, var(--bg-chat) 100%);
        }
        
        .message {
            max-width: 80%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            position: relative;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.sent {
            align-self: flex-end;
            background: var(--accent);
            color: var(--primary);
            border-bottom-right-radius: 0.25rem;
        }
        
        .message.received {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border-bottom-left-radius: 0.25rem;
        }
        
        .message-sender {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .message-text {
            font-size: 0.95rem;
            line-height: 1.4;
            word-wrap: break-word;
        }
        
        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.25rem;
            text-align: right;
        }
        
        .system-message {
            align-self: center;
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
        }
        
        /* Input Area */
        .chat-input {
            padding: 1rem;
            background: var(--primary);
            display: flex;
            gap: 0.75rem;
            align-items: center;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .chat-input input {
            flex: 1;
            padding: 0.875rem 1.25rem;
            border-radius: 2rem;
            border: none;
            background: var(--bg-chat);
            color: var(--text-light);
            font-size: 0.95rem;
            font-family: inherit;
        }
        
        .chat-input input:focus { outline: none; }
        
        .chat-input input::placeholder { color: var(--text-muted); }
        
        .send-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: var(--accent);
            color: var(--primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .send-btn:hover {
            transform: scale(1.1);
        }
        
        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Scrollbar */
        .chat-messages::-webkit-scrollbar { width: 6px; }
        .chat-messages::-webkit-scrollbar-track { background: transparent; }
        .chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
        
        /* Users Panel */
        .users-panel {
            background: var(--bg-dark);
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .users-header {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .users-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .user-tag {
            background: rgba(255,255,255,0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .user-tag.me {
            background: var(--accent);
            color: var(--primary);
        }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
<!-- Login Form -->
<div class="login-container">
    <h1>Chat <span>Komunitas</span></h1>
    <p>Masukkan nama untuk bergabung ke chat grup</p>
    
    <?php if ($error): ?>
        <div class="error"><i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="action" value="join">
        <div class="form-group">
            <label>Nama Anda</label>
            <input type="text" name="username" placeholder="Masukkan nama..." required 
                   minlength="2" maxlength="30" autofocus>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk Chat
        </button>
    </form>
    
    <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
</div>

<?php else: ?>
<!-- Chat Interface -->
<div class="chat-container">
    <div class="chat-header">
        <div class="chat-header-info">
            <h2>Chat <span>Komunitas</span></h2>
            <p>Hai, <strong><?= htmlspecialchars($currentUser['username']) ?></strong> • <span class="online-count" id="onlineCount">0 online</span></p>
        </div>
        <div class="chat-header-actions">
            <button class="header-btn" id="toggleUsers" title="Lihat User Online">
                <i class="fa-solid fa-users"></i>
            </button>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="leave">
                <button type="submit" class="header-btn" title="Keluar">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
            <a href="index.php" class="header-btn" title="Kembali ke Beranda">
                <i class="fa-solid fa-home"></i>
            </a>
        </div>
    </div>
    
    <!-- Panel User Online -->
    <div class="users-panel" id="usersPanel" style="display: none;">
        <div class="users-header">
            <i class="fa-solid fa-circle" style="color: #22C55E; font-size: 0.6rem;"></i> User Online
        </div>
        <div class="users-list" id="usersList"></div>
    </div>
    
    <div class="chat-messages" id="chatMessages">
        <div class="system-message">Selamat datang di Chat Komunitas Tanah Suci!</div>
    </div>
    
    <div class="chat-input">
        <input type="text" id="messageInput" placeholder="Ketik pesan..." maxlength="500" autocomplete="off">
        <button class="send-btn" id="sendBtn" title="Kirim">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </div>
</div>

<script>
const currentUser = {
    id: <?= $currentUser['id'] ?>,
    username: '<?= addslashes($currentUser['username']) ?>',
    color: '<?= $currentUser['color'] ?>'
};

const chatMessages = document.getElementById('chatMessages');
const messageInput = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const onlineCount = document.getElementById('onlineCount');

let lastMessageId = 0;

// Send message
function sendMessage() {
    const message = messageInput.value.trim();
    if (!message) return;
    
    sendBtn.disabled = true;
    
    fetch('komunitas_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'send', message: message })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadMessages();
        }
        sendBtn.disabled = false;
    })
    .catch(() => { sendBtn.disabled = false; });
}

// Load messages
function loadMessages() {
    fetch(`komunitas_api.php?action=get&last_id=${lastMessageId}`)
    .then(res => res.json())
    .then(data => {
        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                addMessage(msg);
                lastMessageId = Math.max(lastMessageId, msg.id);
            });
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        if (data.online_count !== undefined) {
            onlineCount.textContent = data.online_count + ' online';
        }
        if (data.online_users) {
            updateUsersList(data.online_users);
        }
    });
}

// Add message to chat
function addMessage(msg) {
    const isSent = msg.user_id == currentUser.id;
    const div = document.createElement('div');
    div.className = 'message ' + (isSent ? 'sent' : 'received');
    
    const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    
    if (!isSent) {
        div.innerHTML = `
            <div class="message-sender" style="color: ${msg.color}">${msg.username}</div>
            <div class="message-text">${escapeHtml(msg.message)}</div>
            <div class="message-time">${time}</div>
        `;
    } else {
        div.innerHTML = `
            <div class="message-text">${escapeHtml(msg.message)}</div>
            <div class="message-time">${time}</div>
        `;
    }
    
    chatMessages.appendChild(div);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Event listeners
sendBtn.addEventListener('click', sendMessage);
messageInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') sendMessage();
});

// Toggle users panel
const toggleUsers = document.getElementById('toggleUsers');
const usersPanel = document.getElementById('usersPanel');
const usersList = document.getElementById('usersList');

toggleUsers.addEventListener('click', () => {
    usersPanel.style.display = usersPanel.style.display === 'none' ? 'block' : 'none';
});

// Update users list
function updateUsersList(users) {
    if (!users) return;
    usersList.innerHTML = users.map(u => 
        `<span class="user-tag ${u.id == currentUser.id ? 'me' : ''}">${escapeHtml(u.username)}</span>`
    ).join('');
}

// Load messages every 2 seconds
loadMessages();
setInterval(loadMessages, 2000);

// Update online status on page unload
window.addEventListener('beforeunload', () => {
    navigator.sendBeacon('komunitas_api.php?action=offline');
});
</script>
<?php endif; ?>

</body>
</html>
