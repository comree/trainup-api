<?php
require_once '../includes/Db_operations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (
        isset($_POST['user_id']) &&
        isset($_POST['session_type']) &&
        isset($_POST['time_minutes']) &&
        isset($_POST['intensity']) &&
        isset($_POST['calories'])
    ) {
        $userId = intval($_POST['user_id']);
        $sessionType = trim($_POST['session_type']);
        $durationMinutes = floatval($_POST['time_minutes']);
        $intensity = trim($_POST['intensity']);
        $caloriesBurned = floatval($_POST['calories']);
        $note = isset($_POST['note']) ? trim($_POST['note']) : "";

        /* Normalize intensity to match DB ENUM values exactly
        switch (strtoupper($intensityRaw)) {
            case 'LOW':
                $intensity = 'Low';
                break;
            case 'MEDIUM':
                $intensity = 'Medium';
                break;
            case 'HIGH':
                $intensity = 'High';
                break;
            default:
                $intensity = 'Low';
                break;
        }*/

        $db = new Db_operations();
        $result = $db->saveYogaActivity($userId, $sessionType, $durationMinutes, $intensity, $caloriesBurned, $note);

        echo json_encode($result);
    } else {
        $response['error'] = true;
        $response['message'] = "Missing required fields. Please include user_id, session_type, time_minutes, intensity, and calories.";
        echo json_encode($response);
    }

} else {
    $response['error'] = true;
    $response['message'] = "Invalid Request";
    echo json_encode($response);
}
?>
