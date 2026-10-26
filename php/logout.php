<?php

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/StudentHub/",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

// Remove all session variables.
$_SESSION = [];

// Delete the session cookie.
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

// Destroy the server-side session.
session_destroy();

// Redirect to the login page.
header("Location: ../pages/login.html?logout=success");
exit;
?>