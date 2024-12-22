<?php
class TicketModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get list of companies
    public function getCompanies() {
        $query = "SELECT company_id, company_name FROM companies ORDER BY company_name ASC";
        return $this->conn->query($query);
    }

    // Create a new ticket
    public function createTicket($title, $description, $userId, $issueType, $companyId) {
        $query = "INSERT INTO tickets (ticket_title, ticket_description, user_id, issue_type, company_id) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssiss", $title, $description, $userId, $issueType, $companyId);
        return $stmt->execute();
    }

    // Get tickets by user ID
    public function getTicketsByUserId($userId) {
        $query = "SELECT ticket_id, ticket_title, ticket_description, created_at 
                  FROM tickets WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getTicketsWithRepliesByUserId($userId) {
        $query = "
            SELECT 
                t.ticket_id, t.ticket_title, t.ticket_description, t.ticket_status, t.created_at, 
                tr.ticket_reply, tr.replied_at 
            FROM tickets t 
            LEFT JOIN ticket_replies tr ON t.ticket_id = tr.ticket_id 
            WHERE t.user_id = ? 
            ORDER BY t.created_at DESC, tr.replied_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Collect tickets with replies
        $tickets = [];
        while ($row = $result->fetch_assoc()) {
            $ticket_id = $row['ticket_id'];

            if (!isset($tickets[$ticket_id])) {
                $tickets[$ticket_id] = [
                    'ticket_id' => $row['ticket_id'],
                    'ticket_title' => $row['ticket_title'],
                    'ticket_description' => $row['ticket_description'],
                    'ticket_status' => $row['ticket_status'],
                    'created_at' => $row['created_at'],
                    'replies' => []
                ];
            }

            if ($row['ticket_reply']) {
                $tickets[$ticket_id]['replies'][] = [
                    'ticket_reply' => $row['ticket_reply'],
                    'replied_at' => $row['replied_at']
                ];
            }
        }

        return $tickets;
    }

    // Get all tickets with additional details
    // Get all tickets with additional details
    public function getAllTickets() {
        $query = "
            SELECT 
                t.ticket_id, t.ticket_title, t.ticket_description, t.ticket_status, t.priority, 
                u.user_name AS submitted_by, 
                c.company_name AS current_company,
                tt.from_company_id, tt.to_company_id, tt.transferred_at 
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN companies c ON t.company_id = c.company_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            ORDER BY t.created_at DESC
        ";
        
        $result = $this->conn->query($query);
        
        // Collect tickets and their transfer history
        $tickets = [];
        
        while ($row = $result->fetch_assoc()) {
            $ticket_id = $row['ticket_id'];
    
            // Initialize ticket if not already present in $tickets array
            if (!isset($tickets[$ticket_id])) {
                $tickets[$ticket_id] = [
                    'ticket_id' => $row['ticket_id'],
                    'ticket_title' => $row['ticket_title'],
                    'ticket_description' => $row['ticket_description'],
                    'ticket_status' => $row['ticket_status'],
                    'priority' => $row['priority'],
                    'submitted_by' => $row['submitted_by'],
                    'current_company' => $row['current_company'],
                    'transfers' => []
                ];
            }
    
            // If transfer details are present, append them to the ticket's transfer history
            if ($row['from_company_id'] !== null) {
                $tickets[$ticket_id]['transfers'][] = [
                    'from_company_id' => $row['from_company_id'],
                    'to_company_id' => $row['to_company_id'],
                    'transferred_at' => $row['transferred_at']
                ];
            }
        }
    
        // Return the tickets with full transfer history
        return $tickets;
    }

    

    // Fetch tickets assigned to a specific company
    public function getTicketsForCompany($company_id) {
        $query = "SELECT ticket_id, ticket_title, ticket_description, ticket_status, priority, created_at 
                  FROM tickets 
                  WHERE company_id = ? 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Resolve a ticket
    public function resolveTicket($ticket_id, $company_id, $ticket_reply) {
        $this->conn->begin_transaction();

        try {
            // Update ticket status
            $stmt = $this->conn->prepare("
                UPDATE tickets 
                SET ticket_status = 'Resolved' 
                WHERE ticket_id = ? AND company_id = ?"
            );
            $stmt->bind_param("ii", $ticket_id, $company_id);
            $stmt->execute();

            // Insert reply into ticket_replies
            $reply_query = "INSERT INTO ticket_replies (ticket_id, ticket_reply, replied_at) 
                            VALUES (?, ?, NOW())";
            $stmt_reply = $this->conn->prepare($reply_query);
            $stmt_reply->bind_param("is", $ticket_id, $ticket_reply);
            $stmt_reply->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    

    // Transfer a ticket to another company
    public function transferTicket($ticket_id, $to_company_id) {
        $transfer_status = 'Open';
        $transferred_at = date("Y-m-d H:i:s");

        $query = "UPDATE tickets SET company_id = ?, ticket_status = ? WHERE ticket_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isi", $to_company_id, $transfer_status, $ticket_id);
        $stmt->execute();

        // Log the transfer
        $transfer_query = "INSERT INTO ticket_transfers (ticket_id, from_company_id, to_company_id, transferred_at) 
                           VALUES (?, ?, ?, ?)";
        $stmt_transfer = $this->conn->prepare($transfer_query);
        $stmt_transfer->bind_param("iiis", $ticket_id, $_SESSION['company_id'], $to_company_id, $transferred_at);
        return $stmt_transfer->execute();
    }

    // Update ticket status
    // TicketModel.php
    public function updateTicketStatus($ticketId, $status) {
        $sql = "UPDATE tickets SET ticket_status = ? WHERE ticket_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $status, $ticketId);
        return $stmt->execute();
    }

    // Method to get tickets sorted by priority (highest first)
    public function getTicketsSortedByPriority() {
        $query = "SELECT * FROM tickets ORDER BY FIELD(priority, 'high', 'medium', 'low') DESC";
        return $this->conn->query($query);
    }

    


}
?>