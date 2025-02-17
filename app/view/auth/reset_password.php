<?php
require '../../model/includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token']);
    $newPassword = trim($_POST['password']);

    if (empty($newPassword)) {
        $error = 'Please enter a new password.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Validate the token
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password and clear the token
            $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $user['id']);
            $updateStmt->execute();

            $success = 'Password has been reset successfully. You can now log in.';
        } else {
            $error = 'Invalid or expired reset token.';
        }
    }
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    header('Location: forgot_password.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../../../assets/CSS/login.css">
</head>

<body>
    <section>
        <div class="form-box">
            <div class="form-value">
                <form action="" method="POST">
                    <h2>Reset Password</h2>

                    <?php if (!empty($error)): ?>
                    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
                    <?php endif; ?>

                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="inputbox">
                        <input type="password" name="password" required>
                        <label for="">New Password</label>
                    </div>
                    <button type="submit">Reset Password</button>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
