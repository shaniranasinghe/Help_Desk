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
                <a href="../pages/about-us.php" class="breadcrumb-link active">Software help</a>
            </nav>
        </div>
        <section class="intro">
            <h2>Explore Solutions for Software Issues</h2>
            <p>Find detailed guides and FAQs to troubleshoot your software-related challenges.</p>
        </section>
        <section class="resources">
            <article class="card">
                <h3>Installation Guides</h3>
                <p>Step-by-step instructions to install essential software tools.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
            <article class="card">
                <h3>Update Issues</h3>
                <p>Resolve problems arising during software updates.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
            <article class="card">
                <h3>Common Errors</h3>
                <p>Fix frequent software error messages effortlessly.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
        </section>
    </main>
    <?php include_once '../common/footer.php'; ?>
</body>
</html>
