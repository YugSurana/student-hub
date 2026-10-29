<?php
// ========================================================
// Practical 10: Admin Dashboard (pages/admin_dashboard.php)
// Features: Strict RBAC check (admin role required),
// Session inspection, registered user management with last_login.
// ========================================================

require_once __DIR__ . '/../php/auth.php';

// Strict Role-Based Access Control: Admin role required!
// If a student or guest accesses this, access is blocked immediately!
require_auth('admin');

$adminId          = $_SESSION['user_id'];
$adminName        = $_SESSION['user_name'];
$adminEmail       = $_SESSION['user_email'];
$adminRole        = $_SESSION['user_role'];
$loginTime        = $_SESSION['login_time'] ?? time();
$currentSessionId = session_id();

// Fetch Intermediate Extension data: Admin's last login
$adminStmt = $pdo->prepare("SELECT last_login FROM users WHERE id = ?");
$adminStmt->execute([$adminId]);
$adminRecord = $adminStmt->fetch();
$lastLogin   = $adminRecord['last_login'] ?? 'Just now';

// Fetch all registered users for administration
$usersStmt = $pdo->query("SELECT id, name, email, role, last_login, created_at FROM users ORDER BY id ASC");
$allUsers  = $usersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Dashboard (Practical 10)</title>
    <link rel="icon" type="image/png" href="../docs/sitemap.png">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .badge-admin-role {
            background-color: #ffd700;
            color: #333;
            font-weight: bold;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-student-role {
            background-color: #4da6ff;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            background-color: var(--card-bg);
        }
        .admin-table th {
            background-color: var(--nav-bg);
            color: #ffffff;
            padding: 10px 12px;
            text-align: left;
            border: 1px solid var(--border-color);
        }
        .admin-table td {
            padding: 9px 12px;
            border: 1px solid var(--border-color);
        }
        .admin-table tr:hover {
            background-color: var(--button-bg);
        }
        .stat-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px 18px;
            flex: 1;
            min-width: 200px;
        }
        .stat-card .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color);
            margin-top: 4px;
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
        <a href="admin_dashboard.php" class="active">Admin Dashboard</a>
        <a href="dashboard.php">Student View</a>
        <a href="admin.html">Stored Registrations</a>
        <a href="../php/logout.php" style="color: #ff9999; font-weight: bold;">Logout (Admin)</a>
    </nav>

    <!-- Action Bar / Theme Switcher -->
    <div class="action-bar-container">
        <div class="action-bar">
            <button id="theme-toggle" class="action-btn">
                Dark Mode
            </button>
            <span style="font-size: 13.5px; margin-left: 10px;">
                Administrator: <strong><?php echo htmlspecialchars($adminName); ?></strong>
            </span>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        <!-- Welcome Banner -->
        <section class="welcome-card" style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0 0 5px 0;">Administrator Control Center 🛡️</h2>
                    <p style="margin: 0; opacity: 0.85; font-size: 14px;">
                        Practical 10 RBAC Protection: Authenticated as <span class="badge-admin-role">Administrator</span>
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="../php/logout.php" class="btn-primary-submit" style="text-decoration: none; padding: 8px 16px; background-color: #d32f2f; font-size: 13px;">
                        🚪 Secure Logout
                    </a>
                </div>
            </div>
        </section>

        <!-- Stats Overview -->
        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
            <div class="stat-card">
                <div style="font-size: 13px; opacity: 0.8;">Total Registered Users</div>
                <div class="stat-value"><?php echo count($allUsers); ?></div>
            </div>
            <div class="stat-card">
                <div style="font-size: 13px; opacity: 0.8;">Active Admin Session</div>
                <div class="stat-value" style="font-size: 15px; word-break: break-all; margin-top: 8px;">
                    <code><?php echo htmlspecialchars(substr($currentSessionId, 0, 16)) . '...'; ?></code>
                </div>
            </div>
            <div class="stat-card">
                <div style="font-size: 13px; opacity: 0.8;">Last Admin Login</div>
                <div class="stat-value" style="font-size: 15px; margin-top: 8px;">
                    <?php echo htmlspecialchars($lastLogin); ?>
                </div>
            </div>
        </div>

        <!-- Practical 10 Session Security Information -->
        <section class="registration-card" style="margin-bottom: 20px;">
            <h3 style="margin-top: 0; color: var(--accent-color);">🔒 Session &amp; RBAC Security Audit</h3>
            <p style="font-size: 13px; opacity: 0.8; margin-top: -5px;">Active session properties and defense mechanisms</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; font-size: 13.5px;">
                <div><strong>Current Session ID:</strong> <code><?php echo htmlspecialchars($currentSessionId); ?></code></div>
                <div><strong>Fixation Defense:</strong> <span style="color: var(--success-color); font-weight: bold;">session_regenerate_id(true) applied</span></div>
                <div><strong>Cookie Flags:</strong> <code>HttpOnly=1; SameSite=Lax</code></div>
                <div><strong>Timeout Guard:</strong> Inactivity expiry active (15 minutes)</div>
            </div>

            <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="admin_dashboard.php?action=simulate_timeout" class="btn-secondary-reset" style="text-decoration: none; padding: 6px 14px; font-size: 12px;">
                    ⏱ Simulate Admin Inactivity Timeout
                </a>
                <a href="admin.html" class="cache-btn" style="text-decoration: none; padding: 6px 14px; font-size: 12px;">
                    📋 Stored Registrations (Practical 7)
                </a>
            </div>
        </section>

        <!-- Registered Users & Roles Table -->
        <section class="toolbar-card">
            <h3 style="margin-top: 0; color: var(--accent-color);">👥 User Accounts &amp; Role-Based Access Table</h3>
            <p style="font-size: 13px; opacity: 0.8; margin-top: -5px;">
                Seeded users and registered accounts from MySQL <code>users</code> table, showing role and <strong>last_login</strong> timestamp.
            </p>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th># ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Assigned Role</th>
                            <th>Last Login (Intermediate)</th>
                            <th>Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUsers as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge-admin-role">ADMIN</span>
                                    <?php else: ?>
                                        <span class="badge-student-role">STUDENT</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($u['last_login'])): ?>
                                        <code><?php echo htmlspecialchars($u['last_login']); ?></code>
                                    <?php else: ?>
                                        <span style="opacity: 0.6;">Never logged in</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 StudentHub - Digital Campus Portal | CHARUSAT ITUE203</p>
    </footer>

    <!-- Shared Portal Scripts -->
    <script src="../js/script.js"></script>
</body>
</html>
