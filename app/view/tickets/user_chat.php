<?php
session_start();
require_once '../../model/includes/config.php';

// Check if user is logged in and is support team member
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get ticket ID from URL
$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;

// Fetch ticket details
$query = "SELECT * FROM tickets WHERE ticket_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Ticket #<?php echo $ticket_id; ?></title>
    <style>
    .chat-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chat-header {
        padding: 10px;
        background: #f5f5f5;
        border-radius: 8px 8px 0 0;
        margin-bottom: 20px;
    }

    .chat-messages {
        height: 400px;
        overflow-y: auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 4px;
    }

    .message {
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 8px;
        max-width: 70%;
    }

    .message.support {
        background: #d0a33b;
        color: white;
        margin-left: auto;
    }

    .message.client {
        background: #e9ecef;
        color: #333;
    }

    .message-time {
        font-size: 0.8em;
        color: #888;
        margin-top: 5px;
    }

    .chat-input {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    .chat-input textarea {
        flex-grow: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
        min-height: 60px;
    }

    .chat-input button {
        padding: 10px 20px;
        background: #d0a33b;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .chat-input button:hover {
        background: #0056b3;
    }

    .back-button {
        margin: 20px;
        padding: 10px 20px;
        background: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        display: inline-block;
    }
    </style>
</head>

<body>
    <a href="./view_tickets.php" class="back-button">← Back to Dashboard</a>

    <div class="chat-container">
        <div class="chat-header">
            <h2>Ticket #<?php echo $ticket_id; ?> - <?php echo htmlspecialchars($ticket['ticket_title']); ?></h2>
            <p>Status: <?php echo htmlspecialchars($ticket['ticket_status']); ?></p>
        </div>

        <div class="chat-messages" id="chatMessages">
            <!-- Messages will be loaded here -->
        </div>

        <div class="chat-input">
            <textarea id="messageInput" placeholder="Type your message..."></textarea>
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
    let lastMessageId = 0;

    function loadMessages() {
        fetch(`../../controller/get_messages.php?ticket_id=<?php echo $ticket_id; ?>&last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages.length > 0) {
                    const chatMessages = document.getElementById('chatMessages');
                    data.messages.forEach(message => {
                        if (message.id > lastMessageId) {
                            const messageDiv = document.createElement('div');
                            messageDiv.className = `message ${message.sender_type}`;
                            messageDiv.innerHTML = `
                                    ${message.message}
                                    <div class="message-time">${message.created_at}</div>
                                `;
                            chatMessages.appendChild(messageDiv);
                            lastMessageId = message.id;
                        }
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
    }

    function sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();

        if (message) {
            fetch('../../controller/send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ticket_id: <?php echo $ticket_id; ?>,
                        message: message
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        loadMessages();
                    } else {
                        alert('Failed to send message: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to send message. Please try again.');
                });
        }
    }

    // Load messages every 3 seconds
    setInterval(loadMessages, 3000);

    // Initial load
    loadMessages();

    // Allow sending message with Enter key
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function updateReadStatus() {
        fetch(`../../controller/update_read_status.php?ticket_id=<?php echo $ticket_id; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh notification count in header if needed
                    if (typeof checkNewMessages === 'function') {
                        checkNewMessages();
                    }
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function() {


        // Mark messages as read when chat is opened
        updateReadStatus();
    });
    </script>
</body>

</html>