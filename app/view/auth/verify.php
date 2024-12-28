<?php
require '../../model/includes/config.php';

if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);
    
    // Check if token exists and is valid
    $query = "SELECT id FROM users WHERE verify_token = ? AND is_verified = 0";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Update user to verified status
            $updateQuery = "UPDATE users SET is_verified = 1, verify_token = NULL WHERE verify_token = ?";
            if ($updateStmt = $conn->prepare($updateQuery)) {
                $updateStmt->bind_param("s", $token);
                if ($updateStmt->execute()) {
                    $message = "Your email has been verified successfully! You can now login.";
                    header("Location: login.php?verified=1");
                    exit();
                } else {
                    $error = "Verification failed. Please try again.";
                }
                $updateStmt->close();
            }
        } else {
            $error = "Invalid or expired verification token.";
        }
        $stmt->close();
    }
} else {
    $error = "No verification token provided.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../../../assets/CSS/signup.css">
</head>

<body>
    <div class="container">
        <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($message)): ?>
        <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        <p>Return to <a href="login.php">login page</a></p>
    </div>
</body>

</html>