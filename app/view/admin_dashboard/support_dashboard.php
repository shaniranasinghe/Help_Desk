<?php
include_once '../common/AdminlogHeader.php';


// Restrict access to admin and support team members
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['Acc_type'], ['Admin', 'Support'])) {
    header("Location: ../auth/login.php");
    exit();
}


// Initialize the controller with the database connection
require_once('../../controller/TicketController.php');
$ticketController = new TicketController($conn);


// Retrieve the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];



$ticketModel = new TicketModel($conn);  // Initialize the model here
$companies = $ticketModel->getCompanies();
$allMembers = $ticketModel->getSupportMembers();

// Initialize support members array to be empty by default
$supportMembers = [];

// Check if the company is selected and fetch support members
if (isset($_POST['company_id'])) {
    $companyId = $_POST['company_id'];
    $supportMembers = $ticketModel->getSupportMembersByCompany($companyId);
}


// Handling transfer ticket form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer_ticket'])) {
    $ticketId = $_POST['ticket_id'];
    $transferType = $_POST['transfer_type'];
    $success = false;
    $message = '';

    if ($transferType === 'internal' && !empty($_POST['to_assigned_to'])) {
        $toTeamMember = $_POST['to_assigned_to'];
        $query = "UPDATE tickets SET assigned_to = ? WHERE ticket_id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ii", $toTeamMember, $ticketId);
            $success = $stmt->execute();
            $stmt->close();
        }
    } elseif ($transferType === 'external' && !empty($_POST['to_company_id'])) {
        $toCompanyId = $_POST['to_company_id'];
        $query = "UPDATE tickets SET company_id = ?, assigned_to = NULL WHERE ticket_id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ii", $toCompanyId, $ticketId);
            $success = $stmt->execute();
            $stmt->close();
        }
    }

    if ($success) {
        $message = 'Ticket transferred successfully.';
    } else {
        $message = 'Failed to transfer ticket.';
    }

    $_SESSION['transfer_message'] = $message;
    header("Location: support_dashboard.php");
    exit();
}
$allMemberAssignedTickets = $ticketController->getAllMemberAssignedTickets();
$allCompanyAssignedTickets = $ticketController->getAllCompanyAssignedTickets();
$allResolvedTickets = $ticketController->getAllResolvedTickets();


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Dashboard</title>
    <link rel="stylesheet" href="../../../assets/CSS/ticket_dashboard.css">
    <script>
    function toggleSupportMemberDropdown() {
        const companySelect = document.getElementById("company_id");
        const supportMemberSelect = document.getElementById("support_member");
        const supportMemberContainer = document.getElementById("support_member_container");

        if (companySelect.value) {
            // Show the support member container
            supportMemberContainer.style.display = "block";

            // Fetch support members using AJAX
            const companyId = companySelect.value;
            fetch('../../controller/get_support_members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `company_id=${companyId}`,
                })
                .then((response) => response.json())
                .then((data) => {
                    // Clear existing options
                    supportMemberSelect.innerHTML =
                        '<option value="" disabled selected>Select a Support Member (Optional)</option>';

                    // Populate support member dropdown
                    data.forEach((member) => {
                        const option = document.createElement('option');
                        option.value = member.user_id;
                        option.textContent = member.user_name;
                        supportMemberSelect.appendChild(option);
                    });
                })
                .catch((error) => console.error('Error fetching support members:', error));
        } else {
            // Hide the support member container if no company is selected
            supportMemberContainer.style.display = "none";
        }
    }
    </script>
</head>

