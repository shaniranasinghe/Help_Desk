<?php
include_once '../common/AdminlogHeader.php';
include_once '../../controller/CompanyController.php';

// Check Admin Access
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $companyController = new CompanyController($conn);
    $company_id = $_GET['id'];

    $deleted = $companyController->deleteCompany($company_id);

    if ($deleted) {
        echo "<script>alert('Company deleted successfully!'); window.location.href='manage_companies.php';</script>";
    } else {
        echo "<script>alert('Error deleting company.'); window.location.href='manage_companies.php';</script>";
    }
} else {
    header("Location: manage_companies.php");
    exit();
}
?>
