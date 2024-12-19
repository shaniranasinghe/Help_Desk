<?php
include_once '../common/log_header.php';
require_once '../../controller/ProfileController.php'; // Include ProfileController

// Initialize the ProfileController
$profileController = new ProfileController($conn);

// Fetch the user ID from session (ensure user is logged in)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); // Redirect to login if not logged in
    exit;
}

$userId = $_SESSION['user_id'];

// Call the controller method to handle the profile edit
$result = $profileController->editProfile($userId);

// Extract values from the result
$user = $result['user'];
$error = $result['error'];
$success = $result['success'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - IT Support Desk</title>
    <link rel="stylesheet" href="../../../assets/CSS/profile.css">
</head>
<body>

    <div class="breadcrumb-container">
        <nav class="breadcrumb">
            <a href="./home.php" class="breadcrumb-logo">
                <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
            </a>
            <a href="./home.php" class="breadcrumb-link">Help Center</a>
            <span class="breadcrumb-separator">></span>
            <a href="./profile.php" class="breadcrumb-link active">Profile</a>
        </nav>
    </div>
    
    <!-- Profile Section -->
    <section class="profile-section">
        <div class="profile-container">
            <div class="profile-header">
                <img src="../../../assets/Images/icon.png" alt="User Avatar" class="profile-avatar">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
            </div>
            <div class="profile-details">
            <div class="details-box">
                <h3>My Details</h3>
                <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['first_name']); ?> <?php echo htmlspecialchars($user['last_name']); ?></p>
                <p><strong>User Name:</strong> <?php echo htmlspecialchars($user['user_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p> 
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
                <p><strong>Postal Code:</strong> <?php echo htmlspecialchars($user['postalcode']); ?></p>
            </div>


                <div class="edit-details-box">
                    <h3>Edit Details</h3>
                    <form action="profile.php" method="POST">
                        <div class="inputbox">
                            <input type="text" name="user_name" value="<?php echo htmlspecialchars($user['user_name']); ?>" required>
                            <label for="user_name">User Name</label>
                        </div>
                        <div class="inputbox">
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            <label for="first_name">First Name</label>
                        </div>
                        <div class="inputbox">
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            <label for="last_name">Last Name</label>
                        </div>
                        <div class="inputbox">
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <label for="email">Email</label>
                        </div>

                        <div class="inputbox">
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            <label for="phone">Phone</label>
                        </div>
                        <div class="inputbox">
                            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                            <label for="address">Address</label>
                        </div>
                        <div class="inputbox">
                            <input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                            <label for="city">City</label>
                        </div>
                        <div class="inputbox">
                            <input type="text" name="postalcode" value="<?php echo htmlspecialchars($user['postalcode']); ?>" required>
                            <label for="postalcode">Postal Code</label>
                        </div>
                        <button type="submit" class="btn-update-profile">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="delete-account">
                <h3>Delete Account</h3>
                <p>Are you sure you want to delete your account?</p>
                <button class="btn-delete-account" id="openPopup">Delete Account</button>
            </div>

            <!-- Delete Account Popup -->
            <section class="delete-account-section" id="popup" style="display: none;">
                <div class="popup-overlay">
                    <div class="popup-content">
                        <h2>Delete Account</h2>
                        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                        <form action="./delete_account.php" method="POST">
                            <input type="submit" class="btn-close" value="Delete">
                        </form>
                        <button class="btn-close" id="cancelPopup">Cancel</button>
                    </div>
                </div>
            </section>

            <!-- JavaScript to Handle Popup -->
            <script>
            document.getElementById("openPopup").addEventListener("click", function() {
                document.getElementById("popup").style.display = "block";
            });

            document.getElementById("cancelPopup").addEventListener("click", function() {
                document.getElementById("popup").style.display = "none";
            });
            </script>

        </div>
    </section>

    <?php include_once '../common/footer.php'; ?>

</body>
</html>
