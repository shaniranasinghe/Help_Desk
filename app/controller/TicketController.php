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
        if (!in_array($status, ['open', 'resolved', 'pending'])) {
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
                  AND ticket_status != 'resolved' AND assigned_to IS NULL
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
              AND assigned_to = ? AND ticket_status != 'resolved'
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

        $query = "SELECT * FROM tickets 
                  WHERE company_id = ? AND ticket_status = 'resolved' 
                  ORDER BY updated_at DESC 
                  LIMIT 5";  // Added LIMIT clause

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


    public function getAllMemberAssignedTickets()
    {
        $query = "SELECT tickets.*, users.user_name 
                  FROM tickets 
                  LEFT JOIN users ON tickets.assigned_to = users.id 
                  WHERE ticket_status != 'resolved' AND assigned_to IS NOT NULL";
        $result = $this->conn->query($query);
        return $result;
    }


    public function getAllCompanyAssignedTickets()
    {
        if ($this->conn === null) {
            throw new Exception("Database connection is not initialized.");
        }

        // Updated query with a JOIN to fetch company name
        $query = "
            SELECT 
                tickets.*, 
                companies.company_name 
            FROM tickets
            INNER JOIN companies ON tickets.company_id = companies.company_id
            WHERE ticket_status != 'resolved' AND assigned_to IS NULL
            ORDER BY FIELD(priority, 'high', 'medium', 'low') ASC
        ";

        if (!($stmt = $this->conn->prepare($query))) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAllResolvedTickets()
    {
        $query = "SELECT * FROM tickets WHERE ticket_status = 'resolved'";
        $result = $this->conn->query($query); // Updated from $this->db to $this->conn
        return $result;
    }

    public function getTicketTransfers($company_id)
    {
        if ($this->conn === null) {
            throw new Exception("Database connection is not initialized.");
        }

        $query = "SELECT 
                    tt.transfer_id,
                    tt.ticket_id,
                    t.ticket_title,
                    c1.company_name as from_company,
                    c2.company_name as to_company,
                    tt.transferred_at
                  FROM ticket_transfers tt
                  JOIN tickets t ON tt.ticket_id = t.ticket_id
                  JOIN companies c1 ON tt.from_company_id = c1.company_id
                  JOIN companies c2 ON tt.to_company_id = c2.company_id
                  WHERE tt.from_company_id = ? OR tt.to_company_id = ?
                  ORDER BY tt.transferred_at DESC
                  LIMIT 5";

        if (!($stmt = $this->conn->prepare($query))) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }

        if (!$stmt->bind_param("ii", $company_id, $company_id)) {
            throw new Exception("Parameter binding failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function deleteTicket($ticketId) {
        $query = "DELETE FROM tickets WHERE ticket_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $ticketId);
    
        return $stmt->execute();
    }
}