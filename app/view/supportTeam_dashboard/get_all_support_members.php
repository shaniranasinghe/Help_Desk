<?php
require_once('../../model/includes/config.php');

header('Content-Type: application/json');

$query = "SELECT id, user_name, company_id FROM users";
$result = mysqli_query($conn, $query);

if ($result) {
    $members = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
    echo json_encode(['success' => true, 'members' => $members]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch support members']);
}
