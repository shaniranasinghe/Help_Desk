<?php
include_once '../common/AdminlogHeader.php';

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Database connection
require_once('../../controller/TicketController.php');

if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$feedbackId = isset($_GET['id']) ? intval($_GET['id']) : null;
$feedback = "";

// Fetch existing feedback details
if ($feedbackId) {
    $query = "SELECT feedback FROM tickets WHERE ticket_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $feedbackId);
    $stmt->execute();
    $stmt->bind_result($feedback);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission for editing feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedFeedback = $_POST['feedback'];

    $updateQuery = "UPDATE tickets SET feedback = ? WHERE ticket_id = ?";
    $stmt = $conn->prepare($updateQuery);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("si", $updatedFeedback, $feedbackId);
    if ($stmt->execute()) {
        header("Location: feedback.php?message=Feedback updated successfully");
        exit();
    } else {
        die("Failed to update feedback: " . $stmt->error);
    }
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
    <div class="edit-company-container">
        <h2>Edit Feedback</h2>
        <form method="POST">
            <div class="form-group">
                <label for="feedback">Feedback:</label>
                <textarea id="feedback" name="feedback" required><?php echo htmlspecialchars($feedback); ?></textarea>
            </div>

            <div class="button-container">
                <button type="submit" name="edit_company" class="btn save-btn">Save Changes</button>
                <a href="feedback.php" class="btn cancel-btn">Cancel</a>
            </div> 
        </form>
    </div>
    <?php include_once '../common/footer.php'; ?>
</body>
</html>
