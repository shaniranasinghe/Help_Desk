<?php
include_once '../common/AdminlogHeader.php';
include_once '../../controller/CompanyController.php';

// Check Admin Access
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$companyController = new CompanyController($conn);

if (isset($_GET['id'])) {
    $company_id = $_GET['id'];
    $company = $companyController->getCompany($company_id);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_company'])) {
        $company_name = trim($_POST['company_name']);
        $company_email = trim($_POST['company_email']);
        $company_type = $_POST['company_type'];
    
        if (!empty($company_name) && !empty($company_email) && !empty($company_type)) {
            $updated = $companyController->editCompany($company_id, $company_name, $company_email, $company_type);
            if ($updated) {
                echo "<script>alert('Company updated successfully!'); window.location.href='manage_companies.php';</script>";
            } else {
                echo "<script>alert('Error updating company. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('Please fill in all fields.');</script>";
        }
    }
    
} else {
    header("Location: manage_companies.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Company</title>
    <link rel="stylesheet" href="../../../assets/CSS/manage_companies.css">
</head>
<body>
    <div class="edit-company-container">
        <h2>Edit Company Details</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($company['company_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="company_email">Company Email:</label>
                <input type="email" name="company_email" id="company_email" value="<?php echo htmlspecialchars($company['company_email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Company Type</label>
                <select name="company_type" required>
                    <option value="Primary" <?php echo ($company['company_type'] === 'Primary') ? 'selected' : ''; ?>>Primary</option>
                    <option value="Sub" <?php echo ($company['company_type'] === 'Sub') ? 'selected' : ''; ?>>Sub</option>
                </select>
            </div>
            <button type="submit" name="edit_company" class="btn save-btn">Save Changes</button>
            <a href="manage_companies.php" class="btn cancel-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
