<?php
require_once '../includes/Db_operations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (
        isset($_POST['user_id']) &&
        isset($_POST['distance']) &&
        isset($_POST['time_minutes']) &&
        isset($_POST['weather']) &&
        isset($_POST['speed']) &&
        isset($_POST['calories'])
    ) {
        $userId = $_POST['user_id'];
        $distanceKm = $_POST['distance'];
        $timeMinutes = $_POST['time_minutes'];
        $weather = $_POST['weather'];
        $speedKmh = $_POST['speed'];
        $caloriesBurned = $_POST['calories'];
        $note = isset($_POST['note']) ? $_POST['note'] : "";

        $db = new Db_operations();
        $result = $db->saveWalkingActivity($userId, $distanceKm, $timeMinutes, $weather, $speedKmh, $caloriesBurned, $note);

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
