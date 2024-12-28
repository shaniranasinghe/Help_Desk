<?php
session_start();
require '../model/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Optional support member
$supportMember = isset($_POST['support_member']) ? $_POST['support_member'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $ticket_title = $conn->real_escape_string(trim($_POST['ticket_title']));
    $ticket_description = $conn->real_escape_string(trim($_POST['ticket_description']));
    $issue_type = $conn->real_escape_string($_POST['issue_type']);
    $company_id = $conn->real_escape_string($_POST['company_id']);
    $priority = $conn->real_escape_string($_POST['priority']);  // Capture priority field

    // Handle file upload
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['attachment'];

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($file['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            die('Invalid file type. Only JPG, PNG and GIF are allowed.');
        }

        // Validate file size (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            die('File is too large. Maximum size is 5MB.');
        }

        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/tickets/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $attachment_path = 'uploads/tickets/' . $unique_filename;
        } else {
            die('Failed to upload file.');
        }
    }

    // Prepare SQL query to include the priority
    $query = "INSERT INTO tickets (ticket_title, ticket_description, user_id, issue_type, company_id, priority, assigned_to, attachment_path) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("SQL preparation error: " . $conn->error);
    }

    // Bind parameters, including the priority
    $stmt->bind_param("ssisssss", $ticket_title, $ticket_description, $user_id, $issue_type, $company_id, $priority, $supportMember, $attachment_path);

    // Execute query
    if ($stmt->execute()) {
        echo "Ticket raised successfully!";
        header("Location: ../view/tickets/ticket_confirmation.php");
        exit();
    } else {
        echo "Execution error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
