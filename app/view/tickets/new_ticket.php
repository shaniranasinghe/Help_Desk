<?php
include_once '../common/log_header.php';
require '../../controller/TicketController.php';


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the company_id for the logged-in user
$ticketModel = new TicketModel($conn);  // Initialize the model here
$userQuery = "SELECT company_id FROM users WHERE id = ?";
if ($stmt = $conn->prepare($userQuery)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($company_id); // Fetch the company_id
    $stmt->fetch();
    $stmt->close();
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raise a New Ticket</title>
    <link rel="stylesheet" href="../../../assets/CSS/new_ticket.css">
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
    <div class="breadcrumb-container">
        <nav class="breadcrumb">
            <a href="../pages/home.php" class="breadcrumb-logo">
                <img src="../../../assets/Images/logo.png" alt="Help Desk Logo" class="logo">
            </a>
            <a href="../pages/home.php" class="breadcrumb-link">Help Center</a>
            <span class="breadcrumb-separator">></span>
            <a href="./knowledgebase.php" class="breadcrumb-link active">Raise Ticket</a>
        </nav>
    </div>

    <!-- Ticket Form Section -->
    <section class="form-section">
        <div class="container">
            <h2>Raise a New Ticket</h2>
            <p>Please fill out the form below to raise a ticket. Our support team will respond shortly.</p>
            <form action="../../controller/submit_ticket.php" method="POST" class="ticket-form"
                enctype="multipart/form-data">

                <div class="inputbox">
                    <label for="ticket_title">Ticket Title</label>
                    <input type="text" id="ticket_title" name="ticket_title" required>
                </div>

                <!-- Ticket Description -->
                <div class="inputbox">
                    <label for="ticket_description">Description</label>
                    <textarea name="ticket_description" id="ticket_description" rows="5" required></textarea>
                </div>

                <!-- Issue Type -->
                <div class="inputbox">
                    <label for="issue_type">Issue Type</label>
                    <select name="issue_type" id="issue_type" required>
                        <option value="software">Software Issue</option>
                        <option value="hardware">Hardware Issue</option>
                        <option value="other">Other Issue</option>
                    </select>
                </div>

                <!-- Select Company (default to user's company) -->
                <div class="inputbox">
                    <label for="company_id">Select Company</label>
                    <select name="company_id" id="company_id" required onchange="fetchSupportMembers() ">
                        <option value="" disabled>Select a Company</option>
                        <?php while ($row = $companies->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['company_id']); ?>"
                            <?php if ($row['company_id'] == $company_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['company_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>






                <div class="inputbox" id="support_members_div">
                    <label for="all_support_members">All Support Members</label>
                    <select name="support_member" id="all_support_members">
                        <option value="" disabled selected>Select a Support Member (Optional)</option>
                    </select>
                </div>



                <!-- Priority -->
                <div class="inputbox">
                    <label for="priority">Priority</label>
                    <select name="priority" id="priority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>


                <!-- Add file input field -->
                <div class="inputbox">
                    <label for="attachment">Attachment (Optional)</label>
                    <input type="file" name="attachment" id="attachment" accept="image/*">
                    <small>Accepted formats: JPG, PNG, GIF (Max size: 5MB)</small>
                </div>

                <button type="submit">Submit Ticket</button>
            </form>
        </div>
    </section>

    <?php include_once '../common/footer.php'; ?>

    <script>
    document.getElementById('attachment').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (5MB limit)
            if (file.size > 5 * 1024 * 1024) {
                alert('File is too large! Maximum size is 5MB.');
                this.value = ''; // Clear the input
                return;
            }

            // Check file type
            if (!file.type.match('image.*')) {
                alert('Only image files are allowed!');
                this.value = ''; // Clear the input
                return;
            }

            // Preview image
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.style.maxWidth = '200px';
                preview.style.marginTop = '10px';

                // Remove existing preview if any
                const existingPreview = document.querySelector('#attachment-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }

                // Add new preview
                const previewDiv = document.createElement('div');
                previewDiv.id = 'attachment-preview';
                previewDiv.appendChild(preview);
                document.getElementById('attachment').parentNode.appendChild(previewDiv);
            }
            reader.readAsDataURL(file);
        }
    });















    document.addEventListener('DOMContentLoaded', function() {
        fetchSupportMembers(); // Fetch support members on page load
    });

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
    </script>

    <script src="JS/script.js"></script>


</body>

</html>