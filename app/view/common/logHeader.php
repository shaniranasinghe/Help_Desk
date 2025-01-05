<?php

session_start();
include('../../model/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$UserID = $_SESSION["user_id"];
$fname = $_SESSION["user_name"];
$acctype =  $_SESSION["Acc_type"];

if ($acctype != "Admin" && $acctype != "Support") {

    echo "<script> alert ('You are not an Admin') 
        location.href='../login.php';
    </script> ";
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="stylesheet" href="../../../assets/CSS/logheaderAdmin.css">

</head>
<style>
.message-notification {
    position: relative;
    display: inline-block;
    margin-right: 20px;
}

.notification-badge {
    position: absolute;
    top: 4px;
    right: 10px;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    display: none;
    min-width: 15px;
    text-align: center;
    font-weight: bold;
}

/* Message Dropdown */
.message-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    width: 350px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
}

/* Message Items */
.message-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s;
}

.message-item:last-child {
    border-bottom: none;
}

.message-item:hover {
    background-color: #f8f9fa;
}

.message-item.unread {
    background-color: #e3f2fd;
}

.message-item.unread:hover {
    background-color: #bbdefb;
}

/* Message Content */
.message-title {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
    font-size: 14px;
}

.message-preview {
    color: #666;
    font-size: 13px;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-time {
    color: #999;
    font-size: 12px;
}

/* Empty and Error States */
.no-messages,
.error-message {
    padding: 20px;
    text-align: center;
    color: #666;
    font-style: italic;
}

.error-message {
    color: #dc3545;
}

/* Scrollbar Styling */
.message-dropdown::-webkit-scrollbar {
    width: 6px;
}

.message-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.message-dropdown::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.message-dropdown::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-dropdown {
    animation: fadeIn 0.2s ease-out;
}

/* Responsive Design */
@media (max-width: 480px) {
    .message-dropdown {
        width: 300px;
        right: -100px;
    }

    .message-title {
        font-size: 13px;
    }

    .message-preview {
        font-size: 12px;
    }
}
</style>

<body>

    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="../../../assets/Images/logo.png" alt="logo" />
            </div>
            <h1>Welcome</h1>
            <nav class="header-nav">
                <div class="message-notification">
                    <a href="#" id="messageIcon" onclick="toggleMessages(event)">
                        <img id="notify" src="../../../assets/images/notifi.png" alt="notifications" />
                        <span id="messageCount" class="notification-badge">0</span>
                    </a>
                    <div id="messageDropdown" class="message-dropdown">
                        <!-- Messages will be loaded here -->
                    </div>
                </div>
                <a href="../pages/support_profile.php" class="profile-icon">
                    <img src="../../../assets/Images/profile.png" alt="profile" />
                </a>
                <a href="../auth/logout.php"><button type="button" id="lout">Logout</button></a>
            </nav>
        </div>
    </header>

    <script>
    function checkNewMessages() {
        fetch('../../controller/check_messages_support.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('messageCount');
                if (data.success) {
                    if (data.count > 0) {
                        badge.style.display = 'block';
                        badge.textContent = data.count;
                        console.log('Unread messages:', data.count);
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error checking messages:', error);
            });
    }

    function loadMessages() {
        fetch('../../controller/get_support_member_message.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const dropdown = document.getElementById('messageDropdown');
                    if (data.messages.length > 0) {
                        dropdown.innerHTML = data.messages.map(message => `
                            <div class="message-item ${message.is_read ? '' : 'unread'}" 
                                 onclick="openChat(${message.ticket_id})">
                                <div class="message-title">Ticket #${message.ticket_id}: ${message.ticket_title}</div>
                                <div class="message-preview">${message.sender_name}: ${message.message}</div>
                                <div class="message-time">${message.created_at}</div>
                            </div>
                        `).join('');
                    } else {
                        dropdown.innerHTML = '<div class="no-messages">No messages</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                document.getElementById('messageDropdown').innerHTML =
                    '<div class="error-message">Failed to load messages</div>';
            });
    }

    function toggleMessages(event) {
        event.preventDefault();
        const dropdown = document.getElementById('messageDropdown');
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
            loadMessages();
        } else {
            dropdown.style.display = 'none';
        }
    }

    function openChat(ticketId) {
        <?php if ($acctype == "Support"): ?>
        window.location.href = '../supportTeam_dashboard/chat.php?ticket_id=' + ticketId;
        <?php else: ?>
        window.location.href = '../tickets/user_chat.php?ticket_id=' + ticketId;
        <?php endif; ?>
    }

    // Check messages more frequently (every 10 seconds)
    setInterval(checkNewMessages, 10000);

    // Initial check when page loads
    document.addEventListener('DOMContentLoaded', function() {
        checkNewMessages();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('messageDropdown');
        const messageIcon = document.getElementById('messageIcon');
        if (!messageIcon.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
    </script>

</body>

</html>