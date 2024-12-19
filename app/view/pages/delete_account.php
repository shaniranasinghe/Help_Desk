<?php
session_start();
require_once '../../controller/ProfileController.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Initialize ProfileController
$profileController = new ProfileController($conn);

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($profileController->deleteAccount($userId)) {
        // Destroy session and redirect to login
        session_destroy();
        header("Location: ../auth/login.php?message=account_deleted");
        exit;
    } else {
        $error = "Failed to delete account. Please try again.";
    }
}
?>

