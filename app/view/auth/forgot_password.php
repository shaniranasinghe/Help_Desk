<?php
session_start();
require '../../model/includes/config.php'; // Database configuration

// Initialize messages
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Generate a unique reset token
            $resetToken = bin2hex(random_bytes(32));
            $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store the token in the database
            $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $resetToken, $expiryTime, $user['id']);
            $updateStmt->execute();

            // Send the reset link via email
            $resetLink = "http://yourwebsite.com/reset_password.php?token=" . urlencode($resetToken);
            $subject = "Password Reset Request";
            $message = "Click the link below to reset your password:\n\n$resetLink\n\nThis link will expire in 1 hour.";
            $headers = "From: no-reply@yourwebsite.com";

            if (mail($email, $subject, $message, $headers)) {
                $success = 'Password reset link has been sent to your email.';
            } else {
                $error = 'Failed to send the email. Please try again later.';
            }
        } else {
            $error = 'No account found with this email.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../../../assets/CSS/login.css">
</head>

<body>
    <section>
        <div class="form-box">
            <div class="form-value">
                <form action="" method="POST">
                    <h2>Forgot Password</h2>

                    <?php if (!empty($error)): ?>
                    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
                    <?php endif; ?>

                    <div class="inputbox">
                        <input type="email" name="email" required>
                        <label for="">Email</label>
                    </div>
                    <button type="submit">Send Reset Link</button>
                    <div class="register">
                        <p>Remembered? <a href="login.php">Log in</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
