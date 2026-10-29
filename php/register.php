<?php
// ========================================
// Practical 8 - Event Registration (register.php)
// StudentHub - PDO Prepared Statements
// ========================================

require_once "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST["student_id"] ?? "";
    $event_id = $_POST["event_id"] ?? "";

    if (!empty($student_id) && !empty($event_id)) {
        try {
            $sql = "INSERT INTO registrations (student_id, event_id) VALUES (:student_id, :event_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":student_id" => $student_id,
                ":event_id"   => $event_id
            ]);
            $message = "Registration successful!";
            $messageType = "success";
        } catch (PDOException $e) {
            // Check for duplicate registration (MySQL error code 23000)
            if ($e->getCode() == 23000) {
                $message = "Registration failed: This student is already registered for this event.";
            } else {
                $message = "Registration failed: " . $e->getMessage();
            }
            $messageType = "error";
        }
    } else {
        $message = "Please provide both Student ID and Event ID.";
        $messageType = "error";
    }
}

// Fetch available students & events for quick reference
try {
    $studentsList = $pdo->query("SELECT student_id, name, department FROM students ORDER BY student_id ASC")->fetchAll();
    $eventsList = $pdo->query("SELECT event_id, event_name, event_date FROM events ORDER BY event_id ASC")->fetchAll();
} catch (PDOException $e) {
    $studentsList = [];
    $eventsList = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Event Registration (Practical 8)</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <!-- Header -->
    <header>
        <div class="heading-group">
            <h1>Student Hub</h1>
            <img src="../docs/campus.jpg" alt="StudentHub Campus" class="heading-img">
        </div>
    </header>

    <!-- Navigation Bar -->
    <nav class="nav-container">
        <a href="../pages/index.html">Home</a>
        <a href="../pages/about.html">About</a>
        <a href="../pages/register.html">Register</a>
        <a href="../pages/events.html">Events</a>
        <a href="register.php" class="active">DB Event Register</a>
        <a href="view_registrations.php">DB Registrations</a>
        <a href="../pages/admin.html">Admin</a>
    </nav>

    <main>
        <section class="registration-card" style="max-width: 680px;">
            <h2>StudentHub Event Registration</h2>
            <p class="form-subtitle">Practical 8: Database-Driven Event Registration using PHP PDO Prepared Statements</p>

            <?php if ($message): ?>
                <div class="form-alert <?php echo ($messageType === 'success') ? 'alert-success' : 'alert-error'; ?>" style="display: block;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <!-- Student ID Input -->
                <div class="form-group">
                    <label for="student_id" class="form-label">
                        Student ID: <span class="required-star">*</span>
                    </label>
                    <input
                        type="number"
                        id="student_id"
                        name="student_id"
                        class="form-control"
                        placeholder="Enter Student ID (e.g. 1, 2, 3, 4)"
                        min="1"
                        required>
                </div>

                <!-- Event ID Input -->
                <div class="form-group">
                    <label for="event_id" class="form-label">
                        Event ID: <span class="required-star">*</span>
                    </label>
                    <input
                        type="number"
                        id="event_id"
                        name="event_id"
                        class="form-control"
                        placeholder="Enter Event ID (e.g. 1, 2, 3)"
                        min="1"
                        required>
                </div>

                <div class="form-buttons-row">
                    <button type="submit" class="btn-primary-submit">
                        Register
                    </button>
                    <a href="view_registrations.php" class="btn-secondary-reset" style="text-align: center; text-decoration: none;">
                        View All Registrations
                    </a>
                </div>
            </form>

            <!-- Quick Reference Tables for Practical Viva / Testing -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 13px;">
                <h3 style="margin-top: 0; color: var(--accent-color);">🔍 Database Reference (Sample IDs)</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <strong>Students (students table):</strong>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <?php foreach ($studentsList as $s): ?>
                                <li><strong>ID <?php echo $s['student_id']; ?>:</strong> <?php echo htmlspecialchars($s['name']); ?> (<?php echo htmlspecialchars($s['department']); ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <strong>Events (events table):</strong>
                        <ul style="padding-left: 20px; margin: 6px 0;">
                            <?php foreach ($eventsList as $e): ?>
                                <li><strong>ID <?php echo $e['event_id']; ?>:</strong> <?php echo htmlspecialchars($e['event_name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <p style="text-align: center; margin-top: 25px;">
                <a href="view_registrations.php" style="color: var(--accent-color); font-weight: bold; text-decoration: none;">
                    View Stored Registrations (INNER JOIN query) &rarr;
                </a>
            </p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub - Digital Campus Portal | CHARUSAT</p>
    </footer>

</body>
</html>
