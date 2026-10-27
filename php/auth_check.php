
<?php

// Start the session and configure cookie settings safely.
if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/StudentHub/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

// Session timeout: 15 minutes of inactivity.
$timeout = 900;

// Check whether the user is logged in.
if (!isset($_SESSION["user_id"], $_SESSION["user_role"])) {
    header("Location: /StudentHub/pages/login.html");
    exit;
}

// Check whether the session has expired.
if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > $timeout
) {
    // Clear session data.
    $_SESSION = [];

    // Remove the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            [
                "expires" => time() - 42000,
                "path" => $params["path"],
                "domain" => $params["domain"],
                "secure" => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => $params["samesite"] ?? "Lax"
            ]
        );
    }

    // Destroy the session.
    session_destroy();

    // Redirect to login with a timeout message.
    header("Location: /StudentHub/pages/login.html?error=timeout");
    exit;
}

// Update the last activity time.
$_SESSION["last_activity"] = time();

// Helper function to restrict pages by role.
function requireRole($allowedRoles)
{
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    if (
        !isset($_SESSION["user_role"]) ||
        !in_array($_SESSION["user_role"], $allowedRoles, true)
    ) {
        http_response_code(403);

        echo "<h1>403 - Access Denied</h1>";
        echo "<p>You are not authorized to access this page.</p>";
        echo '<p><a href="/StudentHub/pages/login.html">Return to Login</a></p>';

        exit;
    }
}
?>
