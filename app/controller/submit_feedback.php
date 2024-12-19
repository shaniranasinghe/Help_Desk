<?php
include('../model/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response_time = $_POST['response_time'];
    $resolution_quality = $_POST['resolution_quality'];
    $communication = $_POST['communication'];
    $overall_rating = $_POST['overall_rating'];
    $comments = $conn->real_escape_string($_POST['comments']);

    $sql = "INSERT INTO feedback (response_time, resolution_quality, communication, overall_rating, comments) 
            VALUES ('$response_time', '$resolution_quality', '$communication', '$overall_rating', '$comments')";

    if ($conn->query($sql) === TRUE) {
        header('Location: ../view/feedback/feedback.php?success=1');
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
