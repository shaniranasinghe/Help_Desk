<?php
require_once('../../model/includes/config.php');

if (isset($_GET['ticket_id'])) {
    $ticket_id = intval($_GET['ticket_id']);
    $query = "SELECT ticket_title, ticket_description, ticket_status, priority FROM tickets WHERE ticket_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $ticketResult = $stmt->get_result();

    $chatQuery = "SELECT * FROM chat_messages WHERE ticket_id = ? ORDER BY created_at ASC";
    $chatStmt = $conn->prepare($chatQuery);
    $chatStmt->bind_param("i", $ticket_id);
    $chatStmt->execute();
    $chatResult = $chatStmt->get_result();

    $messages = [];
    while ($row = $chatResult->fetch_assoc()) {
        $messages[] = $row;
    }

    if ($ticketResult->num_rows > 0) {
        echo json_encode(['ticket' => $ticketResult->fetch_assoc(), 'messages' => $messages]);
    } else {
        echo json_encode(['error' => 'Ticket not found']);
    }

    $stmt->close();
    $chatStmt->close();
}
?>
