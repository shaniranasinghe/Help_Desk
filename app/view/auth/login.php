<?php
session_start();
require '../../model/includes/config.php'; // Database configuration

// Initialize error message
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input fields
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Query to fetch the user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['Acc_type'] = $user['Acc_type'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['address'] = $user['address'];
                $_SESSION['city'] = $user['city'];
                $_SESSION['postalcode'] = $user['postalcode'];
                $_SESSION['is_verified'] = $user['is_verified'];


                if ($_SESSION['is_verified'] === 0) {
                    $error = "Your email is not verified. Please check your email for the verification link.";
                    session_destroy(); // Clear the session
                    ?>
<script>
alert("Your email is not verified. Please check your email for the verification link.");
window.location.href = 'login.php';
</script>
<?php
                    exit;
                }

                // Store company_id only for support members
                if ($user['Acc_type'] === 'Support') {
                    $_SESSION['company_id'] = $user['company_id'];
                }

                // Redirect based on user type (Admin, Support, or User)
                if ($user['Acc_type'] === 'Admin') {
                    header("Location: ../admin_dashboard/dashboard.php");
                    exit;
                } elseif ($user['Acc_type'] === 'Support') {
                    header("Location: ../supportTeam_dashboard/support_dashboard.php");
                    exit;
                } else {
                    header("Location: ../pages/profile.php");
                    exit;
                }
            } else {
                $error = 'Incorrect password.';
            }
        } else {
            $error = 'No account found with this email.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../../../assets/CSS/login.css">
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="../../../index.php"><img src="../../../assets/Images/logo.png" alt="Logo"></a>
                <h1><a href="../../../index.php">Hellodesk.</a></h1>
            </div>

            <div class="login-container">
                <a href="./login.php" class="btn secondary1">Login</a>
                <a href="./signup.php" class="btn secondary1">Sign up</a>
            </div>
        </div>
    </header>




    <!-- Login Form Section -->
    <section>
        <div class="form-box">
            <div class="form-value">
                <form action="" method="POST">
                    <h2>Login</h2>

                    <!-- Display error or success messages -->
                    <?php if (!empty($error)): ?>
                    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
                    <?php endif; ?>

                    <div class="inputbox">
                        <input type="email" name="email" required
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        <label for="">Email</label>
                    </div>
                    <div class="inputbox">
                        <input type="password" name="password" required>
                        <label for="">Password</label>
                    </div>
                    <div class="forget">
                        <label><a href="#">Forgot password?</a></label>
                    </div>
                    <button type="submit">Log in</button>
                    <div class="register">
                        <p>Don't have an account? <a href="signup.php">Register here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php include_once '../common/footer.php'; ?>
</body>

</html>