<?php
require_once '../includes/Db_operations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['userId'])) {
        $db = new Db_operations();
        $userId = $_POST['userId'];

        $deleteResult = $db->deleteUser($userId);

        if ($deleteResult === 0) {
            $response['error'] = false;
            $response['message'] = "User deleted successfully.";
        } else {
            $response['error'] = true;
            $response['message'] = "Failed to delete user.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "User ID is required.";
    }
} else {
    $response['error'] = true;
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>
