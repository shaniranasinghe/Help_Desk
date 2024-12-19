<?php
include_once '../../controller/UserController.php';
include_once '../common/AdminlogHeader.php';

// Check Admin Access
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$userController = new UserController($conn);

// Fetch user details by ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];
    $user = $userController->getUser($user_id);
} else {
    header("Location: manage_users.php");
    exit();
}

// Handle form submission for updating user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $company_id = $_POST['company_id'];

    $userController->editUser($user_id, $user_name, $email, $role, $company_id);
    echo "<script>alert('User updated successfully!'); window.location.href='manage_users.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../../../assets/CSS/manage_users.css">
</head>
<body>

<div class="edit-user-form-container">
    <h2>Edit User</h2>
    <form method="POST" class="edit-user-form">
        <label for="user_name">User Name:</label>
        <input type="text" name="user_name" id="user_name" value="<?php echo htmlspecialchars($user['user_name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        
        <label for="company_id">Company ID:</label>
        <input type="number" name="company_id" id="company_id" value="<?php echo htmlspecialchars($user['company_id']); ?>" required>

        <button type="submit" class="btn save-btn">Save Changes</button>
        <a href="manage_users.php" class="btn cancel-btn">Cancel</a>
    </form>
</div>

</body>
</html>
