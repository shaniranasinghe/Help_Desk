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
                <a href="../tickets/view_tickets.php" class="btn secondary">My Tickets</a>
                <a href="../auth/logout.php" class="btn-logout">Logout</a>

                <!-- Profile Picture Icon -->
                <a href="../pages/profile.php" class="profile-icon">
                    <img src="../../../assets/Images/profile.png" alt="Profile" title="View Profile">
                </a>
            </div>

        </div>
    </header>
</body>
</html>