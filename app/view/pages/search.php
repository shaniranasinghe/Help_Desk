<?php
include_once '../common/log_header.php';
include_once '../../model/includes/config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Get the search query from the form submission
if (isset($_POST['query'])) {
    $query = $_POST['query'];
    // Sanitize the input to prevent SQL injection
    $query = mysqli_real_escape_string($conn, $query);
} else {
    // If no query is entered, redirect to home page
    header("Location: home.php");
    exit;
}

// Corrected SQL query to search in users and tickets tables
$sql = "
    SELECT 'user' AS source, id, CONCAT(first_name, ' ', last_name) AS title, email AS content 
    FROM users 
    WHERE first_name LIKE '%$query%' OR last_name LIKE '%$query%' OR email LIKE '%$query%' OR user_name LIKE '%$query%'
    UNION
    SELECT 'ticket' AS source, ticket_id AS id, ticket_title AS title, ticket_description AS content 
    FROM tickets 
    WHERE ticket_title LIKE '%$query%' OR ticket_description LIKE '%$query%'
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="../../../assets/CSS/search.css"> <!-- Adjust path as needed -->
</head>
<body>

    <div class="breadcrumb-container">
            <nav class="breadcrumb">
                <a href="./home.php" class="breadcrumb-logo">
                    <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
                </a>
                <a href="./home.php" class="breadcrumb-link">Help Center</a>
                <span class="breadcrumb-separator">></span>
                <a href="./search.php" class="breadcrumb-link active">Search</a>
            </nav>
    </div>
<?php
// Check if there are results
if (mysqli_num_rows($result) > 0) {
    // Display results
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='search-result'>";
        echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
        echo "<p>" . htmlspecialchars(substr($row['content'], 0, 150)) . "...</p>";
        // Check the source to determine the link
        if ($row['source'] == 'user') {
            echo "<a href='#?id=" . $row['id'] . "'>Read More</a>";
        } elseif ($row['source'] == 'ticket') {
            echo "<a href='../tickets/view_tickets.php?id=" . $row['id'] . "'>Read More</a>";
        }
        echo "</div>";
    }
} else {
    // Display "No results" message if no results found
    echo "<div class='no-results-message'>No results found for '$query'.</div>";
}
?>
<!-- Add footer -->
<?php include_once '../common/footer.php'; ?>
</body>
</html>