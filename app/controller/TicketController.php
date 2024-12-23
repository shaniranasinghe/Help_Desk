<?php
require '../../model/includes/config.php'; 
require '../../model/TicketModel.php'; // Make sure this path is correct, too

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/auth/login.php");
    exit();
}

class TicketController {
    private $ticketModel;
    private $conn; // Declare the connection property

    public function __construct($conn) {
        $this->conn = $conn; // Initialize the $conn property
        $this->ticketModel = new TicketModel($conn); // Initialize the model with the connection
    }

    // User-specific operations
    public function createTicket() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_id = $_SESSION['user_id'];
            $ticket_title = trim($_POST['ticket_title']);
            $ticket_description = trim($_POST['ticket_description']);
            $issue_type = $_POST['issue_type'];
            $company_id = $_POST['company_id'];

            // Use the model's method to create a ticket
            if ($this->ticketModel->createTicket($ticket_title, $ticket_description, $user_id, $issue_type, $company_id)) {
                header("Location: ../view/tickets/ticket_confirmation.php");
                exit();
            } else {
                echo "Error: Unable to create ticket.";
            }
        }
    }

    // Get all tickets for a user
    public function getAllTickets() {
        return $this->ticketModel->getAllTickets();
    }

    // Company-specific operations (Support)
    public function showTicketsForCompany($company_id) {
        return $this->ticketModel->getTicketsForCompany($company_id);
    }

    public function getTicketsWithReplies($user_id) {
        return $this->ticketModel->getTicketsWithRepliesByUserId($user_id);
    }


    public function transferTicket($ticket_id, $to_company_id) {
        return $this->ticketModel->transferTicket($ticket_id, $to_company_id);
    }

    // Change the status of a ticket
    public function changeTicketStatus($ticketId, $status) {
        if ($status !== 'open' && $status !== 'resolved') {
            return false; // Invalid status
        }

        // Use the already initialized ticket model with the connection
        return $this->ticketModel->updateTicketStatus($ticketId, $status);
    }

    // Method to fetch tickets sorted by priority
    public function getTicketsSortedByPriority($company_id) {
        // Ensure $this->conn is initialized
        if ($this->conn === null) {
            throw new Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM tickets WHERE company_id = ? ORDER BY FIELD(priority, 'high', 'medium', 'low') ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}

?>
