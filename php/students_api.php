<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function respond($success, $message, $data = null, $status = 200) {
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// Student records are private and all management operations are administrator-only.
if (empty($_SESSION['user_id']) || strtolower((string)($_SESSION['user_role'] ?? '')) !== 'admin') {
    respond(false, 'Administrator login is required.', null, 403);
}

require_once __DIR__ . '/db_mysqli.php';

function getInput() {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!is_array($input)) respond(false, 'Invalid JSON request.', null, 400);
    return $input;
}

function validateStudentFields($input) {
    $student = [
        'name' => trim((string)($input['name'] ?? '')),
        'email' => trim((string)($input['email'] ?? '')),
        'mobile' => trim((string)($input['mobile'] ?? '')),
        'course' => trim((string)($input['course'] ?? '')),
        'year' => trim((string)($input['year'] ?? ''))
    ];
    foreach ($student as $value) {
        if ($value === '') respond(false, 'Please fill in all student fields.', null, 400);
    }
    if (!filter_var($student['email'], FILTER_VALIDATE_EMAIL)) respond(false, 'Please enter a valid email address.', null, 400);
    if (!preg_match('/^[0-9]{10}$/', $student['mobile'])) respond(false, 'Mobile number must contain exactly 10 digits.', null, 400);
    return $student;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $result = $conn->query('SELECT student_id, name, email, mobile, course, year FROM students ORDER BY student_id DESC');
    if (!$result) respond(false, 'Unable to fetch students.', null, 500);
    $students = [];
    while ($row = $result->fetch_assoc()) $students[] = $row;
    respond(true, 'Students fetched successfully.', $students);
}

if ($method !== 'POST') respond(false, 'Method not allowed.', null, 405);
$input = getInput();
$action = (string)($input['action'] ?? '');

if ($action === 'add') {
    $student = validateStudentFields($input);
    $stmt = $conn->prepare('INSERT INTO students (name, email, mobile, course, year) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) respond(false, 'Unable to prepare student insert.', null, 500);
    $stmt->bind_param('sssss', $student['name'], $student['email'], $student['mobile'], $student['course'], $student['year']);
    if (!$stmt->execute()) {
        error_log('Student insert failed: ' . $stmt->error);
        $stmt->close();
        respond(false, 'Unable to add student. Check for duplicate or invalid data.', null, 400);
    }
    $id = $conn->insert_id;
    $stmt->close();
    respond(true, 'Student added successfully.', ['student_id' => $id], 201);
}

if ($action === 'update') {
    $id = filter_var($input['student_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) respond(false, 'Invalid student ID.', null, 400);
    $student = validateStudentFields($input);
    $stmt = $conn->prepare('UPDATE students SET name = ?, email = ?, mobile = ?, course = ?, year = ? WHERE student_id = ?');
    if (!$stmt) respond(false, 'Unable to prepare student update.', null, 500);
    $stmt->bind_param('sssssi', $student['name'], $student['email'], $student['mobile'], $student['course'], $student['year'], $id);
    if (!$stmt->execute()) {
        error_log('Student update failed: ' . $stmt->error);
        $stmt->close();
        respond(false, 'Unable to update student. Check for duplicate or invalid data.', null, 400);
    }
    $stmt->close();
    respond(true, 'Student updated successfully.');
}

if ($action === 'delete') {
    $id = filter_var($input['student_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) respond(false, 'Invalid student ID.', null, 400);
    $stmt = $conn->prepare('DELETE FROM students WHERE student_id = ?');
    if (!$stmt) respond(false, 'Unable to prepare student deletion.', null, 500);
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        error_log('Student delete failed: ' . $stmt->error);
        $stmt->close();
        respond(false, 'Unable to delete student.', null, 400);
    }
    $deleted = $stmt->affected_rows;
    $stmt->close();
    if ($deleted === 0) respond(false, 'Student not found.', null, 404);
    respond(true, 'Student deleted successfully.');
}

respond(false, 'Invalid action.', null, 400);
