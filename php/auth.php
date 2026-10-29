<?php
// ========================================================
// Practical 10: Authentication, Secure Sessions, RBAC & Timeout
// StudentHub - CHARUSAT ITUE203
// ========================================================

require_once __DIR__ . '/db.php';

// Session Inactivity Timeout in seconds (15 minutes)
define('SESSION_TIMEOUT_SECONDS', 900);

/**
 * Start a secure session with hardened cookie security parameters.
 */
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        if (!headers_sent()) {
            // Set hardened session cookie parameters
            session_set_cookie_params([
                'lifetime' => 0,              // Valid until browser is closed
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps,       // Transmit only over HTTPS if available
                'httponly' => true,           // Block JavaScript document.cookie access (Mitigates XSS)
                'samesite' => 'Lax'           // Protect against Cross-Site Request Forgery (CSRF)
            ]);

            ini_set('session.use_only_cookies', 1); // Disallow session IDs in URLs
            ini_set('session.use_strict_mode', 1);  // Reject uninitialized session IDs
        }

        @session_start();
    }
}

/**
 * Authenticate user using email and password.
 * Uses password_verify() and regenerates session ID upon login.
 * Updates last_login timestamp and handles remember-me token.
 */
function authenticate_user($email, $password, $rememberMe = false) {
    global $pdo;

    start_secure_session();

    $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'error' => 'invalid', 'message' => 'Invalid email or password.'];
    }

    // Verify password against bcrypt / PASSWORD_DEFAULT hash
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'invalid', 'message' => 'Invalid email or password.'];
    }

    // Regenerate session ID to prevent Session Fixation attacks
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    // Populate session
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_name']     = $user['name'];
    $_SESSION['user_email']    = $user['email'];
    $_SESSION['user_role']     = $user['role'];
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time']    = time();

    // Intermediate Extension: Record last_login timestamp
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);

    // Advanced Extension: Secure Remember-Me Token using random token hashed with SHA-256
    if ($rememberMe) {
        $rawToken    = bin2hex(random_bytes(32)); // 64 hex characters cryptographically secure
        $tokenHash   = hash('sha256', $rawToken);
        $expiryTime  = time() + (14 * 86400);     // 14 days
        $expiryDate  = date('Y-m-d H:i:s', $expiryTime);

        $tokenStmt = $pdo->prepare("UPDATE users SET remember_token_hash = ?, remember_token_expiry = ? WHERE id = ?");
        $tokenStmt->execute([$tokenHash, $expiryDate, $user['id']]);

        $cookieValue = $user['id'] . ':' . $rawToken;
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        if (!headers_sent()) {
            setcookie('remember_token', $cookieValue, [
                'expires'  => $expiryTime,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }

    return [
        'success' => true,
        'role'    => $user['role'],
        'user'    => $user
    ];
}

/**
 * Check and restore session via Remember-Me token if session does not exist.
 */
function try_remember_me_login() {
    global $pdo;

    if (!empty($_SESSION['user_id'])) {
        return true;
    }

    if (empty($_COOKIE['remember_token'])) {
        return false;
    }

    $parts = explode(':', $_COOKIE['remember_token'], 2);
    if (count($parts) !== 2) {
        return false;
    }

    $userId   = (int)$parts[0];
    $rawToken = $parts[1];

    $stmt = $pdo->prepare("SELECT id, name, email, role, remember_token_hash, remember_token_expiry FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || empty($user['remember_token_hash']) || empty($user['remember_token_expiry'])) {
        return false;
    }

    // Check expiration
    if (strtotime($user['remember_token_expiry']) < time()) {
        return false;
    }

    // Verify token hash using timing-safe comparison
    if (!hash_equals($user['remember_token_hash'], hash('sha256', $rawToken))) {
        return false;
    }

    // Auto-login user
    session_regenerate_id(true);
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_name']     = $user['name'];
    $_SESSION['user_email']    = $user['email'];
    $_SESSION['user_role']     = $user['role'];
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time']    = time();

    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);

    return true;
}

/**
 * Enforce authentication, session inactivity timeout, and role-based access control.
 *
 * @param string|null $requiredRole Expected role ('admin' or 'student'), or null for any logged-in user.
 */
function require_auth($requiredRole = null) {
    global $pdo;

    start_secure_session();

    // Check for simulated timeout action (provided for laboratory demonstration/screenshots)
    if (isset($_GET['action']) && $_GET['action'] === 'simulate_timeout') {
        $_SESSION['last_activity'] = time() - (SESSION_TIMEOUT_SECONDS + 10);
    }

    // Attempt auto-login via remember-me token if session is missing
    if (empty($_SESSION['user_id'])) {
        try_remember_me_login();
    }

    // 1. Check if user is logged in
    if (empty($_SESSION['user_id'])) {
        header("Location: ../pages/login.php?error=unauthorized");
        exit;
    }

    // 2. Inactivity Timeout Check
    if (isset($_SESSION['last_activity'])) {
        $inactiveSeconds = time() - $_SESSION['last_activity'];
        if ($inactiveSeconds > SESSION_TIMEOUT_SECONDS) {
            // Destroy session due to timeout
            logout_user(false);
            header("Location: ../pages/login.php?error=timeout");
            exit;
        }
    }

    // Update last activity timestamp
    $_SESSION['last_activity'] = time();

    // 3. Role-Based Access Control (RBAC)
    if ($requiredRole !== null) {
        $currentRole = $_SESSION['user_role'] ?? 'student';
        if ($currentRole !== $requiredRole) {
            // Unauthorized access attempt: render security block page (HTTP 403)
            http_response_code(403);
            render_access_denied_page($requiredRole, $currentRole);
            exit;
        }
    }
}

/**
 * Destroy session completely, clear cookies, and remove DB remember-me token.
 */
function logout_user($redirect = true) {
    global $pdo;

    start_secure_session();

    // Remove Remember-Me token in database if user is known
    if (!empty($_SESSION['user_id']) && $pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET remember_token_hash = NULL, remember_token_expiry = NULL WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (Exception $e) {
            // Ignore DB errors on logout
        }
    }

    // Clear Remember-Me cookie
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    if (!headers_sent()) {
        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    // Unset all session variables
    $_SESSION = [];

    // Delete session cookie
    if (ini_get("session.use_cookies") && !headers_sent()) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session storage
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    if ($redirect && !headers_sent()) {
        header("Location: ../pages/login.php?msg=logged_out");
        exit;
    }
}

/**
 * Render dedicated 403 Forbidden Access Denied page for unauthorized dashboard access attempts.
 */
function render_access_denied_page($requiredRole, $currentRole) {
    $userName  = htmlspecialchars($_SESSION['user_name'] ?? 'User');
    $userEmail = htmlspecialchars($_SESSION['user_email'] ?? '');
    $sessionId = htmlspecialchars(session_id());
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>StudentHub - 403 Forbidden (Unauthorized Access Attempt)</title>
        <link rel="icon" type="image/png" href="../docs/sitemap.png">
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
        <header>
            <div class="heading-group">
                <h1>Student Hub</h1>
                <img src="../docs/campus.jpg" alt="StudentHub Campus" class="heading-img">
            </div>
        </header>

        <nav class="nav-container">
            <a href="index.html">Home</a>
            <a href="dashboard.php">Student Dashboard</a>
            <a href="events.html">Events</a>
            <a href="../php/logout.php" style="color: #ff9999;">Logout</a>
        </nav>

        <main>
            <section class="registration-card" style="max-width: 650px; text-align: center; border: 2px solid var(--error-color);">
                <div class="form-alert alert-error" style="display: block; text-align: left;">
                    <h2 style="margin: 0 0 10px 0; color: var(--error-color); display: flex; align-items: center; gap: 8px;">
                        🚫 403 Forbidden - Access Denied!
                    </h2>
                    <p style="font-size: 15px; margin: 0 0 10px 0;">
                        <strong>Unauthorized Dashboard Access Attempt Blocked.</strong>
                    </p>
                    <p style="font-size: 13.5px; margin: 0; line-height: 1.5;">
                        This area requires the <strong><?php echo htmlspecialchars(strtoupper($requiredRole)); ?></strong> role.
                        Your active session is authenticated as <strong><?php echo htmlspecialchars($userName); ?> (<?php echo htmlspecialchars($userEmail); ?>)</strong>
                        with the role of <span style="background: var(--button-bg); padding: 2px 8px; border-radius: 4px; font-weight: bold;"><?php echo htmlspecialchars(strtoupper($currentRole)); ?></span>.
                    </p>
                </div>

                <div style="background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin: 20px 0; text-align: left; font-size: 13px;">
                    <div style="font-weight: bold; margin-bottom: 8px; color: var(--accent-color);">🔒 Practical 10 - Security RBAC Inspection:</div>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
                        <li><strong>Session ID:</strong> <code><?php echo $sessionId; ?></code></li>
                        <li><strong>Access Check:</strong> Blocked by <code>require_auth('<?php echo htmlspecialchars($requiredRole); ?>')</code></li>
                        <li><strong>HTTP Status Code:</strong> 403 Forbidden</li>
                        <li><strong>Action:</strong> Access to privileged dashboard restricted</li>
                    </ul>
                </div>

                <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; margin-top: 20px;">
                    <a href="dashboard.php" class="btn-primary-submit" style="text-decoration: none; padding: 10px 20px;">
                        ⬅ Return to Student Dashboard
                    </a>
                    <a href="../php/logout.php" class="btn-secondary-reset" style="text-decoration: none; padding: 10px 20px;">
                        🚪 Logout
                    </a>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2026 StudentHub - Digital Campus Portal | CHARUSAT ITUE203</p>
        </footer>
    </body>
    </html>
    <?php
}