<body>

    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-container">
        <nav class="breadcrumb">
            <a href="#" class="breadcrumb-logo">
                <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
            </a>
            <a href="./dashboard.php" class="breadcrumb-link">Help Center</a>
            <span class="breadcrumb-separator">></span>
            <a href="#" class="breadcrumb-link active">Ticket Dashboard</a>
        </nav>
    </div>

    <div class="dashboard-container">
        <!-- Sidebar Section -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Tickets</a></li>
                <li><a href="manage_users.php">Users</a></li>
                <li><a href="manage_companies.php">Companies</a></li>
                <li><a href="ticketListView.php">Summary Report</a></li>
                <li><a href="support_dashboard.php" class="active">Ticket Dashboard</a></li>
                <li><a href="feedback.php">Feedbacks</a></li>
            </ul>
        </div>



        <main class="main-content">
        <!-- Display My Assigned Tickets First -->
        <h3>All Member Assigned Tickets</h3>
        <?php if ($allMemberAssignedTickets->num_rows > 0): ?>
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Assigned To</th>
                    <th class="sortable" onclick="sortByStatus(this)">
                        Status <span class="arrow"></span>
                    </th>
                    <th class="sortable" onclick="sortByPriority(this)">
                        Priority <span class="arrow"></span>
                    </th>
                    <th>Attachment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $allMemberAssignedTickets->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ticket_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_description']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>

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
                        <div class="btn-container">


                       
                        <button class="btn view-more" onclick="openViewMoreModal(<?php echo $row['ticket_id']; ?>)">
                            View More
                        </button>
                        </div>
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

            window.location.href = `../supportTeam_dashboard/chat.php?ticket_id=${ticketId}`;
        }
        </script>


        <?php else: ?>
        <p class="no-tickets">No tickets are currently assigned to you.</p>
        <?php endif; ?>

        <!--Display All Tickets -->
        <h3 style="margin-top: 30px;">All company Tickets</h3>
        <?php if ($allCompanyAssignedTickets->num_rows > 0): ?>
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Company Name</th>
                    <th class="sortable" onclick="sortByStatus(this)">
                        Status <span class="arrow"></span>
                    </th>
                    <th class="sortable" onclick="sortByPriority(this)">
                                Priority <span class="arrow"></span>
                    </th>
                    <th>Attachment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $allCompanyAssignedTickets->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ticket_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_description']); ?></td>
                    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
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
                    <div class="btn-container">
                        <?php if (empty($row['assigned_to'])): ?>
                        
                        <?php elseif ($row['assigned_to'] == $_SESSION['user_id']): ?>
                        <span class="assigned-badge">Assigned to other</span>
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

                        

                        <button class="btn view-more" onclick="openViewMoreModal(<?php echo $row['ticket_id']; ?>)">
                            View More
                        </button>

                    </div>    
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-tickets">No tickets available.</p>
        <?php endif; ?>




        <!-- Resolved tickets section -->
        <h3 style="margin-top: 30px;">Resolved Tickets</h3>
        <?php if ($allResolvedTickets->num_rows > 0): ?>
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
                <?php while ($row = $allResolvedTickets->fetch_assoc()): ?>
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

        </main>
    </div>

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
            <form action="../supportTeam_dashboard/resolve_ticket.php" method="POST">
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
        <form action="./transfer_ticket.php" method="POST">
            <input type="hidden" id="ticket_id_input" name="ticket_id">

            <!-- Select Transfer Type -->
            <label for="transfer_type">Transfer Type:</label>
            <select id="transfer_type" name="transfer_type" onchange="toggleTransferOptions()" required>
                <option value="">--Select Transfer Type--</option>
                <option value="internal">Internal Transfer</option>
                <option value="external">External Transfer</option>
            </select>

            <!-- Internal Transfer Options -->
            <div id="internal_transfer_section" style="display: none;">
                <label for="to_assigned_to">Select Team Member:</label>
                <select name="to_assigned_to" id="to_assigned_to">
                    <option value="">--Select Team Member--</option>
                    <?php
                    $companyId = $_SESSION['company_id'];
                    $teamQuery = "SELECT id, user_name FROM users WHERE company_id = '$companyId'";
                    $result = mysqli_query($conn, $teamQuery);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($member = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . htmlspecialchars($member['id']) . "'>"
                                . htmlspecialchars($member['user_name']) . 
                                "</option>";
                        }
                    }

                    ?>
                </select>
            </div>

            <!-- External Transfer Options -->
            <div id="external_transfer_section" style="display: none;">
                <label for="to_company_id">Select Company to Transfer:</label>
                <select name="to_company_id" id="to_company_id">
                    <option value="">--Select Company--</option>
                    <?php
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
            </div>

            <button type="submit" name="transfer_ticket">Transfer</button>
        </form>
    </div>
