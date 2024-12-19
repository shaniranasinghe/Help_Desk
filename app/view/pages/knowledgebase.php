<?php
include_once 'log_header.php';

?>

<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General FAQs | IT Help Desk</title>
    <link rel="stylesheet" href="CSS/knowledgebase.css">
</head>
<body>
    <div class="breadcrumb-container">
        <nav class="breadcrumb">
            <a href="./home.php" class="breadcrumb-logo">
                <img src="./Images/logo.png" alt="Help Desk Logo" class="logo">
            </a>
            <a href="./home.php" class="breadcrumb-link">Help Center</a>
            <span class="breadcrumb-separator">></span>
            <a href="./knowledgebase.php" class="breadcrumb-link active">Knowledgebase</a>
        </nav>
    </div>


  

    <div class="knowledgebase">
        <h1><span class="icon">💡</span> Knowledgebase</h1>
        <div class="categories">
            <div class="category">
                <div class="icon-folder"></div>
                <h2>General </h2>
                <ul>
                    <li><a href="./about-us.php">About Us</a></li>
                    <li><a href="#">What is Help Desk</a></li>                    
                    <li><a href="#">How to Raise a Ticket?</a></li>
                    <li><a href="#">How Can I Reset My Password?</a></li>
                    <li><a href="#">Rules and Regulations for users</a></li>
                </ul>
               
            </div>
            
        </div>
  </div>
  <script src="JS/kw.js"></script>

    <?php
             include_once '../common/footer.php';

    ?>
</body>
</html>
