<?php
// ========================================================
// Practical 10: Secure Login Page (pages/login.php)
// Features: password_verify(), session_regenerate_id(),
// Role-based redirection, timeout notice, and remember-me.
// ========================================================

require_once __DIR__ . '/../php/auth.php';

start_secure_session();

// Check if already authenticated or auto-login via remember-me token
if (!empty($_SESSION['user_id']) || try_remember_me_login()) {
    if (($_SESSION['user_role'] ?? 'student') === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    } else {
        header("Location: dashboard.php");
        exit;
    }
}

$errorMessage = "";
$successMessage = "";
$infoMessage = "";

// Handle query parameter alerts
if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $successMessage = "✅ You have been logged out securely. Session destroyed and cookies cleared.";
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthorized') {
        $infoMessage = "🔒 Unauthorized Access Attempt: Please log in to access the dashboard.";
    } elseif ($_GET['error'] === 'timeout') {
        $infoMessage = "⏳ Session Expired: You were automatically logged out due to 15 minutes of inactivity.";
    } elseif ($_GET['error'] === 'invalid') {
        $errorMessage = "❌ Invalid email or password. Please verify your credentials.";
    }
}

// Process POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $rememberMe = !empty($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        $errorMessage = "Please enter both email and password.";
    } else {
        $authResult = authenticate_user($email, $password, $rememberMe);

        if ($authResult['success']) {
            // Role-based redirection
            if ($authResult['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $errorMessage = $authResult['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Secure Login (Practical 10)</title>
    <link rel="icon" type="image/png" href="../docs/sitemap.png">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .credentials-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .credentials-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px dashed var(--border-color);
        }
        .credentials-row:last-child {
            border-bottom: none;
        }
        .badge-role {
            font-size: 11px;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-admin { background-color: #ffd700; color: #333; }
        .badge-student { background-color: #4da6ff; color: #fff; }
        .btn-fill-credential {
            background: var(--button-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 3px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-fill-credential:hover {
            background: var(--accent-color);
            color: #fff;
        }
        .remember-checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 15px 0 20px 0;
            font-size: 13.5px;
            cursor: pointer;
        }
        .remember-checkbox-group input {
            cursor: pointer;
            width: 16px;
            height: 16px;
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
        <a href="index.html">Home</a>
        <a href="about.html">About</a>
        <a href="register.html">Student Register</a>
        <a href="register_user.html">User Register</a>
        <a href="login.php" class="active">Login</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="events.html">Events</a>
        <a href="contact.html">Contact</a>
        <a href="admin_dashboard.php">Admin</a>
        <a href="faq.html">FAQ</a>
    </nav>

    <!-- Action Bar / Theme Switcher -->
    <div class="action-bar-container">
        <div class="action-bar">
            <button id="theme-toggle" class="action-btn">
                Dark Mode
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        <section class="registration-card" style="max-width: 540px;">
            <h2>Secure Portal Login</h2>
            <p class="form-subtitle">Practical 10: Sessions, Role-Based Access, Timeout &amp; Remember-Me</p>

            <!-- Status Alert Banners -->
            <?php if (!empty($errorMessage)): ?>
                <div class="form-alert alert-error" style="display: block; margin-bottom: 18px;">
                    <strong>Login Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="form-alert alert-success" style="display: block; margin-bottom: 18px;">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($infoMessage)): ?>
                <div class="form-alert" style="display: block; margin-bottom: 18px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;">
                    <strong>Security Notice:</strong> <?php echo htmlspecialchars($infoMessage); ?>
                </div>
            <?php endif; ?>

            <!-- Seeded Test Accounts Quick Card -->
            <div class="credentials-card">
                <div style="font-weight: bold; margin-bottom: 8px; color: var(--accent-color);">
                    🔑 Seeded Test Accounts (Practical 10 Dataset):
                </div>
                <div class="credentials-row">
                    <div>
                        <span class="badge-role badge-admin">Admin</span>
                        <strong>admin@studenthub.com</strong>
                        <span style="opacity: 0.75;">| Pass: Admin@123</span>
                    </div>
                    <button type="button" class="btn-fill-credential" onclick="fillCredentials('admin@studenthub.com', 'Admin@123')">
                        Auto-Fill
                    </button>
                </div>
                <div class="credentials-row">
                    <div>
                        <span class="badge-role badge-student">Student</span>
                        <strong>student@studenthub.com</strong>
                        <span style="opacity: 0.75;">| Pass: Student@123</span>
                    </div>
                    <button type="button" class="btn-fill-credential" onclick="fillCredentials('student@studenthub.com', 'Student@123')">
                        Auto-Fill
                    </button>
                </div>
            </div>

            <!-- Login Form -->
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="loginEmail" class="form-label">
                        Email Address: <span class="required-star">*</span>
                    </label>
                    <input
                        type="email"
                        id="loginEmail"
                        name="email"
                        class="form-control"
                        placeholder="e.g., student@studenthub.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required
                        autofocus
                        autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="loginPassword" class="form-label">
                        Password: <span class="required-star">*</span>
                    </label>
                    <input
                        type="password"
                        id="loginPassword"
                        name="password"
                        class="form-control"
                        placeholder="Enter your account password"
                        required
                        autocomplete="current-password">
                </div>

                <!-- Advanced Extension: Remember-Me Checkbox -->
                <label class="remember-checkbox-group">
                    <input type="checkbox" name="remember_me" id="rememberMe" value="1">
                    <span><strong>Remember Me</strong> on this device (Stores secure SHA-256 random token in DB)</span>
                </label>

                <div class="form-buttons-row">
                    <button type="submit" class="btn-primary-submit" style="width: 100%;">
                        🔒 Secure Login
                    </button>
                </div>
            </form>

            <div style="margin-top: 22px; text-align: center; font-size: 13.5px;">
                <span>Don't have an account yet? </span>
                <a href="register_user.html" style="color: var(--accent-color); font-weight: bold; text-decoration: none;">
                    Register here (Practical 9)
                </a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 StudentHub - Digital Campus Portal | CHARUSAT ITUE203</p>
    </footer>

    <!-- Shared Portal Scripts -->
    <script src="../js/script.js"></script>

    <script>
        function fillCredentials(email, password) {
            document.getElementById('loginEmail').value = email;
            document.getElementById('loginPassword').value = password;
        }
    </script>
</body>
</html>
