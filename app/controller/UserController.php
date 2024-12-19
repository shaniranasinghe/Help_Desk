<?php
include_once '../../model/UserModel.php';

class UserController {
    private $userModel;

    public function __construct($conn) {
        $this->userModel = new UserModel($conn);
    }

    public function editUser($user_id, $user_name, $email, $role, $company_id) {
        return $this->userModel->updateUser($user_id, $user_name, $email, $role, $company_id);
    }

    public function deleteUser($user_id) {
        return $this->userModel->deleteUser($user_id);
    }

    public function getUser($user_id) {
        return $this->userModel->getUserById($user_id);
    }

    public function getRole($user_id) {
        return $this->userModel->getRoleById($user_id);
    }
}
?>
