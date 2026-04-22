<?php
    class dbconnect{
        private $con;

        function __construct(){
            require_once dirname(__FILE__).'/constants.php';
            $this->con = $this->connect();
        }

        function connect(){
            include_once dirname(__FILE__).'/constants.php';

            $driver = defined('DB_DRIVER') ? DB_DRIVER : 'pgsql';

            if ($driver === 'pgsql') {
                try {
                    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
                    $port = defined('DB_PORT') ? DB_PORT : '5432';
                    $user = defined('DB_USER') ? DB_USER : '';
                    $pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
                    $name = defined('DB_NAME') ? DB_NAME : '';

                    $sslMode = getenv('DB_SSLMODE') ?: 'require';
                    $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode={$sslMode}";

                    $pdo = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);

                    $this->initializeSchema($pdo);

                    $this->con = new MysqliCompat($pdo);
                    return $this->con;
                } catch (Throwable $e) {
                    echo "Failed to connect to PostgreSQL: " . $e->getMessage();
                    return null;
                }
            }

            // Fallback for local MySQL setup
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $user = defined('DB_USER') ? DB_USER : '';
            $pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
            $name = defined('DB_NAME') ? DB_NAME : '';
            $socket = defined('DB_SOCKET') ? DB_SOCKET : null;

            $this->con = new mysqli($host, $user, $pass, $name, 0, $socket);

            if ($this->con->connect_errno) {
                echo "Failed to connect to MySQL: " . $this->con->connect_error;
            }
            return $this->con;
        }

        private function initializeSchema(PDO $pdo) {
            $queries = [
                "CREATE TABLE IF NOT EXISTS train_up_users (
                    id SERIAL PRIMARY KEY,
                    username VARCHAR(100) NOT NULL UNIQUE,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    gender VARCHAR(20),
                    password VARCHAR(255) NOT NULL,
                    weight DOUBLE PRECISION,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS running_activities (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL REFERENCES train_up_users(id) ON DELETE CASCADE,
                    distance_km DOUBLE PRECISION NOT NULL,
                    time_minutes DOUBLE PRECISION NOT NULL,
                    weather VARCHAR(60),
                    speed_kmh DOUBLE PRECISION,
                    calories_burned DOUBLE PRECISION,
                    note TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS cycling_activities (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL REFERENCES train_up_users(id) ON DELETE CASCADE,
                    distance DOUBLE PRECISION NOT NULL,
                    time_minutes DOUBLE PRECISION NOT NULL,
                    weather VARCHAR(60),
                    bike_type VARCHAR(60),
                    speed DOUBLE PRECISION,
                    calories DOUBLE PRECISION,
                    note TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS weightlifting_activities (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL REFERENCES train_up_users(id) ON DELETE CASCADE,
                    exercise_name VARCHAR(120),
                    sets INTEGER,
                    reps INTEGER,
                    weight_kg DOUBLE PRECISION,
                    time_minutes DOUBLE PRECISION,
                    calories DOUBLE PRECISION,
                    note TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS yoga_activities (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL REFERENCES train_up_users(id) ON DELETE CASCADE,
                    session_type VARCHAR(120),
                    duration_minutes DOUBLE PRECISION,
                    intensity VARCHAR(60),
                    calories DOUBLE PRECISION,
                    note TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS swimming_activities (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL REFERENCES train_up_users(id) ON DELETE CASCADE,
                    distance_meters DOUBLE PRECISION NOT NULL,
                    time_minutes DOUBLE PRECISION NOT NULL,
                    weather VARCHAR(60),
                    stroke_type VARCHAR(60),
                    speed_mps DOUBLE PRECISION,
                    calories_burned DOUBLE PRECISION,
                    note TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS walking_activities (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL REFERENCES train_up_users(id) ON DELETE CASCADE,
                    distance_km DOUBLE PRECISION NOT NULL,
                    time_minutes DOUBLE PRECISION NOT NULL,
                    weather VARCHAR(60),
                    speed_kmh DOUBLE PRECISION,
                    calories_burned DOUBLE PRECISION,
                    note TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS goals (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL REFERENCES train_up_users(id) ON DELETE CASCADE,
                    activity_type VARCHAR(50) NOT NULL,
                    duration_option VARCHAR(50),
                    notes TEXT,
                    status VARCHAR(20) DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS goal_targets (
                    id SERIAL PRIMARY KEY,
                    goal_id INTEGER NOT NULL REFERENCES goals(id) ON DELETE CASCADE,
                    metric_key VARCHAR(100) NOT NULL,
                    value DOUBLE PRECISION NOT NULL,
                    unit VARCHAR(30),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE INDEX IF NOT EXISTS idx_running_user_id ON running_activities(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_cycling_user_id ON cycling_activities(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_weightlifting_user_id ON weightlifting_activities(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_yoga_user_id ON yoga_activities(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_swimming_user_id ON swimming_activities(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_walking_user_id ON walking_activities(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_goals_user_id ON goals(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_goal_targets_goal_id ON goal_targets(goal_id)",
            ];

            foreach ($queries as $query) {
                $pdo->exec($query);
            }
        }
    }

    class MysqliCompat {
        public $error = '';
        public $insert_id = 0;

        private $pdo;

        public function __construct(PDO $pdo) {
            $this->pdo = $pdo;
        }

        public function prepare($sql) {
            try {
                $sql = $this->rewriteSql($sql);
                $stmt = $this->pdo->prepare($sql);
                return new MysqliStmtCompat($this, $stmt);
            } catch (Throwable $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function begin_transaction() {
            return $this->pdo->beginTransaction();
        }

        public function commit() {
            return $this->pdo->commit();
        }

        public function rollback() {
            if ($this->pdo->inTransaction()) {
                return $this->pdo->rollBack();
            }
            return true;
        }

        public function refreshInsertId() {
            try {
                $id = $this->pdo->query("SELECT LASTVAL()")?->fetchColumn();
                if ($id !== false && $id !== null) {
                    $this->insert_id = (int)$id;
                }
            } catch (Throwable $e) {
                // no-op
            }
        }

        private function rewriteSql($sql) {
            // MySQL -> PostgreSQL compatibility for streak query.
            $sql = str_replace(
                "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
                "created_at >= (CURRENT_DATE - INTERVAL '30 days')",
                $sql
            );
            return $sql;
        }
    }

    class MysqliStmtCompat {
        public $error = '';
        public $num_rows = 0;
        public $affected_rows = 0;

        private $conn;
        private $stmt;
        private $types = '';
        private $boundParams = [];
        private $boundResults = [];
        private $rows = [];
        private $cursor = 0;

        public function __construct(MysqliCompat $conn, PDOStatement $stmt) {
            $this->conn = $conn;
            $this->stmt = $stmt;
        }

        public function bind_param($types, &...$vars) {
            $this->types = (string)$types;
            $this->boundParams = [];
            foreach ($vars as &$var) {
                $this->boundParams[] = &$var;
            }
            return true;
        }

        public function execute() {
            try {
                $params = [];
                foreach ($this->boundParams as $idx => $valueRef) {
                    $type = $this->types[$idx] ?? 's';
                    $value = $valueRef;
                    if ($type === 'i') {
                        $value = (int)$value;
                    } elseif ($type === 'd') {
                        $value = (float)$value;
                    } elseif ($value === null || $value === '') {
                        $value = null;
                    } else {
                        $value = (string)$value;
                    }
                    $params[] = $value;
                }

                $ok = $this->stmt->execute($params);
                $this->affected_rows = $this->stmt->rowCount();
                $this->conn->refreshInsertId();

                $this->rows = [];
                $this->cursor = 0;
                $this->num_rows = 0;

                return $ok;
            } catch (Throwable $e) {
                $this->error = $e->getMessage();
                $this->conn->error = $e->getMessage();
                return false;
            }
        }

        public function store_result() {
            $this->rows = $this->stmt->fetchAll(PDO::FETCH_NUM);
            $this->num_rows = count($this->rows);
            $this->cursor = 0;
            return true;
        }

        public function bind_result(&...$vars) {
            $this->boundResults = [];
            foreach ($vars as &$var) {
                $this->boundResults[] = &$var;
            }
            return true;
        }

        public function fetch() {
            if (empty($this->rows)) {
                $this->store_result();
            }

            if ($this->cursor >= count($this->rows)) {
                return false;
            }

            $row = $this->rows[$this->cursor++];
            foreach ($this->boundResults as $i => &$resultVar) {
                $resultVar = $row[$i] ?? null;
            }

            return true;
        }

        public function get_result() {
            if (empty($this->rows)) {
                $this->rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->num_rows = count($this->rows);
                $this->cursor = 0;
            }
            return new MysqliResultCompat($this->rows);
        }

        public function close() {
            $this->stmt->closeCursor();
            return true;
        }
    }

    class MysqliResultCompat {
        public $num_rows = 0;

        private $rows = [];
        private $cursor = 0;

        public function __construct(array $rows) {
            $this->rows = array_values($rows);
            $this->num_rows = count($this->rows);
        }

        public function fetch_assoc() {
            if ($this->cursor >= $this->num_rows) {
                return null;
            }
            return $this->rows[$this->cursor++];
        }
    }
?>