<?php
session_start();
include('../../model/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Help Desk</title>
    <link rel="stylesheet" href="../../../assets/CSS/logheader.css">
    <script src="JS/logheader.js"></script>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="../pages/home.php"><img src="../../../assets/Images/logo.png" alt="Logo"></a>
                <h1><a href="../pages/home.php">Hellodesk.</a></h1>
            </div>
            <!-- <nav>
                <ul class="nav-links">
                    <li><a href="home.php" class="btn">Home</a></li>
                    <li><a href="home.php" class="btn">Knowledgebase</a></li>
                    <li><a href="home.php" class="btn">Raise a Ticket</a></li>
                    <li><a href="home.php" class="btn">Contact Support</a></li>
                </ul>
            </nav> -->



            <!-- My Tickets, Logout buttons, and Profile Picture -->
            <div class="login-container">

                <div class="message-notification">
                    <a href="#" id="messageIcon" onclick="toggleMessages(event)">
                        <img id="notify" src="../../../assets/images/notifi.png" alt="notifications" />
                        <span id="messageCount" class="notification-badge"></span>
                    </a>
                    <div id="messageDropdown" class="message-dropdown">
                        <!-- Messages will be loaded here -->
                    </div>
                </div>

                <a href="../tickets/view_tickets.php" class="btn secondary">My Tickets</a>
                <a href="../auth/logout.php" class="btn-logout">Logout</a>

                <!-- Profile Picture Icon -->
                <a href="../pages/profile.php" class="profile-icon">
                    <img src="../../../assets/Images/profile.png" alt="Profile" title="View Profile">
                </a>
            </div>

        </div>
    </header>

    <script>
        function checkNewMessages() {
            fetch('../../controller/check_messages.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('messageCount');
                    if (data.count > 0) {
                        badge.style.display = 'block';
                        badge.textContent = data.count;
                    } else {
                        badge.style.display = 'none';
                    }
                });
        }

        function loadMessages() {
            fetch('../../controller/get_user_messages.php')
                .then(response => response.json())
                .then(data => {
                    const dropdown = document.getElementById('messageDropdown');
                    dropdown.innerHTML = data.messages.map(message => `
                <div class="message-item ${message.is_read ? '' : 'unread'}" 
                     onclick="openChat(${message.ticket_id})">
                    <div class="message-title">Ticket #${message.ticket_id}: ${message.ticket_title}</div>
                    <div class="message-preview">${message.sender_name}: ${message.message}</div>
                    <div class="message-time">${message.created_at}</div>
                </div>
            `).join('');
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
            window.location.href = '../tickets/user_chat.php?ticket_id=' + ticketId;
        }

        // Check for new messages every 30 seconds
        setInterval(checkNewMessages, 30000);

        // Initial check
        checkNewMessages();

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