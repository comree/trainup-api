<?php
class Db_operations {
    private $con;

    function __construct(){
        require_once dirname(__FILE__).'/dbconnect.php';
        $db = new dbconnect();
        $this->con = $db->connect();
    }

    //creating the user
    public function createUser($username, $email,$gender, $pass,$weight)
    {
        if($this->isUserExist($username, $email)){
            return 1;
        } else {
            // Hash the password (make sure column is VARCHAR(255))
            $password = password_hash($pass, PASSWORD_BCRYPT);

            $sql = "INSERT INTO train_up_users (username, email, gender, password, weight) VALUES (?, ?, ?,?,?)";
            $createuserquerry = $this->con->prepare($sql);

            if (!$createuserquerry) {
                // Do not stop execution; return error code and message
                return 2;
            }

            $createuserquerry->bind_param("sssss", $username, $email,$gender, $password,$weight);

            if ($createuserquerry->execute()) {
                return 0;
            } else {
                return 2;
            }
        }
    }

    

    //user login
    public function userLogin($email, $pass)
    {
        $sql = "SELECT id, password FROM train_up_users WHERE email = ?";
        $userloginquerry = $this->con->prepare($sql);
        $userloginquerry->bind_param("s", $email);
        $userloginquerry->execute();

        $userloginquerry->store_result();

        if($userloginquerry->num_rows == 0){
            return 0;
        }

        $userloginquerry->bind_result($id, $dbPassword);
        $userloginquerry->fetch();

        if(password_verify($pass, $dbPassword)){
            return $id; // return user id on success
        }

        return 0;
    }

    public function getUserByUsername($username){
        $sql = "SELECT * FROM train_up_users WHERE username = ?";
        $getuserquerry = $this->con->prepare($sql);
        $getuserquerry->bind_param("s", $username);
        $getuserquerry->execute();
        return $getuserquerry->get_result()->fetch_assoc();
    }

    public function getUserByEmail($useremail){
        $sql = "SELECT * FROM train_up_users WHERE email = ?";
        $getuserquerry = $this->con->prepare($sql);
        $getuserquerry->bind_param("s", $useremail);
        $getuserquerry->execute();
        return $getuserquerry->get_result()->fetch_assoc();
    }

    private function isUserExist($username, $email){
        $sql = "SELECT id FROM train_up_users WHERE username = ? OR email = ?";
        $userexistquerry = $this->con->prepare($sql);
        $userexistquerry->bind_param("ss", $username, $email);
        $userexistquerry->execute();
        $userexistquerry->store_result();
        return $userexistquerry->num_rows > 0;
    }

