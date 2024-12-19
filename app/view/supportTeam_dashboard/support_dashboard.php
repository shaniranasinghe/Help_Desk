<?php
include_once '../common/logHeader.php';

// Restrict access to support team members
if (!isset($_SESSION['user_id']) || $_SESSION['Acc_type'] !== 'Support') {
    header("Location: ../auth/login.php");
    exit();
}

// Initialize the controller with the database connection
require_once('../../controller/TicketController.php');
$ticketController = new TicketController($conn);

// Get the company ID from the session
$company_id = $_SESSION['company_id'];

// Fetch tickets sorted by priority for the dashboard
$tickets = $ticketController->getTicketsSortedByPriority($company_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Dashboard</title>
    <link rel="stylesheet" href="../../../assets/CSS/support_dashboard.css">
</head>
<body>

<!-- Breadcrumb Navigation -->
<div class="breadcrumb-container">
    <nav class="breadcrumb">
        <a href="#" class="breadcrumb-logo">
            <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
        </a>
        <a href="#" class="breadcrumb-link">Help Center</a>
        <span class="breadcrumb-separator">></span>
        <a href="#" class="breadcrumb-link active">Support Dashboard</a>
    </nav>
</div>

<div class="container">
    <h1>Support Dashboard</h1>

    <?php if ($tickets->num_rows > 0): ?>
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $tickets->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ticket_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['ticket_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['ticket_description']); ?></td>
                        <td>
                            <span class="status <?php echo strtolower($row['ticket_status']); ?>">
                                <?php echo htmlspecialchars($row['ticket_status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['priority']); ?></td> <!-- Display Priority -->
                        <td>
                            <!-- Resolve Button -->
                            <a href="#"
                            class="btn resolve <?php echo ($row['ticket_status'] !== 'open') ? 'disabled' : ''; ?>"
                            <?php if ($row['ticket_status'] === 'open'): ?>
                                onclick="openResolveModal(<?php echo $row['ticket_id']; ?>)"
                            <?php else: ?>
                                onclick="return false;"
                            <?php endif; ?>>
                            Resolve
                            </a>

                            <!-- Transfer Button -->
                            <button class="btn transfer <?php echo ($row['ticket_status'] !== 'open') ? 'disabled' : ''; ?>" 
                                <?php if ($row['ticket_status'] === 'open'): ?>
                                    onclick="openTransferModal(<?php echo $row['ticket_id']; ?>)"
                                <?php else: ?>
                                    onclick="return false;"
                                <?php endif; ?>>
                                Transfer
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-tickets">No tickets assigned yet.</p>
    <?php endif; ?>
</div>

<!-- Resolve Ticket Modal -->
<div id="resolveModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeResolveModal()">&times;</span>
        <h2>Resolve Ticket</h2>
        <form action="resolve_ticket.php" method="POST">
            <input type="hidden" id="resolve_ticket_id" name="ticket_id">
            <label for="ticket_reply">Reply:</label>
            <textarea name="ticket_reply" required></textarea>
            <button type="submit">Submit Resolution</button>
        </form>
    </div>
</div>

<!-- Transfer Ticket Modal -->
<div id="transferModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeTransferModal()">&times;</span>
        <h2>Transfer Ticket</h2>
        <form action="transfer_ticket.php" method="POST">
            <input type="hidden" id="ticket_id_input" name="ticket_id">
            <label for="to_company_id">Select Company to Transfer:</label>
            <select name="to_company_id" required>
                <option value="">--Select Company--</option>
                <?php
                // Fetch companies from the database
                $companyQuery = "SELECT company_id, company_name FROM companies";
                $result = mysqli_query($conn, $companyQuery);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($company = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . htmlspecialchars($company['company_id']) . "'>" 
                            . htmlspecialchars($company['company_name']) . 
                            "</option>";
                    }
                }
                ?>
            </select>
            <button type="submit" name="transfer_ticket">Transfer</button>
        </form>
    </div>
</div>


<!-- JavaScript for Modals -->
<script>
function openResolveModal(ticketId) {
    document.getElementById("resolveModal").style.display = "block";
    document.getElementById("resolve_ticket_id").value = ticketId;
}

function closeResolveModal() {
    document.getElementById("resolveModal").style.display = "none";
}

function openTransferModal(ticketId) {
    document.getElementById("transferModal").style.display = "block";
    document.getElementById("ticket_id_input").value = ticketId;
}

function closeTransferModal() {
    document.getElementById("transferModal").style.display = "none";
}
</script>

<?php include_once '../common/footer.php'; ?>

</body>
</html>
