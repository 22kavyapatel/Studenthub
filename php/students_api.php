
<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_mysqli.php';

function respond($success, $message, $data = null, $status = 200) {
    http_response_code($status);

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);

    exit;
}

function getInput() {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);

    if (!is_array($input)) {
        respond(false, 'Invalid JSON request.', null, 400);
    }

    return $input;
}

$method = $_SERVER['REQUEST_METHOD'];

/* GET: List all students */
if ($method === 'GET') {
    $result = $conn->query(
        'SELECT student_id, name, email, mobile, course, year
         FROM students ORDER BY student_id DESC'
    );

    if (!$result) {
        respond(false, 'Unable to fetch students.', null, 500);
    }

    $students = [];

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    respond(true, 'Students fetched successfully.', $students);
}

/* POST: Add, update, or delete students */
if ($method === 'POST') {
    $input = getInput();
    $action = $input['action'] ?? '';

    /* ADD */
    if ($action === 'add') {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $mobile = trim($input['mobile'] ?? '');
        $course = trim($input['course'] ?? '');
        $year = trim($input['year'] ?? '');

        if (
            $name === '' || $email === '' || $mobile === '' ||
            $course === '' || $year === ''
        ) {
            respond(false, 'Please fill in all fields.', null, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(false, 'Please enter a valid email address.', null, 400);
        }

        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            respond(false, 'Mobile number must contain exactly 10 digits.', null, 400);
        }

        $stmt = $conn->prepare(
            'INSERT INTO students (name, email, mobile, course, year)
             VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->bind_param('sssss', $name, $email, $mobile, $course, $year);

        if (!$stmt->execute()) {
            $stmt->close();
            respond(false, 'Unable to add student. Check whether the email must be unique.', null, 400);
        }

        $id = $conn->insert_id;
        $stmt->close();

        respond(true, 'Student added successfully.', ['student_id' => $id], 201);
    }

    /* UPDATE */
    if ($action === 'update') {
        $id = filter_var($input['student_id'] ?? null, FILTER_VALIDATE_INT);
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $mobile = trim($input['mobile'] ?? '');
        $course = trim($input['course'] ?? '');
        $year = trim($input['year'] ?? '');

        if (
            !$id || $name === '' || $email === '' || $mobile === '' ||
            $course === '' || $year === ''
        ) {
            respond(false, 'Please provide a valid ID and complete all fields.', null, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(false, 'Please enter a valid email address.', null, 400);
        }

        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            respond(false, 'Mobile number must contain exactly 10 digits.', null, 400);
        }

        $stmt = $conn->prepare(
            'UPDATE students
             SET name = ?, email = ?, mobile = ?, course = ?, year = ?
             WHERE student_id = ?'
        );

        $stmt->bind_param('sssssi', $name, $email, $mobile, $course, $year, $id);

        if (!$stmt->execute()) {
            $stmt->close();
            respond(false, 'Unable to update student.', null, 400);
        }

        $changed = $stmt->affected_rows;
        $stmt->close();

        if ($changed === 0) {
            $check = $conn->prepare(
                'SELECT student_id FROM students WHERE student_id = ?'
            );
            $check->bind_param('i', $id);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();

            if (!$exists) {
                respond(false, 'Student not found.', null, 404);
            }
        }

        respond(true, 'Student updated successfully.');
    }

    /* DELETE */
    if ($action === 'delete') {
        $id = filter_var($input['student_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            respond(false, 'Invalid student ID.', null, 400);
        }

        $stmt = $conn->prepare(
            'DELETE FROM students WHERE student_id = ?'
        );

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $deleted = $stmt->affected_rows;
        $stmt->close();

        if ($deleted === 0) {
            respond(false, 'Student not found.', null, 404);
        }

        respond(true, 'Student deleted successfully.');
    }

    respond(false, 'Invalid action.', null, 400);
}

respond(false, 'Method not allowed.', null, 405);
