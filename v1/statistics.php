<?php
require_once '../includes/Db_operations.php';
header('Content-Type: application/json; charset=utf-8');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['user_id'])) {
        echo json_encode([
            'error' => true,
            'message' => 'Missing required field: user_id'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $userId = intval($_POST['user_id']);
    $db = new Db_operations();

    // If client asked for current month calories data
    if (isset($_POST['get_current_month_calories']) && $_POST['get_current_month_calories'] === 'true') {
        $monthlyData = $db->getCurrentMonthCaloriesData($userId);
        echo json_encode($monthlyData, JSON_PRETTY_PRINT);
        exit;
    }

    // If client specifically asked for goals only
    if (isset($_POST['get_goals']) && $_POST['get_goals'] === 'true') {
        $goals = $db->getUserGoals($userId);
        echo json_encode($goals, JSON_PRETTY_PRINT);
        exit;
    }

    // If client wants to delete a goal
    if (isset($_POST['delete_goal']) && $_POST['delete_goal'] === 'true') {
        if (!isset($_POST['goal_id'])) {
            echo json_encode([
                'error' => true,
                'message' => 'Missing required field: goal_id'
            ], JSON_PRETTY_PRINT);
            exit;
        }
        
        $goalId = intval($_POST['goal_id']);
        $result = $db->deleteGoal($userId, $goalId);
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }

    // If client asked for all activities
    if (isset($_POST['get_all_activities']) && $_POST['get_all_activities'] === 'true') {
        $activities = $db->getAllActivities($userId);
        echo json_encode($activities, JSON_PRETTY_PRINT);
        exit;
    }

    // If client wants to delete an activity
    if (isset($_POST['delete_activity']) && $_POST['delete_activity'] === 'true') {
        if (!isset($_POST['activity_id']) || !isset($_POST['activity_type'])) {
            echo json_encode([
                'error' => true,
                'message' => 'Missing required fields: activity_id and activity_type'
            ], JSON_PRETTY_PRINT);
            exit;
        }
        
        $activityId = intval($_POST['activity_id']);
        $activityType = $_POST['activity_type'];
        $result = $db->deleteActivity($userId, $activityId, $activityType);
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }

    // for profile statistics only
    if (isset($_POST['get_user_prof_stat']) && $_POST['get_user_prof_stat'] === 'true') {
        $profileStats = $db->getUserProfileStats($userId);
        echo json_encode($profileStats, JSON_PRETTY_PRINT);
        exit;
    }

    // for comprehensive statistics
    if (isset($_POST['get_statistics']) && $_POST['get_statistics'] === 'true') {
        $stats = $db->getComprehensiveStatistics($userId);
        echo json_encode($stats, JSON_PRETTY_PRINT);
        exit;
    }

    // Default: return dashboard stats 
    $dashboard = $db->getDashboardStats($userId);
    echo json_encode($dashboard, JSON_PRETTY_PRINT);
    exit;

} else {
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request method. Use POST.'
    ], JSON_PRETTY_PRINT);
    exit;
}
?>