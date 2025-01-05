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
                <a href="../pages/about-us.php" class="breadcrumb-link active">Hardware help</a>
            </nav>
        </div>

        <section class="intro">
            <h2>Get Help with Hardware Issues</h2>
            <p>Discover tips and solutions for fixing hardware-related problems quickly.</p>
        </section>
        <section class="resources">
            <article class="card">
                <h3>Device Configuration</h3>
                <p>Learn how to set up and configure your hardware devices.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
            <article class="card">
                <h3>Repair Guidelines</h3>
                <p>Access troubleshooting guides for malfunctioning devices.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
            <article class="card">
                <h3>Compatibility Issues</h3>
                <p>Resolve issues between your hardware and software systems.</p>
                <a href="#" class="btn">Learn More</a>
            </article>
        </section>
    </main>
    <?php include_once '../common/footer.php'; ?>
</body>
</html>
