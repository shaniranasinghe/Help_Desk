<?php
include_once '../common/log_header.php';
require '../../controller/TicketController.php';

// Assuming $conn is your database connection
$ticketController = new TicketController($conn); // Initialize TicketController with the DB connection

$userId = $_SESSION['user_id'];
$tickets = $ticketController->getTicketsWithReplies($userId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets</title>
    <link rel="stylesheet" href="../../../assets/CSS/view_tickets.css">

    <script>
    // Function to toggle the visibility of the ticket details
    function showDetails(ticketId) {
        // Get the ticket details section for the specific ticket
        var ticketDetails = document.getElementById("ticket-details-" + ticketId);

        // Toggle the visibility of the section
        if (ticketDetails.style.display === "none" || ticketDetails.style.display === "") {
            ticketDetails.style.display = "block";
        } else {
            ticketDetails.style.display = "none";
        }
    }
    </script>

</head>

<body>
    <div class="breadcrumb-container">
        <nav class="breadcrumb">
            <a href="../pages/home.php" class="breadcrumb-logo">
                <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
            </a>
            <a href="../pages/home.php" class="breadcrumb-link">Help Center</a>
            <span class="breadcrumb-separator">></span>
            <a href="#" class="breadcrumb-link active">My Tickets</a>
        </nav>
    </div>

    <div class="container1">
        <div class="new">
            <h1>My Tickets</h1>
            <a href="new_ticket.php" class="btn primary">Raise a New Ticket</a>
        </div>

        <!-- Ticket Display Section -->
        <?php if (count($tickets) > 0): ?>
        <div class="ticket-grid">
            <?php foreach ($tickets as $ticket): ?>
            <div
                class="ticket-card <?php echo $ticket['ticket_status'] === 'open' ? 'open-ticket' : ($ticket['ticket_status'] === 'resolved' ? 'resolved-ticket' : ''); ?>">
                <div class="ticket-header">
                    <h2><?php echo htmlspecialchars($ticket['ticket_title']); ?></h2>
                </div>
                <div class="ticket-body">
                    <p class="status">Status: <?php echo htmlspecialchars($ticket['ticket_status']); ?></p>
                    <div id="ticket-details-<?php echo $ticket['ticket_id']; ?>" style="display: none;">
                        <h3>More Details:</h3>
                        <p>Full Description: <?php echo htmlspecialchars($ticket['ticket_description']); ?></p>
                        <p>Submitted on: <?php echo htmlspecialchars($ticket['created_at']); ?></p>





                        <!-- Add this section for displaying the attachment -->
                        <?php if (!empty($ticket['attachment_path'])): ?>
                        <div class="ticket-attachment">
                            <h4>Attachment:</h4>
                            <img src="../../<?php echo htmlspecialchars($ticket['attachment_path']); ?>"
                                alt="Ticket Attachment" class="ticket-image" onclick="openImageModal(this.src)">
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($ticket['replies'])): ?>
                        <div class="replies">
                            <h3>Replies:</h3>
                            <?php foreach ($ticket['replies'] as $reply): ?>
                            <div class="reply">
                                <p><strong>Reply:</strong> <?php echo htmlspecialchars($reply['ticket_reply']); ?></p>
                                <p class="date">Replied on: <?php echo htmlspecialchars($reply['replied_at']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p>No replies yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ticket-footer">
                    <button class="btn view-more"
                        onclick="showDetails(<?php echo htmlspecialchars($ticket['ticket_id']); ?>)">View More</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="no-tickets">No tickets found. Submit a new ticket!</p>
        <?php endif; ?>
    </div>

    <!-- Add this modal at the bottom of your body tag -->
    <div id="imageModal" class="modal">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <!-- Add this JavaScript before the closing body tag -->
    <script>
    // Get the modal
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("modalImage");
    var span = document.getElementsByClassName("close-modal")[0];

    // Function to open modal
    function openImageModal(imgSrc) {
        modal.style.display = "block";
        modalImg.src = imgSrc;
    }

    // Close modal when clicking (X)
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Close modal when clicking outside the image
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>






    <?php include_once '../common/footer.php'; ?>
</body>

</html>