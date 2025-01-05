<?php
include_once '../common/AdminlogHeader.php';

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Initialize the controller with the database connection
require_once('../../controller/TicketController.php');
$ticketController = new TicketController($conn);

// Fetch all tickets
$tickets = $ticketController->getAllTickets();

// Handle status change actions
if (isset($_GET['action']) && isset($_GET['ticketId'])) {
    $ticketId = $_GET['ticketId'];
    $action = $_GET['action'];

    if ($action === 'resolved') {
        $ticketController->changeTicketStatus($ticketId, 'resolved');
    } elseif ($action === 'open') {
        $ticketController->changeTicketStatus($ticketId, 'open');
    } elseif ($action === 'pending') {
        $ticketController->changeTicketStatus($ticketId, 'pending');
    }

    header('Location: dashboard.php');
    exit();
}

// Handle search query
$searchQuery = '';
$highlightId = -1;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['query'])) {
    $searchQuery = trim($_POST['query']);

    if (is_numeric($searchQuery)) {
        $highlightId = (int)$searchQuery;

        $result = $conn->query("
        SELECT t.ticket_id, t.ticket_title, t.ticket_description, t.ticket_status, 
            t.priority, u.user_name AS submitted_by, c.company_name AS current_company,
            tt.from_company_id, tt.to_company_id, tt.transferred_at
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        JOIN companies c ON t.company_id = c.company_id
        LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
        WHERE t.ticket_id = '$highlightId'
        ORDER BY t.ticket_id DESC
    ");




        if (!$result) {
            die("Query failed: " . $conn->error);
        }
        $tickets = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "<script>alert('Please enter a valid numeric Ticket ID!');</script>";
        $tickets = $ticketController->getAllTickets();
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
                <li><a href="feedback.php">Feedbacks</a></li>
            </ul>
        </div>

        <main class="main-content">
            <h2>All Tickets Overview</h2>

            <section class="hero">
                <div class="container">
                    <form id="search-form" method="POST" action="dashboard.php">
                        <input type="text" name="query" placeholder="Search by Ticket ID..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                        <button type="submit">Search</button>
                    </form>
                </div>
            </section>

            <form action="../../controller/tController.php" method="POST">
                <input type="hidden" name="report_type" value="tickets">
                <div class="report-container">
                    <button class="generate-report-btn">Generate Report</button>
                </div>
            </form>

            <?php if (!empty($tickets)): ?>
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
                        <td>
                            <?php if ($ticket['ticket_status'] === 'open'): ?>
                            <button class="modal-button open"
                                onclick="openModal('open', <?php echo $ticket['ticket_id']; ?>)">Open</button>
                                <?php elseif ($ticket['ticket_status'] === 'pending'): ?>
                                <button class="modal-button pending"
                                onclick="openModal('pending', <?php echo $ticket['ticket_id']; ?>)">Pending</button>
                            <?php else: ?>    
                            <button class="modal-button resolved"
                                onclick="openModal('resolved', <?php echo $ticket['ticket_id']; ?>)">Resolved</button>
                            <?php endif; ?>
                            
                        </td>
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
            <?php else: ?>
            <p>No tickets found.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal for status change -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <h3>Change Ticket Status</h3>
            <button class="modal-button open" id="openBtn">Open</button>
            <button class="modal-button pending" id="pendingBtn">Pending</button>
            <button class="modal-button resolved" id="resolvedBtn">Resolved</button>
            <button class="modal-button cancel" onclick="closeModal()">Cancel</button>
        </div>
    </div>

    <script>

    function openModal(status, ticketId) {
        document.getElementById('statusModal').style.display = 'flex';
        document.getElementById('openBtn').onclick = function() {
            changeStatus(ticketId, 'open');
        };
        document.getElementById('pendingBtn').onclick = function() { 
            changeStatus(ticketId, 'pending');
        };
        document.getElementById('resolvedBtn').onclick = function() {
            changeStatus(ticketId, 'resolved');
        };
    }


    function closeModal() {
        document.getElementById('statusModal').style.display = 'none';
    }

    function changeStatus(ticketId, status) {
        window.location.href = `dashboard.php?action=${status}&ticketId=${ticketId}`;
        closeModal();
    }
    </script>

    <?php include_once '../common/footer.php'; ?>
</body>

</html>