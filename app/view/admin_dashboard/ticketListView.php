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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['query'])) {
    $searchQuery = trim($_POST['query']);
    if (!empty($searchQuery)) {
        $tickets = $reportController->searchUserTickets($searchQuery);
    }
}
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
            <li><a href="manage_companies.php">Companies</a></li>
            <li><a href="ticketListView.php">Summery Report</a></li>
        </ul>
    </div>

    <main class="main-content">
        <h2>All Tickets Overview</h2>

        <form id="search-form" method="POST" action="ticketListView.php">
            <input type="text" name="query" placeholder="Search by User ID..." value="<?php echo htmlspecialchars($searchQuery); ?>" required>
            <button type="submit">Search</button>
        </form>

        <?php if (!empty($tickets)): ?>
            <form action="../../controller/ReportController.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" name="generateUserReport" class="generate-report-btn">Generate Report</button>
            </form>

            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Submitted By</th>
                        <th>Current Company</th>                        
                        <th>Transfer History</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($ticket['ticket_id']); ?></td>
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
