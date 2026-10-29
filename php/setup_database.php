<?php
// ========================================================
// Practical 8 - Automated Database & Seed Data Setup
// StudentHub - CHARUSAT ITUE203
// ========================================================

$host = "localhost";
$username = "root";
$password = "";

try {
    // 1. Connect to MySQL server without database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create Database & Switch
    $pdo->exec("CREATE DATABASE IF NOT EXISTS studenthub");
    $pdo->exec("USE studenthub");

    // 3. Create Tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS students (
            student_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            department VARCHAR(50) NOT NULL,
            semester INT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS events (
            event_id INT AUTO_INCREMENT PRIMARY KEY,
            event_name VARCHAR(150) NOT NULL,
            event_date DATE NOT NULL,
            venue VARCHAR(150) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS registrations (
            registration_id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            event_id INT NOT NULL,
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
            UNIQUE (student_id, event_id)
        );

        CREATE TABLE IF NOT EXISTS users (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'student',
            last_login DATETIME DEFAULT NULL,
            remember_token_hash VARCHAR(64) DEFAULT NULL,
            remember_token_expiry DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        );
    ");

    // Ensure columns exist if table was already created
    $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('last_login', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login DATETIME DEFAULT NULL AFTER role");
    }
    if (!in_array('remember_token_hash', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN remember_token_hash VARCHAR(64) DEFAULT NULL AFTER last_login");
    }
    if (!in_array('remember_token_expiry', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN remember_token_expiry DATETIME DEFAULT NULL AFTER remember_token_hash");
    }

    // 4. Insert Seed Students
    $pdo->exec("
        INSERT INTO students (name, email, department, semester) VALUES
        ('Rahul Patel', 'rahul@example.com', 'IT', 3),
        ('Priya Shah', 'priya@example.com', 'IT', 5),
        ('Amit Desai', 'amit@example.com', 'CSE', 3),
        ('Neha Patel', 'neha@example.com', 'CE', 7)
        ON DUPLICATE KEY UPDATE name=VALUES(name);
    ");

    // 5. Insert Seed Events
    $pdo->exec("
        INSERT INTO events (event_name, event_date, venue) VALUES
        ('Web Development Workshop', '2026-10-15', 'Seminar Hall'),
        ('Python Workshop', '2026-10-20', 'Lab 301'),
        ('Hackathon 2026', '2026-11-05', 'Innovation Centre')
        ON DUPLICATE KEY UPDATE event_name=VALUES(event_name);
    ");

    // 6. Insert Seed Registrations
    $pdo->exec("
        INSERT INTO registrations (student_id, event_id) VALUES
        (1, 1),
        (1, 2),
        (2, 1),
        (3, 3),
        (4, 2)
        ON DUPLICATE KEY UPDATE registration_date=CURRENT_TIMESTAMP;
    ");

    // 7. Practical 10: Seed Admin and Student Users
    $adminHash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $studentHash = password_hash('Student@123', PASSWORD_DEFAULT);

    $checkUser = $pdo->prepare("SELECT id FROM users WHERE email = ?");

    // Seed Admin
    $checkUser->execute(['admin@studenthub.com']);
    if (!$checkUser->fetch()) {
        $seedUser = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $seedUser->execute(['System Administrator', 'admin@studenthub.com', $adminHash]);
    }

    // Seed Student
    $checkUser->execute(['student@studenthub.com']);
    if (!$checkUser->fetch()) {
        $seedUser = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
        $seedUser->execute(['Demo Student', 'student@studenthub.com', $studentHash]);
    }

    $success = true;
    $msg = "Database 'studenthub', tables, and initial seed records (including Practical 10 Admin and Student users) configured successfully!";

} catch (PDOException $e) {
    $success = false;
    $msg = "Database setup error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 8 Database Setup</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="heading-group">
            <h1>Student Hub</h1>
            <img src="../docs/campus.jpg" alt="StudentHub Campus" class="heading-img">
        </div>
    </header>

    <main>
        <section class="registration-card" style="max-width: 650px; text-align: center;">
            <h2>Practical 8 Database Setup</h2>

            <?php if ($success): ?>
                <div class="form-alert alert-success" style="display: block; text-align: left;">
                    <strong>✅ Success!</strong> <?php echo htmlspecialchars($msg); ?>
                </div>

                <div style="margin-top: 25px; display: flex; flex-direction: column; gap: 12px;">
                    <a href="test_connection.php" class="btn-primary-submit" style="text-decoration: none; padding: 12px;">
                        1. Test Database Connection (test_connection.php)
                    </a>
                    <a href="register.php" class="btn-secondary-reset" style="text-decoration: none; padding: 12px;">
                        2. Register Student for Event (register.php)
                    </a>
                    <a href="view_registrations.php" class="btn-card-action" style="text-decoration: none; padding: 12px;">
                        3. View Stored Registrations (view_registrations.php)
                    </a>
                    <a href="../pages/login.php" class="btn-primary-submit" style="text-decoration: none; padding: 12px; background-color: var(--accent-color);">
                        4. Practical 10 - Secure Login Portal (pages/login.php)
                    </a>
                </div>
            <?php else: ?>
                <div class="form-alert alert-error" style="display: block; text-align: left;">
                    <strong>❌ Error:</strong> <?php echo htmlspecialchars($msg); ?>
                    <p style="margin-top: 10px; font-size: 13px;">
                        Please ensure that <strong>MySQL is running</strong> in your XAMPP Control Panel. Click the <strong>Start</strong> button next to MySQL.
                    </p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub - Digital Campus Portal | CHARUSAT</p>
    </footer>
</body>
</html>
