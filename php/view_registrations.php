<?php
// ========================================================
// View Registrations - CSV & Database Records
// StudentHub - CHARUSAT ITUE203
// ========================================================

// 1. Read CSV Stored Registrations (Practical 7)
$csvFile = "../data/registrations.csv";
$csvRecords = [];

if (file_exists($csvFile)) {
    $fileHandle = fopen($csvFile, "r");
    if ($fileHandle !== false) {
        $header = fgetcsv($fileHandle); // Skip header row
        while (($row = fgetcsv($fileHandle)) !== false) {
            if (!empty($row) && count($row) >= 6) {
                $csvRecords[] = $row;
            }
        }
        fclose($fileHandle);
    }
}

// 2. Fetch Database Event Registrations (Practical 8 - with graceful fallback if DB offline)
$dbRegistrations = [];
$dbError = null;

try {
    if (file_exists("db.php")) {
        require_once "db.php";
        if (isset($pdo)) {
            $sql = "SELECT
                r.registration_id,
                s.name,
                s.email,
                e.event_name,
                e.event_date,
                e.venue,
                r.registration_date
            FROM registrations r
            INNER JOIN students s ON r.student_id = s.student_id
            INNER JOIN events e ON r.event_id = e.event_id
            ORDER BY r.registration_date DESC";

            $stmt = $pdo->query($sql);
            $dbRegistrations = $stmt->fetchAll();
        }
    }
} catch (Exception $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Registered Students &amp; Data Records</title>
    <link rel="icon" type="image/png" href="../docs/sitemap.png">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .hash-code {
            font-family: 'Courier New', monospace;
            font-size: 11.5px;
            background-color: var(--button-bg);
            color: var(--text-color);
            padding: 3px 6px;
            border-radius: 4px;
            word-break: break-all;
            display: inline-block;
            max-width: 220px;
        }
        .badge-pill {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
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
        <a href="../pages/register.html">Student Register</a>
        <a href="../pages/register_user.html">User Register</a>
        <a href="../pages/events.html">Events</a>
        <a href="view_registrations.php" class="active">View Registrations</a>
        <a href="../pages/admin.html">Admin</a>
    </nav>

    <main>
        <section class="welcome-card" style="margin-bottom: 25px;">
            <h2>Registered Students &amp; Stored Records</h2>
            <p style="margin: 0; opacity: 0.85; font-size: 14.5px;">
                Displaying form data processed by PHP and saved into <code>data/registrations.csv</code> with hashed passwords (Practical 7) and MySQL database records (Practical 8).
            </p>
        </section>

        <!-- Action Bar -->
        <div class="status-bar" style="margin-bottom: 25px;">
            <div style="font-weight: 600; font-size: 14px;">
                📁 Source: <code style="background: var(--button-bg); padding: 3px 7px; border-radius: 4px;">data/registrations.csv</code>
                <span class="badge-pill" style="background-color: var(--success-bg); color: var(--success-color); margin-left: 8px;">
                    <?php echo count($csvRecords); ?> Records Saved
                </span>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="../pages/register.html" class="btn-primary-submit" style="text-decoration: none; padding: 8px 16px; font-size: 13px;">
                    ➕ Register New Student
                </a>
                <a href="../data/registrations.csv" class="btn-secondary-reset" style="text-decoration: none; padding: 8px 16px; font-size: 13px;" download>
                    📥 Download CSV File
                </a>
            </div>
        </div>

        <!-- ========================================================
             SECTION 1: CSV Stored Registrations (Practical 7)
             ======================================================== -->
        <div class="toolbar-card" style="margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; color: var(--accent-color);">
                    📄 Stored Registrations (from CSV File)
                </h3>
                <span style="font-size: 13px; opacity: 0.8;">
                    Total: <strong><?php echo count($csvRecords); ?></strong> students registered
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: var(--card-bg); font-size: 13.5px;">
                    <thead>
                        <tr style="background-color: var(--nav-bg); color: #ffffff;">
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">#</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Full Name</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Email Address</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Mobile</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Password Hash (bcrypt)</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Course</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Year</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($csvRecords)): ?>
                            <tr>
                                <td colspan="8" style="padding: 25px; text-align: center;">No registration records found in CSV file.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($csvRecords as $index => $row): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color); font-weight: bold;">
                                        <?php echo $index + 1; ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color); font-weight: 600;">
                                        <?php echo htmlspecialchars($row[0] ?? ''); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color);">
                                        <?php echo htmlspecialchars($row[1] ?? ''); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color);">
                                        <?php echo htmlspecialchars($row[2] ?? ''); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color);">
                                        <span class="hash-code" title="<?php echo htmlspecialchars($row[3] ?? ''); ?>">
                                            <?php echo htmlspecialchars($row[3] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color); font-weight: bold; color: var(--accent-color);">
                                        <?php echo htmlspecialchars($row[4] ?? ''); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color);">
                                        Year <?php echo htmlspecialchars($row[5] ?? ''); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color); text-transform: capitalize;">
                                        <?php echo htmlspecialchars($row[6] ?? ''); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================================
             SECTION 2: Database Event Registrations (Practical 8)
             ======================================================== -->
        <?php if (!empty($dbRegistrations) || $dbError === null): ?>
        <div class="toolbar-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; color: var(--accent-color);">
                    🗄️ MySQL Database Registrations (Practical 8 INNER JOIN)
                </h3>
                <a href="register.php" class="btn-primary-submit" style="text-decoration: none; padding: 7px 14px; font-size: 13px;">
                    Register Event in Database &rarr;
                </a>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background-color: var(--card-bg); font-size: 13.5px;">
                    <thead>
                        <tr style="background-color: var(--nav-bg); color: #ffffff;">
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">ID</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Student Name</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Email</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Event Name</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Event Date</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Venue</th>
                            <th style="padding: 10px 12px; text-align: left; border: 1px solid var(--border-color);">Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dbRegistrations)): ?>
                            <tr>
                                <td colspan="7" style="padding: 20px; text-align: center;">No event registrations found in database. Start MySQL in XAMPP and run setup_database.php if needed.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dbRegistrations as $reg): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color); font-weight: bold;">
                                        <?php echo htmlspecialchars($reg["registration_id"]); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color); font-weight: 600;">
                                        <?php echo htmlspecialchars($reg["name"]); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color);">
                                        <?php echo htmlspecialchars($reg["email"]); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color); font-weight: bold; color: var(--accent-color);">
                                        <?php echo htmlspecialchars($reg["event_name"]); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color);">
                                        <?php echo htmlspecialchars($reg["event_date"]); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color);">
                                        <?php echo htmlspecialchars($reg["venue"]); ?>
                                    </td>
                                    <td style="padding: 10px 12px; border: 1px solid var(--border-color); font-size: 12.5px; opacity: 0.85;">
                                        <?php echo htmlspecialchars($reg["registration_date"]); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 25px;">
            <a href="../pages/register.html" style="color: var(--accent-color); font-weight: bold; text-decoration: none;">
                &larr; Back to Registration Form
            </a>
        </p>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub - Digital Campus Portal | CHARUSAT</p>
    </footer>

</body>
</html>