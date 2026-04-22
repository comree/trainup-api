<?php
header('Content-Type: application/json');

require_once '../includes/dbconnect.php';

try {
    $db = new dbconnect();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode([
            'error' => true,
            'status' => 'db_error',
            'message' => 'Database connection unavailable'
        ]);
        exit;
    }

    $stmt = $conn->prepare('SELECT 1 as ok');
    if (!$stmt || !$stmt->execute()) {
        echo json_encode([
            'error' => true,
            'status' => 'db_error',
            'message' => 'Database ping failed'
        ]);
        exit;
    }

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;

    echo json_encode([
        'error' => false,
        'status' => 'ok',
        'db' => ($row && intval($row['ok']) === 1) ? 'connected' : 'unknown'
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'error' => true,
        'status' => 'exception',
        'message' => $e->getMessage()
    ]);
}
?>
