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
$my_assigned_tickets = $ticketController->getMyAssignedTickets($company_id, $_SESSION['user_id']);

// Get resolved tickets
$resolved_tickets = $ticketController->getResolvedTickets($company_id);

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
        <!-- Display My Assigned Tickets First -->
        <h2>My Assigned Tickets</h2>
        <?php if ($my_assigned_tickets->num_rows > 0): ?>
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th class="sortable" onclick="sortByStatus(this)">
                        Status <span class="arrow">▼</span>
                    </th>
                    <th>Priority</th>
                    <th>Attachment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $my_assigned_tickets->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ticket_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_description']); ?></td>
                    <td>
                        <span class="status <?php echo strtolower($row['ticket_status']); ?>">
                            <?php echo htmlspecialchars($row['ticket_status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['priority']); ?></td>
                    <td>
                        <?php if (!empty($row['attachment_path'])): ?>
                        <img src="../../<?php echo htmlspecialchars($row['attachment_path']); ?>"
                            alt="Ticket Attachment" class="ticket-thumbnail" onclick="openImageModal(this.src)">
                        <?php else: ?>
                        <span>No attachment</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['ticket_status'] === 'open'): ?>
                        <button class="btn pending"
                            onclick="changeTicketStatus(<?php echo $row['ticket_id']; ?>, 'pending')">
                            Mark Pending
                        </button>
                        <?php endif; ?>

                        <?php if ($row['ticket_status'] === 'pending'): ?>
                        <button class="btn chat" onclick="openChat(<?php echo $row['ticket_id']; ?>)">
                            Chat
                        </button>
                        <?php endif; ?>

                        <?php if ($row['assigned_to'] == $_SESSION['user_id']): ?>
                        <?php if ($row['ticket_status'] === 'open' || $row['ticket_status'] === 'pending'): ?>
                        <a href="#" class="btn resolve"
                            onclick="handleResolveClick(<?php echo $row['ticket_id']; ?>, <?php echo $row['assigned_to']; ?>, '<?php echo $row['ticket_status']; ?>')">
                            Resolve
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>

                        <button class="btn view-more" onclick="openViewMoreModal(<?php echo $row['ticket_id']; ?>)">
                            View More
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>


        <script>
        function changeTicketStatus(ticketId, status) {
            if (confirm('Are you sure you want to mark this ticket as ' + status + '?')) {
                fetch('../../controller/change_ticket_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            ticket_id: ticketId,
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to update ticket status: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to update ticket status. Please try again.');
                    });
            }
        }

        function openChat(ticketId) {

            window.location.href = `chat.php?ticket_id=${ticketId}`;
        }
        </script>



        <?php else: ?>
        <p class="no-tickets">No tickets are currently assigned to you.</p>
        <?php endif; ?>

        <!--Display All Tickets -->
