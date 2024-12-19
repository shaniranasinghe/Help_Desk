<?php
require '../../model/includes/config.php';

// Fetch all feedback
$sql = "SELECT response_time, resolution_quality, communication, overall_rating, comments, created_at 
        FROM feedback ORDER BY created_at DESC";
$result = $conn->query($sql);

$feedbacks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}

$conn->close();
?>

