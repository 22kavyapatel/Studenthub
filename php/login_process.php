<?php

// Accept only POST requests.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/login.html");
    exit;
}

// Configure secure session cookies before starting the session.
session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/StudentHub/",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

require_once __DIR__ . "/db.php";

// Get submitted form values.
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$selectedRole = strtolower(trim($_POST["role"] ?? ""));

// Validate the input.
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $password === "" ||
    !in_array($selectedRole, ["student", "teacher", "admin"], true)
) {
    header("Location: ../pages/login.html?error=invalid");
    exit;
}

try {
    // Find the account by email.
    $stmt = $pdo->prepare(
        "SELECT id, name, email, password, role
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    $stmt->execute([$email]);

    $user = $stmt->fetch();

    // Verify the password and selected role.
    if (
        !$user ||
        !password_verify($password, $user["password"]) ||
        strtolower($user["role"]) !== $selectedRole
    ) {
        header("Location: ../pages/login.html?error=invalid");
        exit;
    }

    // Prevent session fixation.
    session_regenerate_id(true);

    // Store authenticated user details in the session.
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_name"] = $user["name"];
    $_SESSION["user_email"] = $user["email"];
    $_SESSION["user_role"] = strtolower($user["role"]);

    // Track activity for the inactivity timeout.
    $_SESSION["last_activity"] = time();

    // Redirect according to the role stored in the database.
    switch ($_SESSION["user_role"]) {
        case "student":
            header("Location: ../pages/studentdashboard.php");
            break;

        case "teacher":
            header("Location: ../pages/teacher-dashboard.php");
            break;

        case "admin":
            header("Location: ../pages/admin-dashboard.php");
            break;

        default:
            session_unset();
            session_destroy();

            header("Location: ../pages/login.html?error=invalid");
            break;
    }

    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    exit("Login is temporarily unavailable. Please try again later.");
}
?>