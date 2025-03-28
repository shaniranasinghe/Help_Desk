<?php
require '../../model/includes/config.php'; // Database configuration

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $conn->real_escape_string(trim($_POST['first_name']));
    $lastName = $conn->real_escape_string(trim($_POST['last_name']));
    $userName = $conn->real_escape_string(trim($_POST['user_name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['c_password']);
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $city = $conn->real_escape_string(trim($_POST['city']));
    $postalCode = $conn->real_escape_string(trim($_POST['postalcode']));
    $company_id = $conn->real_escape_string($_POST["company_id"]);

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($userName) || empty($email) || empty($password) || empty($confirmPassword) || empty($phone) || empty($address) || empty($city) || empty($postalCode)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error = 'Password must be at least 8 characters long, contain uppercase, lowercase, numbers, and special symbols.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $error = 'Invalid phone number format.';
    } else {
        // Check if the username already exists
        $userNameCheckQuery = "SELECT * FROM users WHERE user_name = ?";
        if ($stmt = $conn->prepare($userNameCheckQuery)) {
            $stmt->bind_param("s", $userName);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = 'Username is already registered.';
            }
            $stmt->close();
        } else {
            $error = 'Error preparing query. Please try again.';
        }

        // If no errors, proceed to check email and insert data
        if (!$error) {
            $emailCheckQuery = "SELECT * FROM users WHERE email = ?";
            if ($stmt = $conn->prepare($emailCheckQuery)) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = 'Email is already registered.';
                } else {
                    // Generate verification token
                    $verificationToken = bin2hex(random_bytes(32));
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    // Insert user with verification token
                    $insertQuery = "INSERT INTO users (first_name, last_name, user_name, email, password, phone, address, city, postalcode, company_id, verify_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    if ($stmt = $conn->prepare($insertQuery)) {
                        $stmt->bind_param("sssssssssss", $firstName, $lastName, $userName, $email, $hashedPassword, $phone, $address, $city, $postalCode, $company_id, $verificationToken);

                        if ($stmt->execute()) {
                            // Send verification email using PHPMailer
                            $mail = new PHPMailer(true);

                            try {
                                // Server settings
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = 'ithelpd228@gmail.com';
                                $mail->Password   = 'vfyz zclo mxyn culp';
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port       = 587;

                                // Recipients
                                $mail->setFrom('ithelpd228@gmail.com', 'IT Help Desk');
                                $mail->addAddress($email, $firstName . ' ' . $lastName);

                                // Content
                                $verificationLink = "http://localhost/HELP_DESK/app/view/auth/verify.php?token=" . $verificationToken;

                                $mail->isHTML(true);
                                $mail->Subject = 'Verify Your Email Address';
                                $mail->Body    = "
                                    <h2>Welcome to IT Help Desk!</h2>
                                    <p>Hi $firstName,</p>
                                    <p>Thank you for registering. Please click the link below to verify your email address:</p>
                                    <p><a href='$verificationLink'>Verify Email Address</a></p>
                                    <p>If you can't click the link, copy and paste this URL into your browser:</p>
                                    <p>$verificationLink</p>
                                ";

                                $mail->send();
                                $success = 'Registration successful! Please check your email to verify your account.';
                            } catch (Exception $e) {
                                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                            }
                        } else {
                            $error = 'Registration failed. Please try again.';
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}

// Fetch companies for the dropdown
$companyQuery = "SELECT * FROM companies";
$companyResult = $conn->query($companyQuery);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - IT Support Desk</title>
    <link rel="stylesheet" href="../../../assets/CSS/signup.css">
    <style>
        .required::after {
            content: " *";
            color: red;
        }
    </style>
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


    <section>
        <div class="form-box">
            <h2>Create Your Account</h2><br>

            <!-- Display error or success messages -->
            <?php if ($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php endif; ?>

            <form action="" method="POST" id="signup-form">
                <div class="input-group">
                    <div class="inputbox">
                        <input type="text" id="first_name" name="first_name" required>
                        <label for=" first_name" class="required">First Name</label>
                    </div>

                    <div class="inputbox">
                        <input type="text" id="last_name" name="last_name" required>
                        <label for="last_name" class="required">Last Name</label>
                    </div>
                </div>

                <div class="inputbox">
                    <input type="text" id="user_name" name="user_name" required>
                    <label for="user_name" class="required">User Name</label>
                </div>
                <div class="inputbox">
                    <input type="email" id="email" name="email" required>
                    <label for="email" class="required">Email Address</label>
                </div>

                <div class="inputbox">
                    <input type="text" id="phone" name="phone" required>
                    <label for="phone" class="required">Phone Number</label>
                </div>

                <div class="inputbox">
                    <input type="text" id="address" name="address">
                    <label for="address">Address</label>
                </div>

                <div class="inputbox">
                    <input type="text" id="city" name="city">
                    <label for="city">City</label>
                </div>

                <div class="inputbox">
                    <input type="text" id="postalcode" name="postalcode">
                    <label for="postalcode">Postal Code</label>
                </div>


                <div class="inputbox">
                <label for="company_id" class="required">Company</label>
                <select id="company_id" name="company_id">

                        <option value="" disabled selected>Select a company</option>
                        <?php 
                        if ($companyResult && $companyResult->num_rows > 0) {
                            while ($row = $companyResult->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['company_id']) . '">' . htmlspecialchars($row['company_name']) . '</option>';
                            }
                        } else {
                            echo '<option value="" disabled>No companies available</option>';
                        }
                        ?>
                    </select>
                </div>



                <div class="inputbox">
                    <input type="password" id="password" name="password" required>
                    <label for="password" class="required">Password</label>


                </div>
                <div class="inputbox">
                    <input type="password" id="confirm-password" name="c_password" required>
                    <label for="confirm-password" class="required">Confirm Password</label>

                </div>

                <button type="submit">Signup</button>
            </form>
            <div class="login">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php
    include_once '../common/footer.php';
    ?>
    <script>
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            // Password validation regex
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!passwordRegex.test(password)) {
                e.preventDefault();
                alert('Password must contain:\n' +
                    '- At least 8 characters\n' +
                    '- At least one uppercase letter\n' +
                    '- At least one lowercase letter\n' +
                    '- At least one number\n' +
                    '- At least one special character (@$!%*?&)');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
        });
    </script>

</body>

</html>