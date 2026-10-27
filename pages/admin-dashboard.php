
<?php
require_once __DIR__ . "/../php/auth_check.php";
requireRole("admin");

require_once __DIR__ . "/../php/db_mysqli.php";

/* Get administrator details */
$adminName = $_SESSION["user_name"]
    ?? $_SESSION["name"]
    ?? $_SESSION["user"]["name"]
    ?? "Administrator";

$adminId = $_SESSION["user_id"]
    ?? $_SESSION["id"]
    ?? $_SESSION["user"]["id"]
    ?? null;

$adminId = filter_var($adminId, FILTER_VALIDATE_INT);
$adminId = $adminId ?: null;

$errorMessage = "";

$totalUsers = 0;
$totalStudents = 0;
$totalTeachers = 0;
$totalEvents = 0;
$totalRegistrations = 0;

$recentRegistrations = false;
$auditRecords = false;

/* Escape output safely */
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

/* Record administrator actions */
function logAdminAction($conn, $userId, $action, $details)
{
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, details)
         VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        error_log("Audit log preparation failed: " . $conn->error);
        return;
    }

    $stmt->bind_param("iss", $userId, $action, $details);

    if (!$stmt->execute()) {
        error_log("Audit log insert failed: " . $stmt->error);
    }

    $stmt->close();
}

/* Load dashboard information */
try {

    /* User statistics */
    $result = $conn->query(
        "SELECT
            COUNT(*) AS total_users,
            COALESCE(SUM(role = 'student'), 0) AS total_students,
            COALESCE(SUM(role = 'teacher'), 0) AS total_teachers
         FROM users"
    );

    if (!$result) {
        throw new Exception("Unable to load user statistics.");
    }

    $stats = $result->fetch_assoc();

    $totalUsers = (int) $stats["total_users"];
    $totalStudents = (int) $stats["total_students"];
    $totalTeachers = (int) $stats["total_teachers"];

    /* Event count */
    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM admin_events"
    );

    if (!$result) {
        throw new Exception("Unable to load events.");
    }

    $totalEvents = (int) $result->fetch_assoc()["total"];

    /* Registration count */
    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM registrations"
    );

    if (!$result) {
        throw new Exception("Unable to load registrations.");
    }

    $totalRegistrations = (int) $result->fetch_assoc()["total"];

    /* Latest registrations */
    $recentRegistrations = $conn->query(
        "SELECT
            r.registration_id,
            r.registration_date,
            s.name AS student_name,
            s.email AS student_email,
            ev.event_name
         FROM registrations r
         LEFT JOIN students s
            ON r.student_id = s.student_id
         LEFT JOIN events ev
            ON r.event_id = ev.event_id
         ORDER BY
            r.registration_date DESC,
            r.registration_id DESC
         LIMIT 5"
    );

    if (!$recentRegistrations) {
        throw new Exception("Unable to load recent registrations.");
    }

    /* Recent audit records */
    $auditRecords = $conn->query(
        "SELECT
            a.log_id,
            a.action,
            a.details,
            a.created_at,
            u.name AS admin_name
         FROM audit_logs a
         LEFT JOIN users u ON a.user_id = u.id
         ORDER BY a.created_at DESC, a.log_id DESC
         LIMIT 5"
    );

    if (!$auditRecords) {
        throw new Exception("Unable to load audit logs.");
    }

} catch (Throwable $exception) {

    error_log("Admin dashboard error: " . $exception->getMessage());

    $errorMessage = "Some dashboard information could not be loaded.";
}

