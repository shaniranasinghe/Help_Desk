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

// Get the feedback ID from URL
$feedbackId = $_GET['id'] ?? null;

if (!$feedbackId) {
    die("Feedback ID is missing");
}

// Fetch the feedback from the database
$query = "SELECT * FROM feedback WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $feedbackId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Feedback not found");
}

$feedback = $result->fetch_assoc();

// Handle form submission to update feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $responseTime = $_POST['response_time'];
    $resolutionQuality = $_POST['resolution_quality'];
    $communication = $_POST['communication'];
    $overallRating = $_POST['overall_rating'];
    $comments = $_POST['comments'];

    // Update the feedback in the database
    $updateQuery = "UPDATE feedback SET response_time = ?, resolution_quality = ?, communication = ?, overall_rating = ?, comments = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssssi", $responseTime, $resolutionQuality, $communication, $overallRating, $comments, $feedbackId);
    $updateStmt->execute();

    // Redirect back to the feedback list
    header("Location: feedback.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Feedback</title>
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
            <a href="#" class="breadcrumb-link active">Edit Feedback</a>
        </nav>
    </div>

    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <!-- Sidebar links here -->
        </div>

        <div class="edit-company-container">
            <h2>Edit Feedback</h2>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="response_time">Response Time:</label>
                    <input type="text" id="response_time" name="response_time" value="<?php echo htmlspecialchars($feedback['response_time']); ?>" required><br>
                </div>
                
                <div class="form-group">
                    <label for="resolution_quality">Resolution Quality:</label>
                    <input type="text" id="resolution_quality" name="resolution_quality" value="<?php echo htmlspecialchars($feedback['resolution_quality']); ?>" required><br>
                </div> 

                <div class="form-group">
                    <label for="communication">Communication:</label>
                    <input type="text" id="communication" name="communication" value="<?php echo htmlspecialchars($feedback['communication']); ?>" required><br>
                </div>

                <div class="form-group">
                    <label for="overall_rating">Overall Rating:</label>
                    <input type="text" id="overall_rating" name="overall_rating" value="<?php echo htmlspecialchars($feedback['overall_rating']); ?>" required><br>
                </div>

                <div class="form-group">
                    <label for="comments">Comments:</label>
                    <textarea id="comments" name="comments" rows="4" required><?php echo htmlspecialchars($feedback['comments']); ?></textarea><br>
                </div> 
                
                
                <div class="button-container">
                    <button type="submit" name="edit_company" class="btn save-btn">Save Changes</button>
                    <a href="feedback.php" class="btn cancel-btn">Cancel</a>
                </div>    
            </form>
        </div>
    </div>

    <?php include_once '../common/footer.php'; ?>
</body>

</html>