<h2 style="margin-top: 30px;">All company Tickets</h2>
<?php if ($tickets->num_rows > 0): ?>
<table class="ticket-table">
    <thead>
        <tr>
            <th>Ticket ID</th>
            <th>Title</th>
            <th>Description</th>
            <th class="sortable" onclick="sortByStatus(this)">
                Status <span class="arrow">▼</span>
            </th>
            <th>Priority</th>
            <th>Attachment</th>
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
                <td><?php echo htmlspecialchars($row['priority']); ?></td>
                <td>
                    <?php if (!empty($row['attachment_path'])): ?>
                    <img src="../../<?php echo htmlspecialchars($row['attachment_path']); ?>"
                        alt="Ticket Attachment" class="ticket-thumbnail" onclick="openImageModal(this.src)">
                    <?php else: ?>
                    <span>No attachment</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (empty($row['assigned_to'])): ?>
                    <!-- Add Assign to Me button for unassigned tickets -->
                    <button class="btn assign-to-me" onclick="assignToMe(<?php echo $row['ticket_id']; ?>)">
                        Assign to Me
                    </button>
                    <?php elseif ($row['assigned_to'] == $_SESSION['user_id']): ?>
                    <span class="assigned-badge">Assigned to Me</span>
                    <?php else: ?>
                    <span class="assigned-badge">Assigned to Other</span>
                    <?php endif; ?>

                    <!-- Existing buttons -->
                    <?php if ($row['assigned_to'] == $_SESSION['user_id']): ?>
                    <?php if ($row['ticket_status'] === 'open' || $row['ticket_status'] === 'pending'): ?>
                    <a href="#" class="btn resolve"
                        onclick="handleResolveClick(<?php echo $row['ticket_id']; ?>, <?php echo $row['assigned_to']; ?>, '<?php echo $row['ticket_status']; ?>')">
                        Resolve
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <button class="btn transfer <?php echo ($row['ticket_status'] === 'open' || $row['ticket_status'] === 'pending') ? '' : 'disabled'; ?>"
                        <?php if ($row['ticket_status'] === 'open' || $row['ticket_status'] === 'pending'): ?>
                            onclick="openTransferModal(<?php echo $row['ticket_id']; ?>)"
                        <?php else: ?>
                            onclick="return false;" 
                        <?php endif; ?>>
                        Transfer
                    </button>

                    <button class="btn view-more" onclick="openViewMoreModal(<?php echo $row['ticket_id']; ?>)">
                        View More
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="no-tickets">No tickets available.</p>
    <?php endif; ?>




        <!-- Resolved tickets section -->
        <h2 style="margin-top: 30px;">Resolved Tickets</h2>
        <?php if ($resolved_tickets->num_rows > 0): ?>
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Attachment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resolved_tickets->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ticket_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_description']); ?></td>
                    <td>
                        <span class="status resolved">
                            <?php echo htmlspecialchars($row['ticket_status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['priority']); ?></td>
                    <td>
                        <?php if (!empty($row['attachment_path'])): ?>
                        <img src="../../<?php echo htmlspecialchars($row['attachment_path']); ?>"
                            alt="Ticket Attachment" class="ticket-thumbnail" onclick="openImageModal(this.src)">
                        <?php else: ?>
                        <span>No attachment</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn view-more" onclick="openViewMoreModal(<?php echo $row['ticket_id']; ?>)">
                            View More
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-tickets">No resolved tickets available.</p>
        <?php endif; ?>
    </div>

    <div id="viewMoreModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeViewMoreModal()">&times;</span>
            <h2>Ticket Details</h2>
            <p><strong>Title:</strong> <span id="ticket_title"></span></p>
            <p><strong>Description:</strong> <span id="ticket_description"></span></p>
            <p><strong>Status:</strong> <span id="ticket_status"></span></p>
            <p><strong>Priority:</strong> <span id="ticket_priority"></span></p>

            <h3 style="display: none;">Chat</h3>
            <div id="chat-container" class="chat-container" style="display: none;"></div>

            <div class="chat-input" style="display: none;">
                <textarea id="chatMessage" placeholder="Type your message..."></textarea>

            </div>
        </div>
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

    <!-- Add Modal for Image Preview -->
    <div id="imageModal" class="modal">
        <span class="close-modal" onclick="closeImageModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>


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

    function openViewMoreModal(ticketId) {
        fetch(`get_ticket_details.php?ticket_id=${ticketId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    // Update ticket details
                    document.getElementById("ticket_title").textContent = data.ticket.ticket_title;
                    document.getElementById("ticket_description").textContent = data.ticket.ticket_description;
                    document.getElementById("ticket_status").textContent = data.ticket.ticket_status;
                    document.getElementById("ticket_priority").textContent = data.ticket.priority;

                    // Update chat messages
                    const chatContainer = document.getElementById("chat-container");
                    chatContainer.innerHTML = ""; // Clear old messages
                    data.messages.forEach(message => {
                        const messageDiv = document.createElement("div");
                        messageDiv.classList.add("chat-message");

                        // Check if the user is the sender or receiver
                        if (message.sender_id === loggedInUserId) {
                            messageDiv.classList.add("receiver");
                        } else {
                            messageDiv.classList.add("sender");
                        }

                        messageDiv.textContent = message.message_content;
                        chatContainer.appendChild(messageDiv);
                    });

                    // Show modal
                    document.getElementById("viewMoreModal").style.display = "block";
                }
            })
            .catch(error => console.error("Error fetching ticket details:", error));
    }


    function closeViewMoreModal() {
        document.getElementById("viewMoreModal").style.display = "none";
    }

    function loadChatMessages(ticketId) {
        fetch(`get_chat.php?ticket_id=${ticketId}`)
            .then(response => response.json())
            .then(data => {
                const chatBox = document.getElementById("chat-box");
                chatBox.innerHTML = ""; // Clear existing messages

                data.messages.forEach(message => {
                    const messageElement = document.createElement("div");
                    messageElement.className = `message ${message.sender}`;
                    messageElement.textContent =
                        `${message.sender === 'user' ? 'User: ' : 'Support: '} ${message.text}`;
                    chatBox.appendChild(messageElement);
                });
            })
            .catch(error => console.error('Error loading chat messages:', error));
    }

    function sendMessage(ticketId) {
        const message = document.getElementById("chat-message").value;
        fetch('./send_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ticket_id: ticketId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadChatMessages(ticketId);
                    document.getElementById("chat-message").value = "";
                } else {
                    alert("Error sending message");
                }
            })
            .catch(error => console.error('Error sending message:', error));
    }


    function openImageModal(src) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modal.style.display = "flex";
        modalImg.src = src;
    }

    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

    function closeImageModal() {
        document.getElementById('imageModal').style.display = "none";
    }

    // Close modal when clicking outside the image
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function assignToMe(ticketId) {
        if (confirm('Are you sure you want to assign this ticket to yourself?')) {
            fetch('../../controller/assign_ticket.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ticket_id: ticketId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show updated assignments
                        location.reload();
                    } else {
                        alert('Failed to assign ticket: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to assign ticket. Please try again.');
                });
        }
    }

    const loggedInUserId = <?php echo json_encode($_SESSION['user_id']); ?>;

    function handleResolveClick(ticketId, assignedTo, status) {
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;

        if (assignedTo != currentUserId) {
            alert('You can only resolve tickets assigned to you.');
            return false;
        }

        // Open the resolve modal
        openResolveModal(ticketId);
        return false;
    }
    </script>

    <script>
    function openImageModal(src) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modal.style.display = "block";
        modalImg.src = src;
    }

    function closeImageModal() {
        document.getElementById('imageModal').style.display = "none";
    }

    // Close modal when clicking outside the image
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeImageModal();
        }
    });



    // sort by status
    function sortByStatus(header) {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const arrow = header.querySelector('.arrow');
        const isAscending = !arrow.classList.contains('asc');

        // Update arrow
        arrow.classList.toggle('asc');

        // Sort rows
        rows.sort((a, b) => {
            const statusA = a.querySelector('td:nth-child(4)').textContent.trim();
            const statusB = b.querySelector('td:nth-child(4)').textContent.trim();
            return isAscending ?
                statusA.localeCompare(statusB) :
                statusB.localeCompare(statusA);
        });

        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
    }

    function sortByStatus(header) {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const arrow = header.querySelector('.arrow');
        const isAscending = !arrow.classList.contains('asc');

        // Update arrow
        arrow.classList.toggle('asc');

        // Sort rows
        rows.sort((a, b) => {
            const statusA = a.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
            const statusB = b.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
            return isAscending ?
                statusA.localeCompare(statusB) :
                statusB.localeCompare(statusA);
        });

        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
    }
    </script>

    <?php include_once '../common/footer.php'; ?>

</body>

</html>