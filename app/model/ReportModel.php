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
                tt.from_company_id, tt.to_company_id, tt.transferred_at 
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN companies c ON t.company_id = c.company_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            WHERE t.user_id = '$userID'
            ORDER BY t.created_at DESC
        ";
        

        $result = $conn->query($query);
        if (!$result) {
            die("Query failed: " . $conn->error);
        }

        return $result;
    }
}
?>
