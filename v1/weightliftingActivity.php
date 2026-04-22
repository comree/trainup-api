<?php
require_once '../includes/Db_operations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (
        isset($_POST['user_id']) &&
        isset($_POST['exercise_name']) &&
        isset($_POST['sets']) &&
        isset($_POST['reps']) &&
        isset($_POST['weight_kg']) &&
        isset($_POST['time_minutes']) &&
        isset($_POST['calories'])
    ) {
        $userId = $_POST['user_id'];
        $exerciseName = $_POST['exercise_name'];
        $sets = $_POST['sets'];
        $reps = $_POST['reps'];
        $weightKg = $_POST['weight_kg'];
        $timeMinutes = $_POST['time_minutes'];
        $caloriesBurned = $_POST['calories'];
        $note = isset($_POST['note']) ? $_POST['note'] : "";

        $db = new Db_operations();
        $result = $db->saveWeightliftingActivity($userId, $exerciseName, $sets, $reps, $weightKg, $timeMinutes, $caloriesBurned, $note);

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
