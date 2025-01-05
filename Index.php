<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Help Desk</title>
    <link rel="stylesheet" href="./assets/CSS/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.php"><img src="./assets/Images/logo.png" alt="Logo"></a>
                <h1><a href="index.php">Hellodesk.</a></h1>       
            </div>
            
            <div class="login-container">
                 <a href="./app/view/auth/login.php" class="btn secondary1">Login</a>
                <a href="./app/view/auth/signup.php" class="btn secondary1">Sign up</a>
            </div>
        </div>
    </header>


        <div class="breadcrumb-container">
            <nav class="breadcrumb">
                <a href="index.php" class="breadcrumb-logo">
                    <img src="./assets/Images/logo.png" alt="Help Desk Logo" class="logo">
                </a>
                <a href="./app/view/pages/home.php" class="breadcrumb-link active">Help Center</a>
            </nav>
        </div>
    

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>How can we assist you today?</h2>
            <form id="search-form" method="POST" action="./app/view/pages/search.php">
                <input type="text" name="query" placeholder="Search knowledgebase or tickets..." required>
                <button type="submit">Search</button>
            </form>
        </div>
    </section>

    <!-- Raise a New Ticket Section -->
    <section id="raise-ticket" class="ticket">
        <div class="container">
            <h2>Need Assistance?</h2>
            <p>If you're experiencing technical issues, raise a ticket and our support team will get back to you promptly.</p><br>
            <a href="./app/view/tickets/new_ticket.php" class="btn">Raise a New Ticket</a>
        </div>
    </section>

    <!-- Knowledgebase Section -->
    <section id="knowledgebase" class="section">
        <div class="container">
            <h2>Knowledgebase</h2>
            <div class="cards">
                <div class="card">
                    <h3>General FAQs</h3>
                    <p>Find answers to frequently asked questions related to IT support and services.</p>
                    <a href="./app/view/pages/general.php">View Articles</a>
                </div>
                <div class="card">
                    <h3>Software Help</h3>
                    <p>Explore troubleshooting guides and FAQs for the software tools provided by IT.</p>
                    <a href="./app/view/pages/software.php">View Articles</a>
                </div>
                <div class="card">
                    <h3>Hardware Support</h3>
                    <p>Get assistance with hardware-related issues such as device malfunctions and configurations.</p>
                    <a href="./app/view/pages/hardware.php">View Articles</a>
                </div>
            </div>
        </div>
    </section>

        <!-- Feedback Section -->
    <section id="feedback" class="section">
        <div class="container">
            <h2>We Value Your Feedback</h2>
            <p>Your opinions help us improve. Share your feedback with us!</p><br>
            <a href="./app/view/feedback/feedback.php" class="btn">Give Feedback</a>
        </div>
    </section>

    <footer class="footer">
            <p>&copy; 2024 </p>
    </footer>
</body>
</html>