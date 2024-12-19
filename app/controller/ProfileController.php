<?php
require '../../model/includes/config.php'; 
require_once '../../model/UserModel.php'; // Include the UserModel

class ProfileController {
    private $userModel;

    public function __construct($conn) {
        $this->userModel = new UserModel($conn);
    }

    // Display profile data or handle profile update
    public function editProfile($userId) {
        $error = '';
        $success = '';

        // Fetch user data from the database
        $user = $this->userModel->getUserById($userId);

        // If form is submitted to update profile
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get values from form
            
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $userName = trim($_POST['user_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);
            $city = trim($_POST['city']);
            $postalcode = trim($_POST['postalcode']);

            // Validate inputs
            if (empty($firstName) || empty($lastName) || empty($userName)  || empty($email) || empty($phone) || empty($address) || empty($city) || empty($postalcode)) {
                $error = 'All fields are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format.';
            } else {
                // Update profile in the database
                if ($this->userModel->updateUserProfile($userId, $firstName, $lastName, $userName,$email, $phone, $address, $city, $postalcode)) {
                    $success = 'Profile updated successfully.';
                    // Update session values if email or name changed
                    $_SESSION['user_name'] = $userName;
                    $_SESSION['email'] = $email;

                    // Fetch the updated user data and return it
                    $user = $this->userModel->getUserById($userId);

                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
        }

        // Return the result (user, error, success)
        return ['user' => $user, 'error' => $error, 'success' => $success];
    }

    public function deleteAccount($userId) {
        return $this->userModel->deleteUser($userId);
    }
    
}
?>
