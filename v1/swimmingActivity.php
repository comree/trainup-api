<?php
require_once '../includes/Db_operations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (
        isset($_POST['user_id']) &&
        isset($_POST['distance_meters']) &&
        isset($_POST['time_minutes']) &&
        isset($_POST['weather']) &&
        isset($_POST['stroke_type']) &&
        isset($_POST['speed_mps']) &&
        isset($_POST['calories'])
    ) {
        $userId = $_POST['user_id'];
        $distanceMeters = $_POST['distance_meters'];
        $timeMinutes = $_POST['time_minutes'];
        $weather = $_POST['weather'];
        $strokeType = $_POST['stroke_type'];
        $speedMps = $_POST['speed_mps'];
        $caloriesBurned = $_POST['calories'];
        $note = isset($_POST['note']) ? $_POST['note'] : "";

        require_once '../includes/Db_operations.php';
        $db = new Db_operations();

        $result = $db->saveSwimmingActivity(
            $userId,
            $distanceMeters,
            $timeMinutes,
            $weather,
            $strokeType,
            $speedMps,
            $caloriesBurned,
            $note
        );

        echo json_encode($result);
    } else {
        $response['error'] = true;
        $response['message'] = "Missing required fields";
        echo json_encode($response);
    }

} else {
    $response['error'] = true;
    $response['message'] = "Invalid Request";
    echo json_encode($response);
}
?>
