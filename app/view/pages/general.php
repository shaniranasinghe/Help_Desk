<?php
include_once '../common/log_header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - IT Help Desk</title>
    <link rel="stylesheet" href="../../../assets/CSS/content.css">
</head>
<body>
    
    <main>
        <div class="breadcrumb-container">
            <nav class="breadcrumb">
                <a href="../pages/home.php" class="breadcrumb-logo">
                    <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
                </a>
                <a href="../pages/home.php" class="breadcrumb-link">Help Center</a>
                <span class="breadcrumb-separator">></span>
                <a href="../pages/about-us.php" class="breadcrumb-link active">General FAQs</a>
            </nav>
        </div>
        <section class="intro">
            <h2>Frequently Asked Questions</h2>
            <p>Find answers to common questions about IT support and services.</p>
        </section>
        <section class="resources">
            <article class="card">
                <h3>Account Management</h3>
                <p>Learn how to manage your account settings and preferences.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
            <article class="card">
                <h3>Service Requests</h3>
                <p>Understand the process of raising and managing support tickets.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
            <article class="card">
                <h3>General Support</h3>
                <p>Explore a variety of topics related to IT support services.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
        </section>
    </main>
    <?php include_once '../common/footer.php'; ?>
</body>
</html>
