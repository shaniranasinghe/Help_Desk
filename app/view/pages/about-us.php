<?php
include_once '../common/log_header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - IT Help Desk</title>
    <link rel="stylesheet" href="CSS/about-us.css">
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
                <a href="../pages/knowledgebase.php" class="breadcrumb-link">Knowledgebase</a>
                <span class="breadcrumb-separator">></span>
                <a href="../pages/about-us.php" class="breadcrumb-link active">About Us</a>
            </nav>
        </div>


        <section class="about-section">
            <div class="container-wrapper">
                <div class="container1">
                    <h2>Who We Are</h2>
                    <p>
                        We are a leading IT Help Desk service provider, dedicated to delivering top-tier technical support and innovative solutions to enhance user experience. Our help desk operates with a commitment to excellence, adhering to globally recognized standards and practices in the IT industry. <br>

                        Established with a vision to empower users with reliable and efficient assistance, our IT Help Desk supports a diverse user base, including students, faculty, and professionals. We take pride in resolving thousands of queries annually, ensuring seamless access to technical resources and services. Our team of skilled professionals is equipped to handle a wide range of IT-related issues, from troubleshooting software to guiding users through complex systems.<br>

                        Through continuous improvement and a focus on user satisfaction, we aim to foster a supportive environment where technology serves as an enabler for learning, productivity, and innovation. We are committed to being the trusted partner for all your IT needs.
                    </p>
                </div>
            </div>    
        </section>

        
    </main>
    <?php
             include_once '../common/footer.php';

    ?>
</body>
</html>
