<?php
include_once '../common/AdminlogHeader.php';
include_once '../../controller/CompanyController.php';

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$companyController = new CompanyController($conn);
$companies = $companyController->getAllCompanies();

// Handle Add Company request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_company'])) {
    $company_name = trim($_POST['company_name']);
    $company_email = trim($_POST['company_email']);
    $company_type = $_POST['company_type'];

    if (!empty($company_name) && !empty($company_email) && !empty($company_type)) {
        $success = $companyController->addCompany($company_name, $company_email, $company_type);
        if ($success) {
            echo "<script>alert('Company added successfully!'); window.location.href='manage_companies.php';</script>";
        } else {
            echo "<script>alert('Error adding company. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}

$active_companies = "active";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies</title>
    <link rel="stylesheet" href="../../../assets/CSS/manage_companies.css">
</head>
<body>

<div class="breadcrumb-container">
    <nav class="breadcrumb">
        <a href="#" class="breadcrumb-logo">
            <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
        </a>
        <a href="#" class="breadcrumb-link">Help Center</a>
        <span class="breadcrumb-separator">></span>
        <a href="#" class="breadcrumb-link active">Dashboard</a>
    </nav>
</div>

<div class="dashboard-container">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Tickets</a></li>
            <li><a href="manage_users.php">Users</a></li>
            <li><a href="manage_companies.php" class="<?php echo $active_companies; ?>">Companies</a></li>
            <li><a href="ticketListView.php">Summery Report</a></li>
        </ul>
    </div>

    <main class="main-content">
        <h2>Manage Companies</h2>

        <form action="../../controller/cController.php" method="POST">
            <input type="hidden" name="report_type" value="companies">
            <div class="report-container">
                <button class="generate-report-btn">Generate Report</button>
            </div>
        </form>

        <form method="POST" action="" class="add-company-form">
            <h3>Add New Company</h3>

            <div class="form-row">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" required>
            </div>

            <div class="form-row">
                <label for="company_email">Company Email</label>
                <input type="email" id="company_email" name="company_email" required>
            </div>

            <div class="form-row">
                <label for="company_type">Company Type</label>
                <select id="company_type" name="company_type" required>
                    <option value="" disabled selected>Select Type</option>
                    <option value="Primary">Primary</option>
                    <option value="Sub">Sub</option>
                </select>
            </div>

            <button type="submit" name="add_company" class="btn add-btn">Add Company</button>
        </form>

        <?php if ($companies->num_rows > 0): ?>
            <div class="table-wrapper">
                <table class="companies-table">
                    <thead>
                        <tr>
                            <th>Company ID</th>
                            <th>Company Name</th>
                            <th>Company Email</th>
                            <th>Company Type</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $companies->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($row['company_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['company_email']); ?></td>
                                <td><?php echo htmlspecialchars($row['company_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_created']); ?></td>
                                <td>
                                    <a href="edit_company.php?id=<?php echo $row['company_id']; ?>" class="btn edit-btn">Edit</a>
                                    <a href="delete_company.php?id=<?php echo $row['company_id']; ?>" class="btn delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this company?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No companies found.</p>
        <?php endif; ?>

        
    </main>
</div>

<?php include_once '../common/footer.php'; ?>
</body>
</html>
