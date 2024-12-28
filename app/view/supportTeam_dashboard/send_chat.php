<?php
require_once('../../model/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ticket_id = intval($data['ticket_id']);
    $message = htmlspecialchars($data['message']);
    $sender = $_SESSION['Acc_type'] === 'Support' ? 'support' : 'user';

    $query = "INSERT INTO chat_messages (ticket_id, sender, text) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $ticket_id, $sender, $message);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}
?>
