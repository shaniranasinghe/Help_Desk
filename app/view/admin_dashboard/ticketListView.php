<?php
include_once '../common/AdminlogHeader.php';
require_once('../../controller/ReportController.php');

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$reportController = new ReportController();
$tickets = [];
$searchQuery = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filters = [
        'user_id' => $_POST['user_id'] ?? '',
        'priority' => $_POST['priority'] ?? 'all',
        'status' => $_POST['status'] ?? 'all',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'company' => $_POST['company'] ?? ''
    ];
    $tickets = $reportController->searchUserTickets($filters);
}

$companyQuery = "SELECT * FROM companies";
$companyResult = $conn->query($companyQuery);

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../../../assets/CSS/dashboard.css">
</head>
<body>

<div class="breadcrumb-container">
    <nav class="breadcrumb">
        <a href="#" class="breadcrumb-logo">
            <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
        </a>
        <a href="./dashboard.php" class="breadcrumb-link">Help Center</a>
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
            <li><a href="manage_companies.php">Companies</a></li>
            <li><a href="ticketListView.php">Summery Report</a></li>
            <li><a href="feedback.php">Feedbacks</a></li>
        </ul>
    </div>

    <main class="main-content">
        <h2>All User Tickets</h2>

        <section class="hero">
            <div class="container">
            <form id="search-form" method="POST" action="ticketListView.php">
                <input type="text" name="user_id" placeholder="Search by User ID..." value="<?php echo htmlspecialchars($_POST['user_id'] ?? ''); ?>">
                <select name="priority">
                    <option value="">Select Priority</option>
                    <option value="low" <?php echo isset($_POST['priority']) && $_POST['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo isset($_POST['priority']) && $_POST['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo isset($_POST['priority']) && $_POST['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                </select>
                <select name="status">
                    <option value="">Select Status</option>
                    <option value="open" <?php echo isset($_POST['status']) && $_POST['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="resolved" <?php echo isset($_POST['status']) && $_POST['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="pending" <?php echo isset($_POST['status']) && $_POST['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
                <label for="start_date">From:</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" placeholder="Start Date">
                <label for="end_date">To:</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>" placeholder="End Date">
                <select name="company">
                    <option value="">Select Company</option>
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
                <button type="submit">Search</button>
            </form>

            </div>
        </section>    

        <?php if (!empty($tickets)): ?>
            <form action="../../controller/utController.php" method="POST">
                <input type="hidden" name="report_type" value="user_tickets">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_POST['user_id'] ?? ''); ?>">
                <input type="hidden" name="priority" value="<?php echo htmlspecialchars($_POST['priority'] ?? 'all'); ?>">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($_POST['status'] ?? 'all'); ?>">
                <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                <input type="hidden" name="company" value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>">
                
                <div class="report-container">
                    <button class="generate-report-btn" name="download_report" type="submit">Download Report</button>
                </div>
            </form>




            <table class="ticket-table">
    <thead>
        <tr>
            <th>Ticket ID</th>
            <th>User ID</th> <!-- New Column -->
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Submitted By</th>
            <th>Current Company</th>
            <th>Transfer History</th>
            <th>Replies</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td>#<?php echo htmlspecialchars($ticket['ticket_id']); ?></td>
                <td><?php echo htmlspecialchars($ticket['user_id']); ?></td> <!-- Display User ID -->
                <td><?php echo htmlspecialchars($ticket['ticket_title']); ?></td>
                <td><?php echo htmlspecialchars($ticket['ticket_description']); ?></td>
                <td><?php echo htmlspecialchars($ticket['ticket_status']); ?></td>
                <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                <td><?php echo htmlspecialchars($ticket['submitted_by']); ?></td>
                <td><?php echo htmlspecialchars($ticket['current_company']); ?></td>
                <td>
                    <?php 
                        if (!empty($ticket['transfers'])): 
                            foreach ($ticket['transfers'] as $transfer): ?>
                                From: <?php echo htmlspecialchars($transfer['from_company_id']); ?><br>
                                To: <?php echo htmlspecialchars($transfer['to_company_id']); ?><br>
                                At: <?php echo htmlspecialchars($transfer['transferred_at']); ?><br>
                            <?php endforeach; 
                        else: ?>
                            Not Transferred
                    <?php endif; ?>
                </td>
                <td>
                    <?php 
                        if (!empty($ticket['replies'])): 
                            foreach ($ticket['replies'] as $reply): ?>
                                <?php echo htmlspecialchars($reply['ticket_reply']); ?><br>
                                <?php echo htmlspecialchars($reply['replied_at']); ?><br>
                            <?php endforeach; 
                        else: ?>
                            No replies
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        <?php endif; ?>
    </main>
</div>

<?php include_once '../common/footer.php'; ?>
</body>
</html>
