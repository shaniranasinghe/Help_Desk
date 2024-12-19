<?php
include_once '../../controller/UserController.php';
include_once '../common/AdminlogHeader.php';

// Check Admin Access
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$userController = new UserController($conn);

// Handle user deletion
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];
    if ($userController->deleteUser($user_id)) {
        echo "<script>alert('User deleted successfully!'); window.location.href='manage_users.php';</script>";
    } else {
        echo "<script>alert('Failed to delete user.'); window.location.href='manage_users.php';</script>";
    }
} else {
    header("Location: manage_users.php");
    exit();
}
?>
