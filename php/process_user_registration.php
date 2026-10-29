<?php
// ========================================================
// Process User Registration - Secure MySQLi Implementation
// StudentHub - Object-Oriented MySQLi with Prepared Statements
// ========================================================

require "db_connect_mysqli.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // 1. Server-side validation
    if (empty($name) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    if (strlen($password) < 6) {
        die("Password must contain at least 6 characters.");
    }

    // 2. Check duplicate email using prepared statement
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        die("This email is already registered.");
    }
    $check_stmt->close();

    // 3. Hash password using bcrypt / PASSWORD_DEFAULT
    $hashed_password = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // 4. Insert user using MySQLi prepared statements
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param(
        "sss",
        $name,
        $email,
        $hashed_password
    );

    if ($stmt->execute()) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Registration Successful - StudentHub</title>
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
                <section class="registration-card" style="max-width: 600px; text-align: center;">
                    <div class="form-alert alert-success" style="display: block;">
                        <h2 style="margin: 0 0 10px 0; color: var(--success-color);">Registration Successful!</h2>
                        <p style="margin: 0;">User <strong><?php echo htmlspecialchars($name); ?></strong> has been registered securely using MySQLi.</p>
                        <p style="font-size: 13px; opacity: 0.9; margin-top: 8px;">Password was hashed using <code>password_hash(PASSWORD_DEFAULT)</code>.</p>
                    </div>
                    <div style="margin-top: 25px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                        <a href="../pages/login.php" class="btn-primary-submit" style="text-decoration: none; padding: 10px 20px; background-color: var(--accent-color);">
                            Go to Secure Login
                        </a>
                        <a href="../pages/register_user.html" class="btn-secondary-reset" style="text-decoration: none; padding: 10px 20px;">
                            Register Another User
                        </a>
                        <a href="../pages/index.html" class="cache-btn" style="text-decoration: none; padding: 10px 20px;">
                            Back to Home
                        </a>
                    </div>
                </section>
            </main>
            <footer>
                <p>&copy; 2026 StudentHub - Digital Campus Portal | CHARUSAT</p>
            </footer>
        </body>
        </html>
        <?php
    } else {
        echo "Registration failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