     public function deleteUser($userId) {
    // Prepare the delete statement
    $sql = "DELETE FROM train_up_users WHERE id = ?";
    $stmt = $this->con->prepare($sql);

    if (!$stmt) {
        // Failed to prepare the statement
        return 2; // Error code
    }

    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        // Check if a row was actually deleted
        if ($stmt->affected_rows > 0) {
            return 0; // Success
        } else {
            return 1; // User not found
        }
    } else {
        return 2; // Execution error
    }
}


    //---------saving activities
    //save run activity
    public function saveRunningActivity($userId, $distanceKm, $timeMinutes, $weather, $speedKmh, $caloriesBurned, $note) {
        $sql = "INSERT INTO running_activities 
                (user_id, distance_km, time_minutes, weather, speed_kmh, calories_burned, note)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            return array("error" => true, "message" => "Prepare failed: " . $this->con->error);
        }

        // i: int, d: double, d: double, s: string, d: double, d: double, s: string
        $stmt->bind_param("iddsdds", $userId, $distanceKm, $timeMinutes, $weather, $speedKmh, $caloriesBurned, $note);

        if ($stmt->execute()) {
            return array("error" => false, "message" => "Running activity saved successfully");
        } else {
            return array("error" => true, "message" => "Execute failed: " . $stmt->error);
        }
    }

    //save cycling activity
    public function saveCyclingActivity($userId, $distanceKm, $timeMinutes, $weather, $bikeType, $speedKmh, $caloriesBurned, $note)
    {
        $stmt = $this->con->prepare("INSERT INTO cycling_activities (user_id, distance, time_minutes, weather, bike_type, speed, calories, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            return array("error" => true, "message" => "Prepare failed: " . $this->con->error);
        }

        // i d d s s d d s
        $stmt->bind_param("iddssdds", $userId, $distanceKm, $timeMinutes, $weather, $bikeType, $speedKmh, $caloriesBurned, $note);

        if ($stmt->execute()) {
            return array("error" => false, "message" => "Cycling activity saved successfully");
        } else {
            return array("error" => true, "message" => "Failed to save cycling activity: " . $stmt->error);
        }
    }

    public function saveWeightliftingActivity($userId, $exerciseName, $sets, $reps, $weightKg, $timeMinutes, $calories, $note)
    {
        $stmt = $this->con->prepare("
            INSERT INTO weightlifting_activities
            (user_id, exercise_name, sets, reps, weight_kg, time_minutes, calories, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            return array("error" => true, "message" => "Prepare failed: " . $this->con->error);
        }

        // i s i i d d d s
        $stmt->bind_param("isiiddds", $userId, $exerciseName, $sets, $reps, $weightKg, $timeMinutes, $calories, $note);

        if ($stmt->execute()) {
            return array("error" => false, "message" => "Weightlifting activity saved successfully");
        } else {
            return array("error" => true, "message" => "Database error: " . $stmt->error);
        }
    }

    public function saveYogaActivity($userId, $sessionType, $durationMinutes, $intensity, $calories, $note)
    {
        $sql = "
            INSERT INTO yoga_activities
            (user_id, session_type, duration_minutes, intensity, calories, note)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            return array("error" => true, "message" => "Prepare failed: " . $this->con->error);
        }

        // i s d s d s  (assuming intensity stored as string; adjust if different)
        $stmt->bind_param("isdsds", $userId, $sessionType, $durationMinutes, $intensity, $calories, $note);

        try {
            if ($stmt->execute()) {
                return array("error" => false, "message" => "Yoga activity saved successfully");
            } else {
                return array("error" => true, "message" => "Execute failed: " . $stmt->error);
            }
        } catch (mysqli_sql_exception $ex) {
            return array("error" => true, "message" => "Database Error: " . $ex->getMessage());
        }
    }

    public function saveSwimmingActivity($userId, $distanceMeters, $timeMinutes, $weather, $strokeType, $speedMps, $caloriesBurned, $note)
    {
        $stmt = $this->con->prepare("INSERT INTO swimming_activities (user_id, distance_meters, time_minutes, weather, stroke_type, speed_mps, calories_burned, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            return array('error' => true, 'message' => 'Prepare failed: ' . $this->con->error);
        }

        // i d d s s d d s
        $stmt->bind_param("iddssdds", $userId, $distanceMeters, $timeMinutes, $weather, $strokeType, $speedMps, $caloriesBurned, $note);

        if ($stmt->execute()) {
            return ['error' => false, 'message' => 'Swimming activity saved successfully'];
        } else {
            return ['error' => true, 'message' => 'Failed to save swimming activity: ' . $stmt->error];
        }
    }

    public function saveWalkingActivity($userId, $distanceKm, $timeMinutes, $weather, $speedKmh, $caloriesBurned, $note)
    {
        $stmt = $this->con->prepare(
            "INSERT INTO walking_activities (user_id, distance_km, time_minutes, weather, speed_kmh, calories_burned, note)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            // i d d s d d s
            $stmt->bind_param("iddsdds", $userId, $distanceKm, $timeMinutes, $weather, $speedKmh, $caloriesBurned, $note);

            if ($stmt->execute()) {
                return ['error' => false, 'message' => 'Walking activity saved successfully'];
            } else {
                return ['error' => true, 'message' => 'Failed to save walking activity: ' . $stmt->error];
            }
        } else {
            return ['error' => true, 'message' => 'Database error: ' . $this->con->error];
        }
    }

   //save goals
    public function saveGoal($userId, $activityType, $durationOption = null, $notes = null)
    {
        $sql = "INSERT INTO goals (user_id, activity_type, duration_option, notes) VALUES (?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            return array('error' => true, 'message' => 'Prepare failed: ' . $this->con->error);
        }

        $stmt->bind_param("isss", $userId, $activityType, $durationOption, $notes);
        if ($stmt->execute()) {
            $goalId = $this->con->insert_id;
            return array('error' => false, 'message' => 'Goal created', 'goal_id' => $goalId);
        } else {
            return array('error' => true, 'message' => 'Execute failed: ' . $stmt->error);
        }
    }

    public function saveGoalTarget($goalId, $metricKey, $value, $unit = null)
    {
        $sql = "INSERT INTO goal_targets (goal_id, metric_key, value, unit) VALUES (?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            return array('error' => true, 'message' => 'Prepare failed: ' . $this->con->error);
        }

        $stmt->bind_param("isds", $goalId, $metricKey, $value, $unit);
        if ($stmt->execute()) {
            return array('error' => false, 'message' => 'Target saved');
        } else {
            return array('error' => true, 'message' => 'Execute failed: ' . $stmt->error);
        }
    }

    public function saveGoalWithTargets($userId, $activityType, $durationOption = null, $notes = null, $targets = array())
    {
        if (!is_array($targets) || count($targets) === 0) {
            return array('error' => true, 'message' => 'Targets are required');
        }

        $this->con->begin_transaction();

        try {
            $goalResult = $this->saveGoal($userId, $activityType, $durationOption, $notes);
            if ($goalResult['error']) {
                throw new Exception($goalResult['message'] ?? 'Failed to create goal');
            }

            $goalId = intval($goalResult['goal_id']);
            $savedTargets = 0;

            foreach ($targets as $t) {
                if (!isset($t['metric_key']) || !isset($t['value'])) {
                    continue;
                }

                $metric = trim((string)$t['metric_key']);
                $rawValue = $t['value'];

                if ($metric === '' || !is_numeric($rawValue)) {
                    continue;
                }

                $value = floatval($rawValue);
                $unit = isset($t['unit']) ? trim((string)$t['unit']) : null;

                $targetResult = $this->saveGoalTarget($goalId, $metric, $value, $unit);
                if ($targetResult['error']) {
                    throw new Exception($targetResult['message'] ?? 'Failed to save target');
                }

                $savedTargets++;
            }

            if ($savedTargets === 0) {
                throw new Exception('No valid targets were provided');
            }

            $this->con->commit();
            return array('error' => false, 'message' => 'Goal and targets saved successfully', 'goal_id' => $goalId);

        } catch (Exception $e) {
            $this->con->rollback();
            return array('error' => true, 'message' => 'Failed to save goal: ' . $e->getMessage());
        }
    }

