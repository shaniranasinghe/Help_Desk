<?php
class UserModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUserById($user_id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function deleteUser($user_id) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    public function updateUser($user_id, $user_name, $email, $Acc_type, $company_id) {
        $query = "UPDATE users SET user_name = ?, email = ?, Acc_type = ?, company_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssii", $user_name, $email, $Acc_type, $company_id, $user_id);
        return $stmt->execute();
    }

    public function getRoleById($user_id) {
        $query = "SELECT role FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['role'];
    }

    // Update user profile
    public function updateUserProfile($userId, $firstName, $lastName, $userName, $email, $phone, $address, $city, $postalcode) {
        // Update user profile in the database
        $stmt = $this->conn->prepare("UPDATE users SET first_name = ?, last_name = ?, user_name = ?, email = ?, phone = ?, address = ?, city = ?, postalcode = ? WHERE id = ?");
        $stmt->bind_param("sssssssii", $firstName, $lastName, $userName, $email, $phone, $address, $city, $postalcode, $userId);
        return $stmt->execute();
    }
}
?>