</div>

    <!-- Add Modal for Image Preview -->
    <div id="imageModal" class="modal">
    <span class="close-modal right" onclick="closeImageModal()">&times;</span>
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

    function toggleTransferOptions() {
    const transferType = document.getElementById("transfer_type").value;
    const internalSection = document.getElementById("internal_transfer_section");
    const externalSection = document.getElementById("external_transfer_section");

    if (transferType === "internal") {
        internalSection.style.display = "block";
        externalSection.style.display = "none";
    } else if (transferType === "external") {
        internalSection.style.display = "none";
        externalSection.style.display = "block";
    } else {
        internalSection.style.display = "none";
        externalSection.style.display = "none";
    }
}

function openTransferModal(ticketId) {
    document.getElementById("transferModal").style.display = "block";
    document.getElementById("ticket_id_input").value = ticketId;
}

function closeTransferModal() {
    document.getElementById("transferModal").style.display = "none";
    document.getElementById("transfer_type").value = ""; // Reset selection
    toggleTransferOptions(); // Reset sections
}

    function openViewMoreModal(ticketId) {
        fetch(`../supportTeam_dashboard/get_ticket_details.php?ticket_id=${ticketId}`)
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
                        `${message.sender === 'user' ? 'User: ' : 'Admin: '} ${message.text}`;
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

    function handleResolveClick(ticketId, status) {
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;


        // Open the resolve modal
        openResolveModal(ticketId);
        return false;
    }
    </script>




    <script>
        document.getElementById("transfer_type").addEventListener("change", function () {
        const transferType = this.value;
        const internalSection = document.getElementById("internal_transfer_section");
        const externalSection = document.getElementById("external_transfer_section");

        if (transferType === "internal") {
            internalSection.style.display = "block";
            externalSection.style.display = "none";

            function fetchSupportMembers() {
        var selectedCompanyId = document.getElementById('company_id').value;
        var supportMembersDiv = document.getElementById('support_members_div');
        var supportMembersSelect = document.getElementById('all_support_members');

        if (selectedCompanyId) {
            supportMembersDiv.style.display = 'block';
            supportMembersSelect.innerHTML =
                '<option value="" disabled selected>Select a Support Member (Optional)</option>';

            <?php while ($row = $allMembers->fetch_assoc()): ?>
            if (selectedCompanyId === "<?php echo htmlspecialchars($row['company_id']); ?>") {
                var option = document.createElement('option');
                option.value = "<?php echo htmlspecialchars($row['user_id']); ?>";
                option.textContent = "<?php echo htmlspecialchars($row['user_name']); ?>";
                supportMembersSelect.appendChild(option);
            }
            <?php endwhile; ?>
        } else {
            supportMembersDiv.style.display = 'none';
        }
    }
        } else if (transferType === "external") {
            internalSection.style.display = "none";
            externalSection.style.display = "block";
        } else {
            internalSection.style.display = "none";
            externalSection.style.display = "none";
        }
    });

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

    // Update arrow direction
    arrow.classList.toggle('asc');

    // Sort rows alphabetically by status
    rows.sort((a, b) => {
        const statusA = a.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
        const statusB = b.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();

        return isAscending
            ? statusA.localeCompare(statusB)
            : statusB.localeCompare(statusA);
    });

    // Reorder rows in the DOM
    rows.forEach(row => tbody.appendChild(row));
}




    function sortByPriority(header) {
    const table = header.closest('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const arrow = header.querySelector('.arrow');
    const isAscending = !arrow.classList.contains('asc');

    // Update arrow direction
    arrow.classList.toggle('asc');

    // Sort rows based on custom priority order
    rows.sort((a, b) => {
        const priorityA = a.querySelector('td:nth-child(5)').textContent.trim().toLowerCase();
        const priorityB = b.querySelector('td:nth-child(5)').textContent.trim().toLowerCase();

        const priorityOrder = { 'low': 1, 'medium': 2, 'high': 3, 'urgent': 4 };

        return isAscending
            ? (priorityOrder[priorityA] || 0) - (priorityOrder[priorityB] || 0)
            : (priorityOrder[priorityB] || 0) - (priorityOrder[priorityA] || 0);
    });

    // Reorder rows in the DOM
    rows.forEach(row => tbody.appendChild(row));
}


    </script>

    <?php include_once '../common/footer.php'; ?>

</body>

</html>