//code to load Dashbord statics data from db
    public function getDashboardStats($userId)
    {
        $response = array();

        // Date ranges
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $week_start = date('Y-m-d 00:00:00', strtotime('-6 days'));
        $week_end = date('Y-m-d 23:59:59');

        // Tables to iterate
        $tables = [
            ['table' => 'running_activities', 'date' => 'created_at', 'cal' => 'calories_burned'],
            ['table' => 'cycling_activities', 'date' => 'created_at', 'cal' => 'calories'],
            ['table' => 'walking_activities', 'date' => 'created_at', 'cal' => 'calories_burned'],
            ['table' => 'yoga_activities', 'date' => 'created_at', 'cal' => 'calories'],
            ['table' => 'swimming_activities', 'date' => 'created_at', 'cal' => 'calories_burned'],
            ['table' => 'weightlifting_activities', 'date' => 'created_at', 'cal' => 'calories']
        ];

        $today_total = 0;
        $weekly_total = 0;
        $weekly_calories = 0.0;

        foreach ($tables as $t) {
            $table = $t['table'];
            $date_col = $t['date'];
            $cal_col = $t['cal'];

            // Today's total activities
            $stmt = $this->con->prepare("SELECT COUNT(*) as total FROM $table WHERE user_id = ? AND $date_col BETWEEN ? AND ?");
            if ($stmt) {
                $stmt->bind_param('iss', $userId, $today_start, $today_end);
                if ($stmt->execute()) {
                    $today_result = $stmt->get_result()->fetch_assoc();
                    $today_count = intval($today_result['total'] ?? 0);
                    $today_total += $today_count;
                }
                $stmt->close();
            }

            // Weekly total + calories
            $stmt = $this->con->prepare("SELECT COUNT(*) as total, COALESCE(SUM($cal_col), 0) as total_calories FROM $table WHERE user_id = ? AND $date_col BETWEEN ? AND ?");
            if ($stmt) {
                $stmt->bind_param('iss', $userId, $week_start, $week_end);
                if ($stmt->execute()) {
                    $week_result = $stmt->get_result()->fetch_assoc();
                    $week_count = intval($week_result['total'] ?? 0);
                    $week_cal = floatval($week_result['total_calories'] ?? 0);

                    $weekly_total += $week_count;
                    $weekly_calories += $week_cal;
                }
                $stmt->close();
            }
        }

        $response['error'] = false;
        $response['today_activities'] = (int)$today_total;
        $response['weekly_activities'] = (int)$weekly_total;
        $response['weekly_calories'] = round($weekly_calories, 2);
        // For backward compatibility include current_streak here as well:
        $response['current_streak'] = $this->calculateCurrentStreak($userId);

        return $response;
    }

    //Get User goals
    public function getUserGoals($userId)
    {
        $response = array();

        try {
            $sql = "SELECT g.id, g.activity_type, g.duration_option, g.notes, g.created_at, g.status 
                    FROM goals g 
                    WHERE g.user_id = ? 
                    ORDER BY g.created_at DESC 
                    LIMIT 100";

            $stmt = $this->con->prepare($sql);
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $this->con->error);
            }

            $stmt->bind_param("i", $userId);

            if (!$stmt->execute()) {
                throw new Exception('Execute error: ' . $stmt->error);
            }

            $result = $stmt->get_result();

            $goals = array();
            while ($goal = $result->fetch_assoc()) {
                $goalId = $goal['id'];

                $targetSql = "SELECT metric_key, value, unit FROM goal_targets WHERE goal_id = ?";
                $targetStmt = $this->con->prepare($targetSql);
                if ($targetStmt) {
                    $targetStmt->bind_param("i", $goalId);
                    $targetStmt->execute();
                    $targetResult = $targetStmt->get_result();

                    $targets = array();
                    while ($target = $targetResult->fetch_assoc()) {
                        $targets[] = $target;
                    }

                    $goal['targets'] = $targets;
                    $targetStmt->close();
                }

                $goals[] = $goal;
            }

            $stmt->close();

            if (count($goals) > 0) {
                $response['error'] = false;
                $response['goals'] = $goals;
            } else {
                $response['error'] = true;
                $response['message'] = 'No goals found for this user';
            }

        } catch (Exception $e) {
            $response['error'] = true;
            $response['message'] = 'Error fetching goals: ' . $e->getMessage();
        }

        return $response;
    }

   
    public function getComprehensiveStatistics($userId) {
        $response = array();

        try {
            // Basic dashboard stats
            $basicStats = $this->getDashboardStats($userId);

            // Performance metrics
            $performanceMetrics = $this->getPerformanceMetrics($userId);

            // Personal records
            $personalRecords = $this->getPersonalRecords($userId);

            // Goal analytics
            $goalAnalytics = $this->getGoalAnalytics($userId);

            // Activity distribution
            $activityDistribution = $this->getActivityDistribution($userId);

            // Combine
            $response['error'] = false;
            $response['basic_stats'] = array(
                'weekly_activities' => $basicStats['weekly_activities'] ?? 0,
                'weekly_calories' => $basicStats['weekly_calories'] ?? 0,
                'today_activities' => $basicStats['today_activities'] ?? 0,
                'current_streak' => $basicStats['current_streak'] ?? $this->calculateCurrentStreak($userId)
            );
            $response['performance_metrics'] = $performanceMetrics;
            $response['personal_records'] = $personalRecords;
            $response['goal_analytics'] = $goalAnalytics;
            $response['activity_distribution'] = $activityDistribution;
            // Keep top-level current_streak for backward compatibility with older clients
            $response['current_streak'] = $basicStats['current_streak'] ?? $this->calculateCurrentStreak($userId);

        } catch (Exception $e) {
            $response['error'] = true;
            $response['message'] = "Error fetching comprehensive statistics: " . $e->getMessage();
        }

        return $response;
    }

   
    // PERFORMANCE METRICS
    
    private function minutesFloatToMinSec($minutesFloat) {
        // Convert fractional minutes (e.g., 5.5) to m:ss
        $totalSeconds = (int)round($minutesFloat * 60);
        $m = intdiv($totalSeconds, 60);
        $s = $totalSeconds % 60;
        return sprintf("%d:%02d", $m, $s);
    }

    public function getPerformanceMetrics($userId) {
        $metrics = array();

        try {
            // Running: average speed (km/h) -> pace (min/km)
            $stmt = $this->con->prepare("
                SELECT AVG(speed_kmh) as avg_speed 
                FROM running_activities 
                WHERE user_id = ? AND speed_kmh > 0
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $runningResult = $stmt->get_result()->fetch_assoc();
            $avgRunningSpeed = floatval($runningResult['avg_speed'] ?? 0);
            $avgPace = $avgRunningSpeed > 0 ? (60 / $avgRunningSpeed) : 0.0; // minutes per km
            $metrics['avg_running_pace'] = $avgPace > 0 ? $this->minutesFloatToMinSec($avgPace) : "0:00";

            // Walking: average speed (km/h) -> pace (min/km)
            $stmt = $this->con->prepare("
                SELECT AVG(speed_kmh) as avg_speed 
                FROM walking_activities 
                WHERE user_id = ? AND speed_kmh > 0
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $walkingResult = $stmt->get_result()->fetch_assoc();
            $avgWalkingSpeed = floatval($walkingResult['avg_speed'] ?? 0);
            $avgWalkingPace = $avgWalkingSpeed > 0 ? (60 / $avgWalkingSpeed) : 0.0;
            $metrics['avg_walking_pace'] = $avgWalkingPace > 0 ? $this->minutesFloatToMinSec($avgWalkingPace) : "0:00";

            // Swimming: average speed (m/s) -> pace per 100m (min:sec per 100m)
            $stmt = $this->con->prepare("
                SELECT AVG(speed_mps) as avg_speed 
                FROM swimming_activities 
                WHERE user_id = ? AND speed_mps > 0
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $swimResult = $stmt->get_result()->fetch_assoc();
            $avgSwimSpeed = floatval($swimResult['avg_speed'] ?? 0); // meters per second
            if ($avgSwimSpeed > 0) {
                // meters per minute = speed_mps * 60
                // time for 100m in minutes = 100 / (speed_mps * 60.0)
                $minutesPer100m = 100 / ($avgSwimSpeed * 60.0);
                $metrics['avg_swimming_pace'] = $this->minutesFloatToMinSec($minutesPer100m);
            } else {
                $metrics['avg_swimming_pace'] = "0:00";
            }

            // Cycling average speed (km/h)
            $stmt = $this->con->prepare("
                SELECT AVG(speed) as avg_speed 
                FROM cycling_activities 
                WHERE user_id = ? AND speed > 0
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $cyclingResult = $stmt->get_result()->fetch_assoc();
            $metrics['avg_cycling_speed'] = $cyclingResult['avg_speed'] ? number_format(floatval($cyclingResult['avg_speed']), 1) : "0.0";

            // Max weight lifted
            $stmt = $this->con->prepare("
                SELECT MAX(weight_kg) as max_weight 
                FROM weightlifting_activities 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $weightResult = $stmt->get_result()->fetch_assoc();
            $metrics['max_weight'] = $weightResult['max_weight'] !== null ? intval($weightResult['max_weight']) : 0;

            // Average yoga duration
            $stmt = $this->con->prepare("
                SELECT AVG(duration_minutes) as avg_duration 
                FROM yoga_activities 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $yogaResult = $stmt->get_result()->fetch_assoc();
            $metrics['avg_yoga_duration'] = $yogaResult['avg_duration'] ? round(floatval($yogaResult['avg_duration'])) : 0;

        } catch (Exception $e) {
            // Defaults on error 
            $metrics['avg_running_pace'] = "0:00";
            $metrics['avg_cycling_speed'] = "0.0";
            $metrics['max_weight'] = 0;
            $metrics['avg_yoga_duration'] = 0;
            $metrics['avg_swimming_pace'] = "0:00";
            $metrics['avg_walking_pace'] = "0:00";
        }

        return $metrics;
    }

    
    public function getPersonalRecords($userId) {
        $records = array();

        try {
            // Longest run
            $stmt = $this->con->prepare("
                SELECT MAX(distance_km) as longest_run 
                FROM running_activities 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $runResult = $stmt->get_result()->fetch_assoc();
            $records['longest_run'] = $runResult['longest_run'] !== null ? number_format(floatval($runResult['longest_run']), 1) : "0.0";

            // Longest cycling ride
            $stmt = $this->con->prepare("
                SELECT MAX(distance) as longest_ride 
                FROM cycling_activities 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $cycleResult = $stmt->get_result()->fetch_assoc();
            $records['longest_ride'] = $cycleResult['longest_ride'] !== null ? number_format(floatval($cycleResult['longest_ride']), 1) : "0.0";

            // Heaviest weight lifted
            $stmt = $this->con->prepare("
                SELECT MAX(weight_kg) as heaviest_lift 
                FROM weightlifting_activities 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $weightResult = $stmt->get_result()->fetch_assoc();
            $records['heaviest_lift'] = $weightResult['heaviest_lift'] !== null ? intval($weightResult['heaviest_lift']) : 0;

            // Longest swim (convert meters -> km)
            $stmt = $this->con->prepare("
                SELECT MAX(distance_meters) as longest_swim 
                FROM swimming_activities 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $swimResult = $stmt->get_result()->fetch_assoc();
            $records['longest_swim'] = $swimResult['longest_swim'] !== null ? number_format((floatval($swimResult['longest_swim']) / 1000.0), 1) : "0.0";

            // Longest walk
            $stmt = $this->con->prepare("
                SELECT MAX(distance_km) as longest_walk 
                FROM walking_activities 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $walkResult = $stmt->get_result()->fetch_assoc();
            $records['longest_walk'] = $walkResult['longest_walk'] !== null ? number_format(floatval($walkResult['longest_walk']), 1) : "0.0";

            // Longest yoga session
            $stmt = $this->con->prepare("
                SELECT MAX(duration_minutes) as longest_yoga 
                FROM yoga_activities 
                WHERE user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $yogaResult = $stmt->get_result()->fetch_assoc();
            $records['longest_yoga'] = $yogaResult['longest_yoga'] !== null ? intval($yogaResult['longest_yoga']) : 0;

        } catch (Exception $e) {
            // Default values on error -> zeros
            $records['longest_run'] = "0.0";
            $records['longest_ride'] = "0.0";
            $records['heaviest_lift'] = 0;
            $records['longest_swim'] = "0.0";
            $records['longest_walk'] = "0.0";
            $records['longest_yoga'] = 0;
        }

        return $records;
    }

    // GOAL ANALYTICS WITH TIME SCOPE FIX
    public function getGoalAnalytics($userId) {
        $analytics = array();

        try {
            $goalsResponse = $this->getUserGoals($userId);

            if (!$goalsResponse['error'] && isset($goalsResponse['goals'])) {
                $goals = $goalsResponse['goals'];
                $completed = 0;
                $inProgress = 0;

                // DEBUG: Log goals data
                error_log("=== GOAL ANALYTICS DEBUG ===");
                error_log("Total goals found: " . count($goals));

                // Initialize progress trackers for each activity type
                $activityProgress = [
                    'running' => ['progress_sum' => 0, 'goal_count' => 0],
                    'cycling' => ['progress_sum' => 0, 'goal_count' => 0],
                    'weightlifting' => ['progress_sum' => 0, 'goal_count' => 0],
                    'yoga' => ['progress_sum' => 0, 'goal_count' => 0],
                    'swimming' => ['progress_sum' => 0, 'goal_count' => 0],
                    'walking' => ['progress_sum' => 0, 'goal_count' => 0]
                ];

                foreach ($goals as $index => $goal) {
                    $activityType = strtolower($goal['activity_type'] ?? '');
                    $targets = $goal['targets'] ?? [];
                    $durationOption = $goal['duration_option'] ?? '';
                    $goalCreatedAt = $goal['created_at'] ?? '';
                    
                    error_log("Goal $index: Activity=$activityType, Duration=$durationOption, Created=$goalCreatedAt, Targets=" . count($targets));
                    
                    if (isset($activityProgress[$activityType]) && !empty($targets)) {
                        foreach ($targets as $targetIndex => $target) {
                            $metricKey = strtolower($target['metric_key'] ?? '');
                            $targetValue = floatval($target['value'] ?? 0);
                            error_log("  Target $targetIndex: metric=$metricKey, value=$targetValue");
                        }
                        
                        $goalProgress = $this->calculateGoalProgress($userId, $activityType, $targets, $durationOption, $goalCreatedAt);
                        error_log("  Calculated progress: $goalProgress%");
                        
                        $activityProgress[$activityType]['progress_sum'] += $goalProgress;
                        $activityProgress[$activityType]['goal_count']++;
                        
                        if ($goalProgress >= 100) {
                            $completed++;
                        } else {
                            $inProgress++;
                        }
                    } else {
                        // Goal with no targets counts as in progress with 0%
                        error_log("  No targets or invalid activity type");
                        $inProgress++;
                    }
                }

                // Calculate overall analytics
                $analytics['completed'] = $completed;
                $analytics['in_progress'] = $inProgress;
                $analytics['success_rate'] = ($completed + $inProgress) > 0 ? 
                    round(($completed / ($completed + $inProgress)) * 100) : 0;

                // Calculate average progress for each activity type
                foreach ($activityProgress as $activity => $data) {
                    $averageProgress = $data['goal_count'] > 0 ? 
                        round($data['progress_sum'] / $data['goal_count']) : 0;
                    $analytics[$activity . '_progress'] = $averageProgress;
                    error_log("Activity $activity: {$data['goal_count']} goals, average progress: $averageProgress%");
                }

                error_log("=== END DEBUG ===");

            } else {
                // No goals found
                $analytics['completed'] = 0;
                $analytics['in_progress'] = 0;
                $analytics['success_rate'] = 0;
                $analytics['running_progress'] = 0;
                $analytics['cycling_progress'] = 0;
                $analytics['weightlifting_progress'] = 0;
                $analytics['yoga_progress'] = 0;
                $analytics['swimming_progress'] = 0;
                $analytics['walking_progress'] = 0;
            }

        } catch (Exception $e) {
            error_log("Error in getGoalAnalytics: " . $e->getMessage());
            // Error case
            $analytics['completed'] = 0;
            $analytics['in_progress'] = 0;
            $analytics['success_rate'] = 0;
            $analytics['running_progress'] = 0;
            $analytics['cycling_progress'] = 0;
            $analytics['weightlifting_progress'] = 0;
            $analytics['yoga_progress'] = 0;
            $analytics['swimming_progress'] = 0;
            $analytics['walking_progress'] = 0;
        }

        return $analytics;
    }

    
    // CALCULATE INDIVIDUAL GOAL PROGRESS WITH TIME SCOPE
    
    private function calculateGoalProgress($userId, $activityType, $targets, $durationOption, $goalCreatedAt) {
        $totalProgress = 0;
        $targetCount = 0;

        foreach ($targets as $target) {
            $metricKey = strtolower($target['metric_key'] ?? '');
            $targetValue = floatval($target['value'] ?? 0);
            $unit = strtolower($target['unit'] ?? '');

            if ($targetValue <= 0) continue;

            $actualValue = $this->getBestPerformance($userId, $activityType, $metricKey, $durationOption, $goalCreatedAt);
            $progress = $this->calculateMetricProgress($actualValue, $targetValue, $metricKey);
            
            error_log("    Metric $metricKey: actual=$actualValue, target=$targetValue, progress=$progress%");
            
            $totalProgress += $progress;
            $targetCount++;
        }

        return $targetCount > 0 ? round($totalProgress / $targetCount) : 0;
    }

   
    // GET BEST PERFORMANCE FROM LOGGED ACTIVITIES WITH TIME SCOPE

    private function getBestPerformance($userId, $activityType, $metricKey, $durationOption, $goalCreatedAt) {
        $tableMap = [
            'running' => 'running_activities',
            'cycling' => 'cycling_activities', 
            'weightlifting' => 'weightlifting_activities',
            'yoga' => 'yoga_activities',
            'swimming' => 'swimming_activities',
            'walking' => 'walking_activities'
        ];

        // ENHANCED COLUMN MAPPING  FIXED FOR GOAL METRICS
        $columnMap = [
            // Direct mappings for common goal metric patterns
            'target_time_minutes' => ['running' => 'time_minutes', 'cycling' => 'time_minutes', 'walking' => 'time_minutes', 'swimming' => 'time_minutes', 'yoga' => 'duration_minutes', 'weightlifting' => 'time_minutes'],
            'target_duration_minutes' => ['running' => 'time_minutes', 'cycling' => 'time_minutes', 'walking' => 'time_minutes', 'swimming' => 'time_minutes', 'yoga' => 'duration_minutes', 'weightlifting' => 'time_minutes'],
            'target_distance_km' => ['running' => 'distance_km', 'cycling' => 'distance', 'walking' => 'distance_km'],
            'target_distance_meters' => ['swimming' => 'distance_meters'],
            'target_speed_kmh' => ['running' => 'speed_kmh', 'cycling' => 'speed', 'walking' => 'speed_kmh'],
            'target_speed_mps' => ['swimming' => 'speed_mps'],
            'target_weight_kg' => ['weightlifting' => 'weight_kg'],
            'target_reps' => ['weightlifting' => 'reps'],
            'target_sets' => ['weightlifting' => 'sets'],
            'target_calories' => ['running' => 'calories_burned', 'cycling' => 'calories', 'walking' => 'calories_burned', 'swimming' => 'calories_burned', 'yoga' => 'calories', 'weightlifting' => 'calories'],
            
            // Generic fallbacks (strip "target_" prefix)
            'time_minutes' => ['running' => 'time_minutes', 'cycling' => 'time_minutes', 'walking' => 'time_minutes', 'swimming' => 'time_minutes', 'yoga' => 'duration_minutes', 'weightlifting' => 'time_minutes'],
            'duration' => ['running' => 'time_minutes', 'cycling' => 'time_minutes', 'walking' => 'time_minutes', 'swimming' => 'time_minutes', 'yoga' => 'duration_minutes', 'weightlifting' => 'time_minutes'],
            'distance' => ['running' => 'distance_km', 'cycling' => 'distance', 'walking' => 'distance_km', 'swimming' => 'distance_meters'],
            'speed' => ['running' => 'speed_kmh', 'cycling' => 'speed', 'walking' => 'speed_kmh', 'swimming' => 'speed_mps'],
            'weight' => ['weightlifting' => 'weight_kg'],
            'reps' => ['weightlifting' => 'reps'],
            'sets' => ['weightlifting' => 'sets'],
            'calories' => ['running' => 'calories_burned', 'cycling' => 'calories', 'walking' => 'calories_burned', 'swimming' => 'calories_burned', 'yoga' => 'calories', 'weightlifting' => 'calories']
        ];

        $table = $tableMap[$activityType] ?? '';
        
    
        
        $column = $columnMap[$metricKey][$activityType] 
                ?? $columnMap[$this->stripTargetPrefix($metricKey)][$activityType] 
                ?? $columnMap[$this->extractGenericMetric($metricKey)][$activityType] 
                ?? '';

        // DEBUG: Enhanced logging
        error_log("=== PERFORMANCE LOOKUP ===");
        error_log("Activity: $activityType");
        error_log("Metric Key: $metricKey");
        error_log("Table: $table");
        error_log("Column: " . ($column ?: 'NOT FOUND'));
        error_log("Duration Option: $durationOption");
        error_log("Goal Created: $goalCreatedAt");
        error_log("Stripped Prefix: " . $this->stripTargetPrefix($metricKey));
        error_log("Generic Metric: " . $this->extractGenericMetric($metricKey));

        if (empty($table) || empty($column)) {
            error_log("âŒ No matching table or column found!");
            return 0;
        }

        try {
            // Calculate time window based on duration option
            $timeWindow = $this->calculateTimeWindow($durationOption, $goalCreatedAt);
            $startDate = $timeWindow['start'];
            $endDate = $timeWindow['end'];
            
            error_log("Time Window: $startDate to $endDate");

            $sql = "SELECT MAX($column) as best_value FROM $table WHERE user_id = ? AND $column > 0 AND created_at BETWEEN ? AND ?";
            $stmt = $this->con->prepare($sql);
            if (!$stmt) {
                error_log("âŒ Failed to prepare SQL statement");
                return 0;
            }
            
            $stmt->bind_param("iss", $userId, $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $bestValue = floatval($result['best_value'] ?? 0);
            $stmt->close();
            
            error_log("âœ… Found best value: $bestValue (within time window)");
            return $bestValue;
        } catch (Exception $e) {
            error_log("âŒ Error in getBestPerformance: " . $e->getMessage());
            return 0;
        }
    }

    // -------------------------
    // CALCULATE TIME WINDOW FOR GOALS
    // -------------------------
    private function calculateTimeWindow($durationOption, $goalCreatedAt) {
        $createdDate = date('Y-m-d H:i:s', strtotime($goalCreatedAt));
        $startDate = $createdDate;
        
        switch ($durationOption) {
            case 'Today':
                $endDate = date('Y-m-d 23:59:59', strtotime($createdDate));
                break;
                
            case 'In two weeks':
                $endDate = date('Y-m-d 23:59:59', strtotime($createdDate . ' +14 days'));
                break;
                
            case 'In three months':
                $endDate = date('Y-m-d 23:59:59', strtotime($createdDate . ' +90 days'));
                break;
                
            default:
                // Default to today if unknown duration
                $endDate = date('Y-m-d 23:59:59', strtotime($createdDate));
                break;
        }
        
        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    // Helper to strip "target_" prefix
    private function stripTargetPrefix($metricKey) {
        if (strpos($metricKey, 'target_') === 0) {
            return substr($metricKey, 7); // Remove "target_" prefix
        }
        return $metricKey;
    }

    // Improved helper method to extract generic metric
    private function extractGenericMetric($metricKey) {
        $cleanKey = $this->stripTargetPrefix($metricKey);
        $parts = explode('_', $cleanKey);
        if (count($parts) > 1) {
            return end($parts); // Returns "minutes" from "time_minutes"
        }
        return $cleanKey; // Return original if no underscore
    }

    
    // CALCULATE PROGRESS FOR A SPECIFIC METRIC
  
    private function calculateMetricProgress($actualValue, $targetValue, $metricKey) {
        if ($actualValue <= 0) return 0;
        if ($targetValue <= 0) return 0;

        $progress = ($actualValue / $targetValue) * 100;
        
        
        return min(100, round($progress));
    }

    
    // ACTIVITY DISTRIBUTION 
    public function getActivityDistribution($userId) {
        $distribution = array();

        try {
            $tables = [
                'running_activities',
                'cycling_activities',
                'weightlifting_activities',
                'yoga_activities',
                'swimming_activities',
                'walking_activities'
            ];

            $totalActivities = 0;
            $counts = array();

            foreach ($tables as $table) {
                $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM $table WHERE user_id = ?");
                if (!$stmt) {
                    $counts[$table] = 0;
                    continue;
                }
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $count = intval($result['count'] ?? 0);
                $counts[$table] = $count;
                $totalActivities += $count;
                $stmt->close();
            }

            if ($totalActivities > 0) {
                $distribution['running'] = round(($counts['running_activities'] / $totalActivities) * 100);
                $distribution['cycling'] = round(($counts['cycling_activities'] / $totalActivities) * 100);
                $distribution['weightlifting'] = round(($counts['weightlifting_activities'] / $totalActivities) * 100);
                $distribution['yoga'] = round(($counts['yoga_activities'] / $totalActivities) * 100);
                $distribution['swimming'] = round(($counts['swimming_activities'] / $totalActivities) * 100);
                $distribution['walking'] = round(($counts['walking_activities'] / $totalActivities) * 100);
            } else {
                // all zeros to signal no data
                $distribution['running'] = 0;
                $distribution['cycling'] = 0;
                $distribution['weightlifting'] = 0;
                $distribution['yoga'] = 0;
                $distribution['swimming'] = 0;
                $distribution['walking'] = 0;
            }

        } catch (Exception $e) {
            $distribution['running'] = 0;
            $distribution['cycling'] = 0;
            $distribution['weightlifting'] = 0;
            $distribution['yoga'] = 0;
            $distribution['swimming'] = 0;
            $distribution['walking'] = 0;
        }

        return $distribution;
    }

   
    // CURRENT STREAK 

    public function calculateCurrentStreak($userId) {
        try {
            $sql = "
                SELECT COUNT(DISTINCT DATE(created_at)) as active_days 
                FROM (
                    SELECT created_at FROM running_activities WHERE user_id = ?
                    UNION ALL SELECT created_at FROM cycling_activities WHERE user_id = ?
                    UNION ALL SELECT created_at FROM weightlifting_activities WHERE user_id = ?
                    UNION ALL SELECT created_at FROM yoga_activities WHERE user_id = ?
                    UNION ALL SELECT created_at FROM swimming_activities WHERE user_id = ?
                    UNION ALL SELECT created_at FROM walking_activities WHERE user_id = ?
                ) AS all_activities 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ";

            $stmt = $this->con->prepare($sql);
            if (!$stmt) {
                return 0;
            }

            // bind the same userId to each placeholder
            $stmt->bind_param("iiiiii", $userId, $userId, $userId, $userId, $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return intval($result['active_days'] ?? 0);

        } catch (Exception $e) {
            return 0; // default streak zero to indicate no data
        }
    }
  
public function getCurrentMonthCaloriesData($userId) {
    $response = array();
    
    try {
        // Get current month range
        $currentMonthStart = date('Y-m-01'); // First day of current month
        $currentMonthEnd = date('Y-m-t');    // Last day of current month
        $currentMonthLabel = date('F Y');    // e.g., "November 2024"
        
        // Initialize monthly data for all activity types
        $caloriesData = array(
            'running' => 0,
            'cycling' => 0,
            'weightlifting' => 0,
            'yoga' => 0,
            'swimming' => 0,
            'walking' => 0
        );
        
        // Running calories - current month only
        $stmt = $this->con->prepare("
            SELECT COALESCE(SUM(calories_burned), 0) as total_calories 
            FROM running_activities 
            WHERE user_id = ? AND created_at BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $userId, $currentMonthStart, $currentMonthEnd);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $caloriesData['running'] = floatval($result['total_calories'] ?? 0);
        $stmt->close();
        
        // Cycling calories - current month only
        $stmt = $this->con->prepare("
            SELECT COALESCE(SUM(calories), 0) as total_calories 
            FROM cycling_activities 
            WHERE user_id = ? AND created_at BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $userId, $currentMonthStart, $currentMonthEnd);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $caloriesData['cycling'] = floatval($result['total_calories'] ?? 0);
        $stmt->close();
        
        // Weightlifting calories - current month only
        $stmt = $this->con->prepare("
            SELECT COALESCE(SUM(calories), 0) as total_calories 
            FROM weightlifting_activities 
            WHERE user_id = ? AND created_at BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $userId, $currentMonthStart, $currentMonthEnd);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $caloriesData['weightlifting'] = floatval($result['total_calories'] ?? 0);
        $stmt->close();
        
        // Yoga calories - current month only
        $stmt = $this->con->prepare("
            SELECT COALESCE(SUM(calories), 0) as total_calories 
            FROM yoga_activities 
            WHERE user_id = ? AND created_at BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $userId, $currentMonthStart, $currentMonthEnd);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $caloriesData['yoga'] = floatval($result['total_calories'] ?? 0);
        $stmt->close();
        
        // Swimming calories - current month only
        $stmt = $this->con->prepare("
            SELECT COALESCE(SUM(calories_burned), 0) as total_calories 
            FROM swimming_activities 
            WHERE user_id = ? AND created_at BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $userId, $currentMonthStart, $currentMonthEnd);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $caloriesData['swimming'] = floatval($result['total_calories'] ?? 0);
        $stmt->close();
        
        // Walking calories - current month only
        $stmt = $this->con->prepare("
            SELECT COALESCE(SUM(calories_burned), 0) as total_calories 
            FROM walking_activities 
            WHERE user_id = ? AND created_at BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $userId, $currentMonthStart, $currentMonthEnd);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $caloriesData['walking'] = floatval($result['total_calories'] ?? 0);
        $stmt->close();
        
        $response['error'] = false;
        $response['current_month'] = $currentMonthLabel;
        $response['calories_data'] = $caloriesData;
        
    } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = "Error fetching current month calories data: " . $e->getMessage();
    }
    
    return $response;
}

//user profile stats
public function getUserProfileStats($userId) {
    $response = array();
    
    try {
        // Get total activities count
        $totalActivities = $this->getTotalActivitiesCount($userId);
        
        // Get total goals count
        $totalGoals = $this->getTotalGoalsCount($userId);
        
        $response['error'] = false;
        $response['total_activities'] = $totalActivities;
        $response['total_goals'] = $totalGoals;
        
    } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = "Error fetching profile stats: " . $e->getMessage();
    }
    
    return $response;
}

private function getTotalActivitiesCount($userId) {
    $tables = [
        'running_activities',
        'cycling_activities', 
        'weightlifting_activities',
        'yoga_activities',
        'swimming_activities',
        'walking_activities'
    ];
    
    $totalCount = 0;
    
    foreach ($tables as $table) {
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM $table WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $totalCount += intval($result['count'] ?? 0);
            $stmt->close();
        }
    }
    
    return $totalCount;
}

private function getTotalGoalsCount($userId) {
    $sql = "SELECT COUNT(*) as total_goals FROM goals WHERE user_id = ?";
    $stmt = $this->con->prepare($sql);
    
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return intval($result['total_goals'] ?? 0);
}

// -------------------------
// GET ALL ACTIVITIES (combined from all tables)
// -------------------------
public function getAllActivities($userId) {
    $response = array();

    try {
        $activities = array();

        // Running activities
        $stmt = $this->con->prepare("
            SELECT id, 'running' as activity_type, distance_km, time_minutes, weather, 
                   speed_kmh, calories_burned as calories, note, created_at 
            FROM running_activities 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Cycling activities
        $stmt = $this->con->prepare("
            SELECT id, 'cycling' as activity_type, distance as distance_km, time_minutes, weather, 
                   speed as speed_kmh, calories, note, created_at 
            FROM cycling_activities 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Walking activities
        $stmt = $this->con->prepare("
            SELECT id, 'walking' as activity_type, distance_km, time_minutes, weather, 
                   speed_kmh, calories_burned as calories, note, created_at 
            FROM walking_activities 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Swimming activities
        $stmt = $this->con->prepare("
            SELECT id, 'swimming' as activity_type, distance_meters, time_minutes, weather, 
                   stroke_type, speed_mps, calories_burned as calories, note, created_at 
            FROM swimming_activities 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Yoga activities
        $stmt = $this->con->prepare("
            SELECT id, 'yoga' as activity_type, session_type, duration_minutes as time_minutes, 
                   intensity, calories, note, created_at 
            FROM yoga_activities 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Weightlifting activities
        $stmt = $this->con->prepare("
            SELECT id, 'weightlifting' as activity_type, exercise_name, sets, reps, weight_kg, 
                   time_minutes, calories, note, created_at 
            FROM weightlifting_activities 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Sort all activities by date (newest first)
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        $response['error'] = false;
        $response['activities'] = $activities;

    } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = "Error fetching activities: " . $e->getMessage();
    }

    return $response;
}


// DELETE ACTIVITY
public function deleteActivity($userId, $activityId, $activityType) {
    $response = array();

    try {
        $tableMap = [
            'running' => 'running_activities',
            'cycling' => 'cycling_activities',
            'walking' => 'walking_activities',
            'swimming' => 'swimming_activities',
            'yoga' => 'yoga_activities',
            'weightlifting' => 'weightlifting_activities'
        ];

        $table = $tableMap[$activityType] ?? '';

        if (empty($table)) {
            throw new Exception("Invalid activity type");
        }

        $sql = "DELETE FROM $table WHERE id = ? AND user_id = ?";
        $stmt = $this->con->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->con->error);
        }

        $stmt->bind_param("ii", $activityId, $userId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['error'] = false;
                $response['message'] = "Activity deleted successfully";
            } else {
                $response['error'] = true;
                $response['message'] = "Activity not found or you don't have permission";
            }
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = "Error deleting activity: " . $e->getMessage();
    }

    return $response;
}

// DELETE GOAL
public function deleteGoal($userId, $goalId) {
    $response = array();

    try {
        $this->con->begin_transaction();

        // Verify ownership first
        $stmt = $this->con->prepare("SELECT id FROM goals WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for ownership check: " . $this->con->error);
        }

        $stmt->bind_param("ii", $goalId, $userId);
        $stmt->execute();
        $ownershipResult = $stmt->get_result();
        $stmt->close();

        if ($ownershipResult->num_rows === 0) {
            $this->con->rollback();
            $response['error'] = true;
            $response['message'] = "Goal not found or you don't have permission";
            return $response;
        }

        // Delete goal (goal_targets are removed by FK ON DELETE CASCADE)
        $stmt = $this->con->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for goals: " . $this->con->error);
        }

        $stmt->bind_param("ii", $goalId, $userId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $this->con->commit();
                $response['error'] = false;
                $response['message'] = "Goal deleted successfully";
            } else {
                $this->con->rollback();
                $response['error'] = true;
                $response['message'] = "Goal not found or you don't have permission";
            }
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        $this->con->rollback();
        $response['error'] = true;
        $response['message'] = "Error deleting goal: " . $e->getMessage();
    }

    return $response;
}

}
?>