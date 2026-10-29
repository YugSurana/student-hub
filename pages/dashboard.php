<?php
// ========================================================
// Practical 10: Student Dashboard (pages/dashboard.php)
// Features: Protected dashboard access, session inspection,
// session timeout tracking, and RBAC demonstration.
// ========================================================

require_once __DIR__ . '/../php/auth.php';

// Enforce login and session timeout (accepts logged-in student or admin)
require_auth();

$userId      = $_SESSION['user_id'];
$userName    = $_SESSION['user_name'];
$userEmail   = $_SESSION['user_email'];
$userRole    = $_SESSION['user_role'];
$loginTime   = $_SESSION['login_time'] ?? time();
$lastActivity = $_SESSION['last_activity'] ?? time();
$currentSessionId = session_id();

// Fetch Intermediate Extension data: Last Login from Database
$stmt = $pdo->prepare("SELECT last_login, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userRecord = $stmt->fetch();
$lastLogin = $userRecord['last_login'] ?? 'Just now';
$memberSince = $userRecord['created_at'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Dashboard (Practical 10)</title>
    <link rel="icon" type="image/png" href="../docs/sitemap.png">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .security-badge {
            background-color: var(--success-bg);
            color: var(--success-color);
            border: 1px solid var(--success-color);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .inspection-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            margin-top: 10px;
        }
        .inspection-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border-color);
        }
        .inspection-table td:first-child {
            font-weight: 600;
            width: 40%;
            color: var(--text-color);
            opacity: 0.85;
        }
        .role-tag {
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .role-student { background-color: #4da6ff; color: #fff; }
        .role-admin { background-color: #ffd700; color: #333; }
        .timer-box {
            background-color: var(--button-bg);
            border-radius: 6px;
            padding: 8px 14px;
            display: inline-block;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            color: var(--accent-color);
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
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="events.html">Events</a>
        <a href="register.html">Event Register</a>
        <?php if ($userRole === 'admin'): ?>
            <a href="admin_dashboard.php">Admin Portal</a>
        <?php endif; ?>
        <a href="about.html">About</a>
        <a href="faq.html">FAQ</a>
        <a href="../php/logout.php" style="color: #ff9999; font-weight: bold;">Logout (<?php echo htmlspecialchars($userRole); ?>)</a>
    </nav>

    <!-- Action Bar / Theme Switcher -->
    <div class="action-bar-container">
        <div class="action-bar">
            <button id="theme-toggle" class="action-btn">
                Dark Mode
            </button>
            <span style="font-size: 13.5px; margin-left: 10px;">
                Logged in as: <strong><?php echo htmlspecialchars($userName); ?></strong>
            </span>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        <!-- Welcome Banner -->
        <section class="welcome-card" style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0 0 5px 0;">Welcome, <?php echo htmlspecialchars($userName); ?>! 👋</h2>
                    <p style="margin: 0; opacity: 0.85; font-size: 14px;">
                        Digital Campus Portal &bull; Role: 
                        <span class="role-tag <?php echo $userRole === 'admin' ? 'role-admin' : 'role-student'; ?>">
                            <?php echo htmlspecialchars($userRole); ?>
                        </span>
                    </p>
                </div>
                <div>
                    <span class="security-badge">
                        🔒 Session Active &amp; Regenerated
                    </span>
                </div>
            </div>
        </section>

        <!-- Dashboard Grid Cards -->
        <div class="dashboard-grid">
            <!-- Card 1: User Profile & Intermediate Extension -->
            <section class="registration-card" style="margin: 0;">
                <h3 style="margin-top: 0; color: var(--accent-color);">👤 Profile Information</h3>
                <p style="font-size: 13px; opacity: 0.8; margin-top: -5px;">Account details stored in MySQL</p>

                <table class="inspection-table">
                    <tr>
                        <td>Full Name:</td>
                        <td><?php echo htmlspecialchars($userName); ?></td>
                    </tr>
                    <tr>
                        <td>Email Address:</td>
                        <td><?php echo htmlspecialchars($userEmail); ?></td>
                    </tr>
                    <tr>
                        <td>Assigned Role:</td>
                        <td>
                            <span class="role-tag role-student"><?php echo htmlspecialchars($userRole); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>Last Login (DB):</td>
                        <td>
                            <strong><?php echo htmlspecialchars($lastLogin); ?></strong>
                            <div style="font-size: 11px; color: var(--accent-color);">(Intermediate Extension)</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Registered Date:</td>
                        <td><?php echo htmlspecialchars($memberSince); ?></td>
                    </tr>
                </table>
            </section>

            <!-- Card 2: Practical 10 Session Security Inspection -->
            <section class="registration-card" style="margin: 0;">
                <h3 style="margin-top: 0; color: var(--accent-color);">🛡 Practical 10: Session Inspection</h3>
                <p style="font-size: 13px; opacity: 0.8; margin-top: -5px;">Live PHP session management state</p>

                <table class="inspection-table">
                    <tr>
                        <td>Session ID:</td>
                        <td><code style="word-break: break-all;"><?php echo htmlspecialchars($currentSessionId); ?></code></td>
                    </tr>
                    <tr>
                        <td>ID Regeneration:</td>
                        <td><span style="color: var(--success-color); font-weight: bold;">✅ session_regenerate_id(true)</span></td>
                    </tr>
                    <tr>
                        <td>Cookie Flags:</td>
                        <td><code>HttpOnly: Yes</code>, <code>SameSite: Lax</code></td>
                    </tr>
                    <tr>
                        <td>Session Started:</td>
                        <td><?php echo date('H:i:s d-M-Y', $loginTime); ?></td>
                    </tr>
                    <tr>
                        <td>Inactivity Timeout:</td>
                        <td>
                            <span class="timer-box" id="timeoutCountdown">15:00</span>
                            <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">Auto-logout after 15 min inactivity</div>
                        </td>
                    </tr>
                </table>
            </section>
        </div>

        <!-- Card 3: Post Laboratory Work & Viva Actions -->
        <section class="registration-card" style="margin-top: 20px;">
            <h3 style="margin-top: 0; color: var(--accent-color);">🧪 Practical 10 Evaluation &amp; Screenshot Triggers</h3>
            <p style="font-size: 13.5px; opacity: 0.85; margin: 0 0 15px 0;">
                Use the quick buttons below to demonstrate the key security features required for lab submission and viva evaluation:
            </p>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <!-- 1. Test Inactivity Timeout -->
                <a href="dashboard.php?action=simulate_timeout" class="btn-secondary-reset" style="text-decoration: none; padding: 10px 16px; font-size: 13px;">
                    ⏱ Test Session Timeout (Expire Now)
                </a>

                <!-- 2. Test RBAC Unauthorized Access Attempt -->
                <a href="admin_dashboard.php" class="btn-primary-submit" style="text-decoration: none; padding: 10px 16px; font-size: 13px; background-color: #e65100;">
                    🚫 Test RBAC (Try Accessing Admin Portal)
                </a>

                <!-- 3. Logout -->
                <a href="../php/logout.php" class="btn-primary-submit" style="text-decoration: none; padding: 10px 16px; font-size: 13px; background-color: #d32f2f;">
                    🚪 Secure Logout
                </a>
            </div>
        </section>

        <!-- Card 4: Campus Quick Actions -->
        <section class="registration-card" style="margin-top: 20px;">
            <h3 style="margin-top: 0; color: var(--accent-color);">🎓 Campus Portal Services</h3>
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 10px;">
                <a href="events.html" class="cache-btn" style="text-decoration: none; padding: 10px 18px;">
                    📅 Browse Campus Events
                </a>
                <a href="register.html" class="cache-btn" style="text-decoration: none; padding: 10px 18px;">
                    ✍ Register for an Event (Practical 8)
                </a>
                <a href="faq.html" class="cache-btn" style="text-decoration: none; padding: 10px 18px;">
                    ❓ Frequently Asked Questions
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

    <!-- Live Inactivity Timeout Countdown Script -->
    <script>
        (function() {
            let remainingSeconds = 900; // 15 minutes
            const timerEl = document.getElementById('timeoutCountdown');

            function updateCountdown() {
                const minutes = Math.floor(remainingSeconds / 60);
                const seconds = remainingSeconds % 60;
                if (timerEl) {
                    timerEl.textContent = 
                        (minutes < 10 ? '0' : '') + minutes + ':' + 
                        (seconds < 10 ? '0' : '') + seconds;
                }
                if (remainingSeconds > 0) {
                    remainingSeconds--;
                } else {
                    window.location.href = "login.php?error=timeout";
                }
            }

            setInterval(updateCountdown, 1000);
            updateCountdown();
        })();
    </script>
</body>
</html>
