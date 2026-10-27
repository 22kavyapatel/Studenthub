<?php
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

require_once __DIR__ . '/connection.php';

$role = strtolower(trim((string)($_POST['role'] ?? '')));
$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$mobile = trim((string)($_POST['mobile'] ?? ''));
$gender = trim((string)($_POST['gender'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirmPassword = (string)($_POST['confirmPassword'] ?? '');
$course = trim((string)($_POST['course'] ?? ''));
$year = trim((string)($_POST['year'] ?? ''));
$department = trim((string)($_POST['department'] ?? ''));
$qualification = trim((string)($_POST['qualification'] ?? ''));
$terms = $_POST['terms'] ?? '';

// Public registration must never allow someone to create an admin account.
if (!in_array($role, ['student', 'teacher'], true)) {
    http_response_code(400);
    exit('Invalid role. Please register as a student or teacher.');
}
if ($name === '' || $email === '' || $mobile === '' || $gender === '' || $password === '' || $confirmPassword === '' || $terms === '') {
    http_response_code(400);
    exit('Please fill in all required fields.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); exit('Invalid email address.'); }
if (!preg_match('/^[0-9]{10}$/', $mobile)) { http_response_code(400); exit('Mobile number must contain exactly 10 digits.'); }
if (strlen($password) < 8) { http_response_code(400); exit('Password must contain at least 8 characters.'); }
if ($password !== $confirmPassword) { http_response_code(400); exit('Passwords do not match.'); }

$check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
if (!$check) { http_response_code(500); exit('Unable to process registration.'); }
$check->bind_param('s', $email);
$check->execute();
$existing = $check->get_result()->num_rows > 0;
$check->close();
if ($existing) { http_response_code(409); exit('Email already registered.'); }

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (role, name, email, mobile, gender, course, year, department, qualification, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$stmt) { http_response_code(500); exit('Unable to process registration.'); }
$stmt->bind_param('ssssssssss', $role, $name, $email, $mobile, $gender, $course, $year, $department, $qualification, $hashedPassword);
if (!$stmt->execute()) {
    error_log('Registration insert failed: ' . $stmt->error);
    $stmt->close();
    http_response_code(500);
    exit('Registration failed. Please try again.');
}
$stmt->close();
$conn->close();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Successful - StudentHub</title>
<style>body{font-family:Arial,sans-serif;background:#f5f7fb;text-align:center;padding:60px 16px}.box{background:#fff;max-width:440px;margin:auto;padding:32px;border-radius:12px;box-shadow:0 4px 20px #0001}a{display:inline-block;margin-top:18px;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px}</style>
</head><body><div class="box"><h1>Registration Successful!</h1><p>Your account has been created successfully.</p><a href="../pages/login.html">Go to Login</a></div></body></html>
