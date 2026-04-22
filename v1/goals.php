<?php
require_once '../includes/Db_operations.php';
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(array('error' => true, 'message' => 'Invalid Request'));
    exit;
}

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$activityType = isset($_POST['activity_type']) ? trim($_POST['activity_type']) : null;
$targetsJson = isset($_POST['targets']) ? $_POST['targets'] : null;
$durationOption = isset($_POST['duration_option']) ? trim($_POST['duration_option']) : null;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

if ($userId <= 0 || empty($activityType) || empty($targetsJson)) {
    echo json_encode(array('error' => true, 'message' => 'Missing required fields'));
    exit;
}

$targets = json_decode($targetsJson, true);
if (!is_array($targets)) {
    echo json_encode(array('error' => true, 'message' => 'Targets JSON invalid'));
    exit;
}

$db = new Db_operations();
$result = $db->saveGoalWithTargets($userId, $activityType, $durationOption, $notes, $targets);
echo json_encode($result);
?>