/* Log successful dashboard visits */
if ($errorMessage === "") {
    logAdminAction(
        $conn,
        $adminId,
        "VIEW_DASHBOARD",
        "Administrator opened the dashboard."
    );
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - StudentHub</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin-dashboard.css">

    <style>
        .page-title {
            margin-bottom: 8px;
        }

        .dashboard-subtitle {
            margin-bottom: 24px;
            color: #64748b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 18px;
            margin: 24px 0;
        }

        .stat-card,
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.07);
        }

        .stat-card {
            padding: 22px;
        }

        .stat-card h3 {
            margin-top: 12px;
            font-size: 30px;
        }

        .dashboard-card {
            min-width: 0;
            padding: 22px;
            margin-bottom: 22px;
        }

        .dashboard-table-wrap {
            overflow-x: auto;
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .dashboard-table th {
            background: #f1f5f9;
        }

        .dashboard-message {
            padding: 14px;
            border-radius: 8px;
            margin: 15px 0;
            background: #fee2e2;
            color: #991b1b;
        }

        .sidebar-nav a {
            display: block;
            text-decoration: none;
        }

        .sidebar-nav a.active {
            font-weight: bold;
        }

        @media (max-width: 650px) {
            .dashboard-table {
                min-width: 600px;
            }
        }
    </style>
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <div class="brand">
            <h1>StudentHub</h1>
            <p>Admin Portal</p>
        </div>

        <div class="welcome-box">
            <p>Welcome back,</p>
            <strong><?= e($adminName) ?></strong>
            <p>Role: Administrator</p>
        </div>

        <nav class="sidebar-nav">

            <a href="admin-dashboard.php" class="active">
                📊 Dashboard
            </a>

            <a href="event-management.php">
                🎫 Event Management
            </a>

            <a href="students-ajax.php">
                👥 Student Management
            </a>

            <a href="admin-dashboard.php#audit-logs">
                📋 Audit Logs
            </a>

        </nav>

        <a href="../php/logout.php" class="logout">Logout</a>

    </aside>

    <main class="main-content">

        <h2 class="page-title">Admin Dashboard</h2>

        <p class="dashboard-subtitle">
            Monitor users, students, events, and registrations.
        </p>

        <?php if ($errorMessage !== ""): ?>
            <div class="dashboard-message">
                <?= e($errorMessage) ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard statistics -->
        <section class="stats-grid">

            <div class="stat-card">
                <p>Total Users</p>
                <h3 class="blue"><?= $totalUsers ?></h3>
            </div>

            <div class="stat-card">
                <p>Total Students</p>
                <h3 class="green"><?= $totalStudents ?></h3>
            </div>

            <div class="stat-card">
                <p>Total Teachers</p>
                <h3 class="purple"><?= $totalTeachers ?></h3>
            </div>

            <div class="stat-card">
                <p>Total Events</p>
                <h3 class="orange"><?= $totalEvents ?></h3>
            </div>

            <div class="stat-card">
                <p>Total Registrations</p>
                <h3 class="violet"><?= $totalRegistrations ?></h3>
            </div>

        </section>

        <!-- Latest registrations -->
        <section class="dashboard-card">

            <h2>Latest Registrations</h2>

            <div class="dashboard-table-wrap">

                <table class="dashboard-table">

                    <thead>
                        <tr>
                            <th>Registration ID</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Event</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (
                        $recentRegistrations &&
                        $recentRegistrations->num_rows > 0
                    ): ?>

                        <?php while (
                            $registration = $recentRegistrations->fetch_assoc()
                        ): ?>

                            <tr>
                                <td>
                                    <?= (int) $registration["registration_id"] ?>
                                </td>

                                <td>
                                    <?= e($registration["student_name"] ?? "Unknown") ?>
                                </td>

                                <td>
                                    <?= e($registration["student_email"] ?? "Unknown") ?>
                                </td>

                                <td>
                                    <?= e($registration["event_name"] ?? "Unknown") ?>
                                </td>

                                <td>
                                    <?= e($registration["registration_date"]) ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php elseif ($errorMessage === ""): ?>

                        <tr>
                            <td colspan="5">No registrations found.</td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </section>

        <!-- Audit logs -->
        <section class="dashboard-card" id="audit-logs">

            <h2>Recent Audit Logs</h2>

            <p>Recent administrative actions recorded by the system.</p>

            <div class="dashboard-table-wrap">

                <table class="dashboard-table">

                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Administrator</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Date and Time</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (
                        $auditRecords &&
                        $auditRecords->num_rows > 0
                    ): ?>

                        <?php while ($log = $auditRecords->fetch_assoc()): ?>

                            <tr>
                                <td><?= (int) $log["log_id"] ?></td>

                                <td>
                                    <?= e($log["admin_name"] ?? "Unknown") ?>
                                </td>

                                <td><?= e($log["action"]) ?></td>

                                <td><?= e($log["details"]) ?></td>

                                <td><?= e($log["created_at"]) ?></td>
                            </tr>

                        <?php endwhile; ?>

                    <?php elseif ($errorMessage === ""): ?>

                        <tr>
                            <td colspan="5">No audit records found.</td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </section>

    </main>

</div>

<script src="../js/auth.js"></script>
<script src="../js/theme.js"></script>

</body>
</html>
