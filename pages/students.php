
<?php
require_once __DIR__ . "/../php/db_mysqli.php";

$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");

$sql = "SELECT student_id, name, email, mobile, course, year
        FROM students
        WHERE (name LIKE ? OR email LIKE ? OR course LIKE ?)";

$params = ["%$search%", "%$search%", "%$search%"];
$types = "sss";

if ($course !== "") {
    $sql .= " AND course = ?";
    $params[] = $course;
    $types .= "s";
}

$sql .= " ORDER BY student_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$courses = $conn->query(
    "SELECT DISTINCT course FROM students
     WHERE course IS NOT NULL AND course <> ''
     ORDER BY course"
);

$message = $_GET["message"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f4f7fc;
            color: #263247;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            color: #172554;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .filters, .table-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
        }

        .filters form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        input, select {
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            min-width: 180px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th {
            background: #eff6ff;
            color: #1e3a8a;
        }

        .message {
            padding: 12px;
            background: #dcfce7;
            color: #166534;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .empty {
            text-align: center;
            padding: 25px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .edit {
            background: #15803d;
        }

        .delete {
            background: #dc2626;
        }

        .back {
            color: #2563eb;
            text-decoration: none;
        }

        @media (max-width: 800px) {
            .table-box {
                overflow-x: auto;
            }

            table {
                min-width: 850px;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>Student Management</h1>
        <a class="button" href="student-add.php">+ Add Student</a>
    </div>

    <p><a class="back" href="studentdashboard.php">← Back to Dashboard</a></p>

    <?php if ($message !== ""): ?>
        <div class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <div class="filters">
        <form method="GET">
            <input
                type="text"
                name="search"
                placeholder="Search name, email, or course"
                value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>"
            >

            <select name="course">
                <option value="">All Courses</option>

                <?php while ($c = $courses->fetch_assoc()): ?>
                    <option
                        value="<?= htmlspecialchars($c["course"], ENT_QUOTES, "UTF-8") ?>"
                        <?= $course === $c["course"] ? "selected" : "" ?>
                    >
                        <?= htmlspecialchars($c["course"], ENT_QUOTES, "UTF-8") ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button class="button" type="submit">Search / Filter</button>
            <a class="button" href="students.php">Reset</a>
        </form>
    </div>

    <div class="table-box">
        <h2>Student Records</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($student = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $student["student_id"] ?></td>
                        <td><?= htmlspecialchars($student["name"], ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($student["email"], ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($student["mobile"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($student["course"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($student["year"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                        <td>
                            <div class="actions">
                                <a class="button edit"
                                   href="student-edit.php?id=<?= (int) $student["student_id"] ?>">
                                    Edit
                                </a>

                                <form method="POST" action="../php/student-delete.php"
                                      onsubmit="return confirm('Are you sure you want to delete this student?');">
                                    <input type="hidden" name="student_id"
                                           value="<?= (int) $student["student_id"] ?>">
                                    <button class="button delete" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty">No student records found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
