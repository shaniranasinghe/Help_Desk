<?php

class ReportModel {
    // Fetch all tickets for a specific user
    public function getUserTickets($userID) {
        global $conn;

        

        $query = "
            SELECT 
                t.ticket_id, t.ticket_title, t.ticket_description, t.ticket_status, t.priority, 
                u.user_name AS submitted_by, 
                c.company_name AS current_company,
                tt.from_company_id, tt.to_company_id, tt.transferred_at,
                tr.ticket_reply, tr.replied_at 
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN companies c ON t.company_id = c.company_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            LEFT JOIN ticket_replies tr ON t.ticket_id = tr.ticket_id 
            WHERE t.user_id = '$userID'
            ORDER BY t.created_at DESC, tr.replied_at ASC
        ";
        

        $result = $conn->query($query);
        if (!$result) {
            die("Query failed: " . $conn->error);
        }

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
                    'transfers' => [],
                    'replies' => []
                ];
            }

            if ($row['ticket_reply']) {
                $tickets[$ticket_id]['replies'][] = [
                    'ticket_reply' => $row['ticket_reply'],
                    'replied_at' => $row['replied_at']
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
    

        return $tickets;
    }
}
?>
