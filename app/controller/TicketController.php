<?php
require '../../model/includes/config.php';
require '../../model/TicketModel.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/auth/login.php");
    exit();
}



class TicketController
{
    private $ticketModel;
    private $conn; // Declare the connection property

    public function __construct($conn)
    {
        $this->conn = $conn; // Initialize the $conn property
        $this->ticketModel = new TicketModel($conn); // Initialize the model with the connection
    }

    // User-specific operations
    public function createTicket()
    {
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
    public function getAllTickets()
    {
        return $this->ticketModel->getAllTickets();
    }

    // Company-specific operations (Support)
    public function showTicketsForCompany($company_id)
    {
        return $this->ticketModel->getTicketsForCompany($company_id);
    }

    public function getTicketsWithReplies($user_id)
    {
        return $this->ticketModel->getTicketsWithRepliesByUserId($user_id);
    }


    public function transferTicket($ticket_id, $to_company_id)
    {
        return $this->ticketModel->transferTicket($ticket_id, $to_company_id);
    }

    // Change the status of a ticket
    public function changeTicketStatus($ticketId, $status)
    {
        if ($status !== 'open' && $status !== 'resolved') {
            return false; // Invalid status
        }

        // Use the already initialized ticket model with the connection
        return $this->ticketModel->updateTicketStatus($ticketId, $status);
    }

    // Method to fetch tickets sorted by priority
    public function getTicketsSortedByPriority($company_id)
    {
        if ($this->conn === null) {
            throw new Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM tickets 
                  WHERE company_id = ? 
                  AND ticket_status != 'resolved'
                  ORDER BY FIELD(priority, 'high', 'medium', 'low') ASC";

        if (!($stmt = $this->conn->prepare($query))) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getMyAssignedTickets($company_id, $support_member_id)
    {
        if ($this->conn === null) {
            throw new Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM tickets 
              WHERE company_id = ? 
              AND assigned_to = ? 
              ORDER BY FIELD(priority, 'high', 'medium', 'low') ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $company_id, $support_member_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function getResolvedTickets($company_id)
    {
        if ($this->conn === null) {
            throw new Exception("Database connection is not initialized.");
        }

        $query = "SELECT * FROM tickets WHERE company_id = ? AND ticket_status = 'resolved' ORDER BY updated_at DESC";

        // Debug the query
        if (!($stmt = $this->conn->prepare($query))) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }

        if (!$stmt->bind_param("i", $company_id)) {
            throw new Exception("Parameter binding failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }





    public function getSupportMembersDropdown($companyId)
    {
        $supportMembers = $this->ticketModel->getSupportMembersByCompany($companyId);
        return $supportMembers;
    }



    public function getSupportMembers()
    {
        return $this->ticketModel->getSupportMembers();
    }

    public function getSupportMembersbyCompany($company_id)
    {
        if ($this->conn === null) {
            throw new Exception("Database connection is not initialized.");
        }

        $query = "SELECT 
                    id AS user_id,
                    user_name,
                    company_id 
                  FROM users 
                  WHERE Acc_type = 'Support' 
                  AND company_id = ?
                  ORDER BY user_name ASC";

        // Debug the query
        if (!($stmt = $this->conn->prepare($query))) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }

        if (!$stmt->bind_param("i", $company_id)) {
            throw new Exception("Parameter binding failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }
}
