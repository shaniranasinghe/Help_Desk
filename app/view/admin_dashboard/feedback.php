<?php
include_once '../common/AdminlogHeader.php';

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Database connection
require_once('../../controller/TicketController.php');
$ticketController = new TicketController($conn);


// Initialize variables
$searchQuery = '';
$feedbacks = [];

// Check if search query is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query'])) {
    $searchQuery = $conn->real_escape_string($_POST['query']);
    $query = "SELECT id, response_time, resolution_quality, communication, overall_rating, comments, created_at 
              FROM feedback 
              WHERE id LIKE '%$searchQuery%'";
} else {
    // Fetch all feedbacks if no search query
    $query = "SELECT id, response_time, resolution_quality, communication, overall_rating, comments, created_at FROM feedback";
}

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$feedbacks = $result->fetch_all(MYSQLI_ASSOC);

// Fetch feedbacks for each ticket
$ticketFeedbacks = [];
$ticketQuery = "SELECT ticket_id, user_id, feedback FROM tickets";
$ticketResult = $conn->query($ticketQuery);

if ($ticketResult) {
    $ticketFeedbacks = $ticketResult->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedbacks</title>
    <link rel="stylesheet" href="../../../assets/CSS/feedbacks.css">
</head>

<body>

    <div class="breadcrumb-container">
        <nav class="breadcrumb">
            <a href="#" class="breadcrumb-logo">
                <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
            </a>
            <a href="#" class="breadcrumb-link">Help Center</a>
            <span class="breadcrumb-separator">></span>
            <a href="#" class="breadcrumb-link active">Dashboard</a>
        </nav>
    </div>

    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Tickets</a></li>
                <li><a href="manage_users.php">Users</a></li>
                <li><a href="manage_companies.php">Companies</a></li>
                <li><a href="ticketListView.php">Summary Report</a></li>
                <li><a href="support_dashboard.php">Ticket Dashboard</a></li>
                <li><a href="feedback.php" class="active">Feedbacks</a></li>
            </ul>
        </div>

        <main class="main-content">
            <h2>Feedbacks Overview</h2>

            <section class="hero">
                <div class="container">
                    <form id="search-form" method="POST" action="feedback.php">
                        <input type="text" name="query" placeholder="Search by feedback ID..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                        <button type="submit">Search</button>
                    </form>
                </div>
            </section>

            <h3>All Feedbacks</h3><br>
            <?php if (!empty($feedbacks)): ?>
                <table class="feedback-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Response Time</th>
                            <th>Resolution Quality</th>
                            <th>Communication</th>
                            <th>Overall Rating</th>
                            <th>Comments</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['response_time']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['resolution_quality']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['communication']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['overall_rating']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['comments']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                                <td class="actions">
                                    <div class="btn-container">
                                        <a href="edit_feedback.php?id=<?php echo $feedback['id']; ?>" class="btn btn-edit">Edit</a>
                                        <a href="delete_feedback.php?id=<?php echo $feedback['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</a>
                                    </div>
                                    </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No feedbacks found.</p>
            <?php endif; ?>

            <br><br><br><br><h3>Feedbacks for Resolved Tickets</h3><br>
            <?php if (!empty($ticketFeedbacks)): ?>
                <table class="feedback-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>User ID</th>
                            <th>Feedback</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ticketFeedbacks as $ticketFeedback): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticketFeedback['ticket_id']); ?></td>
                                <td><?php echo htmlspecialchars($ticketFeedback['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($ticketFeedback['feedback']); ?></td>
                                <td class="actions">
                                    <div class="btn-container">
                                    <a href="edit_feedback1.php?id=<?php echo htmlspecialchars($ticketFeedback['ticket_id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn edit-btn">Edit</a>
                                    <a href="delete_feedback1.php?id=<?php echo htmlspecialchars($ticketFeedback['ticket_id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</a>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No feedbacks for tickets found.</p>
            <?php endif; ?>
        </main>
    </div>

    <?php include_once '../common/footer.php'; ?>
</body>

</html>
