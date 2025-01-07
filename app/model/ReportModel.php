<?php

class ReportModel {
    public function getFilteredTickets($filters) {
        global $conn;

        $conditions = [];
        
        // Add conditions based on filters
        if (!empty($filters['user_id'])) {
            $conditions[] = "t.user_id = '" . $conn->real_escape_string($filters['user_id']) . "'";
        }
        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $conditions[] = "t.priority = '" . $conn->real_escape_string($filters['priority']) . "'";
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "t.ticket_status = '" . $conn->real_escape_string($filters['status']) . "'";
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = "DATE(t.created_at) BETWEEN '" . $conn->real_escape_string($filters['start_date']) . "' AND '" . $conn->real_escape_string($filters['end_date']) . "'";
        }
        if (!empty($filters['company'])) {
            $conditions[] = "t.company_id = '" . $conn->real_escape_string($filters['company']) . "'";
        }
        

        // Build the query
        $query = "
            SELECT 
                t.ticket_id, t.user_id, t.ticket_title, t.ticket_description, t.ticket_status, t.priority, 
                u.user_name AS submitted_by, 
                c.company_name AS current_company,
                tt.from_company_id, tt.to_company_id, tt.transferred_at,
                tr.ticket_reply, tr.replied_at 
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN companies c ON t.company_id = c.company_id
            LEFT JOIN ticket_transfers tt ON t.ticket_id = tt.ticket_id
            LEFT JOIN ticket_replies tr ON t.ticket_id = tr.ticket_id

        ";

        // Append conditions
        if (!empty($conditions)) {
            $query .= "WHERE " . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY t.created_at DESC, tr.replied_at ASC";

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
                    'user_id' => $row['user_id'],
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