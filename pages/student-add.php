
<?php
require_once __DIR__ . '/../php/db_mysqli.php';

$message = '';
$name = '';
$email = '';
$mobile = '';
$course = '';
$year = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year = trim($_POST['year'] ?? '');

    if ($name === '' || $email === '') {
        $message = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO students (name, email, mobile, course, year)
             VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->bind_param('sssss', $name, $email, $mobile, $course, $year);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: students.php?success=added');
            exit;
        } else {
            $message = $conn->errno === 1062
                ? 'This email address already exists.'
                : 'Unable to add student. Please try again.';
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student | StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .student-form {
            max-width: 600px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px #ddd;
        }

        .student-form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .student-form input,
        .student-form select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        .student-form button {
            margin-top: 20px;
            padding: 10px 20px;
            cursor: pointer;
        }

        .message {
            color: #b00020;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="student-form">
    <h2>Add Student</h2>

    <?php if ($message !== ''): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Full Name *</label>
        <input type="text" id="name" name="name"
               value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
               required>

        <label for="email">Email *</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
               required>

        <label for="mobile">Mobile</label>
        <input type="text" id="mobile" name="mobile"
               value="<?= htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8') ?>">

        <label for="course">Course</label>
        <select id="course" name="course">
            <option value="">Select Course</option>
            <option value="B.Tech IT" <?= $course === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
            <option value="B.Tech CSE" <?= $course === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
            <option value="BCA" <?= $course === 'BCA' ? 'selected' : '' ?>>BCA</option>
            <option value="MCA" <?= $course === 'MCA' ? 'selected' : '' ?>>MCA</option>
        </select>

        <label for="year">Year</label>
        <select id="year" name="year">
            <option value="">Select Year</option>
            <option value="1st Year" <?= $year === '1st Year' ? 'selected' : '' ?>>1st Year</option>
            <option value="2nd Year" <?= $year === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
            <option value="3rd Year" <?= $year === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
            <option value="4th Year" <?= $year === '4th Year' ? 'selected' : '' ?>>4th Year</option>
        </select>

        <button type="submit">Save Student</button>
        <a href="students.php">Cancel</a>
    </form>
</div>

</body>
</html>
