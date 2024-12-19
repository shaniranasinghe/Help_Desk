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
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $userController->deleteUser($user_id);
    echo "<script>alert('User deleted successfully!'); window.location.href='manage_users.php';</script>";
}

// Handle account type update
if (isset($_POST['update_acc_type']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $new_acc_type = $_POST['Acc_type'];
    
    // Check if the user being updated is not an admin
    $user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();
    if ($user['Acc_type'] !== 'Admin') {
        $conn->query("UPDATE users SET Acc_type='$new_acc_type' WHERE id='$user_id'");
        echo "<script>alert('Account type updated successfully!'); window.location.href='manage_users.php';</script>";
    } else {
        echo "<script>alert('You cannot change the admin account type!'); window.location.href='manage_users.php';</script>";
    }
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY date_created DESC");

// Handle search query
$searchQuery = '';
$highlightId = -1;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['query'])) {
    $searchQuery = trim($_POST['query']);
    if (is_numeric($searchQuery)) {
        $highlightId = (int)$searchQuery;

        // Execute the search query
        $users = $conn->query("SELECT * FROM users WHERE id='$highlightId' ORDER BY date_created DESC");
    } else {
        echo "<script>alert('Please enter a valid numeric User ID!');</script>";
        $users = $conn->query("SELECT * FROM users ORDER BY date_created DESC");
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../../../assets/CSS/manage_users.css">
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
    <!-- Sidebar Section -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Tickets</a></li>
            <li><a href="manage_users.php" class="active">Users</a></li>
            <li><a href="manage_companies.php">Companies</a></li>
            <li><a href="ticketListView.php">Summery Report</a></li>
        </ul>
    </div>

    <main class="main-content">
        <h2>All Users</h2>

        <section class="hero">
            <div class="container">
                <form id="search-form" method="POST" action="manage_users.php">
                    <input type="text" name="query" placeholder="Search by User ID..." value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                    <button type="submit">Search</button>
                </form>
            </div>
        </section>

        <!-- Generate Report Button -->
        <form action="../../controller/GenerateReportController.php" method="POST">
            <input type="hidden" name="report_type" value="users">
            <div class="report-container">
                <button class="generate-report-btn">Generate Report</button>
            </div>
        </form> 

        <?php if ($users->num_rows > 0): ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Company ID</th>
                        <th>Acc_Type</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <tr class="<?php echo ($row['id'] == $highlightId) ? 'highlight-row' : ''; ?>">
                            <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['company_id']); ?></td>
                            <td>
                                <?php if ($row['Acc_type'] === 'Admin'): ?>
                                    <span>Admin (Cannot Change)</span>
                                <?php else: ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <select name="Acc_type" onchange="this.form.submit()">
                                            <option value="User" <?php if ($row['Acc_type'] == 'User') echo 'selected'; ?>>User</option>
                                            <option value="Support" <?php if ($row['Acc_type'] == 'Support') echo 'selected'; ?>>Support</option>                                        
                                        </select>
                                        <input type="hidden" name="update_acc_type" value="1">
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['date_created']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn edit-btn">Edit</a>
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>

    </main>    
</div>

<?php include_once '../common/footer.php'; ?>

</body>
</html>
