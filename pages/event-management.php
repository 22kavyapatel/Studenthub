<?php
session_start();

require_once __DIR__ . '/../php/db_mysqli.php';

/* Admin-only access */
$role = strtolower(trim((string)(
    $_SESSION['user_role']
    ?? $_SESSION['role']
    ?? $_SESSION['userRole']
    ?? $_SESSION['user']['role']
    ?? ''
)));

if ($role !== 'admin') {
    http_response_code(403);
    exit('Access denied. Only administrators can manage events.');
}

/* Upload folder configuration */
$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR
           . 'uploads' . DIRECTORY_SEPARATOR
           . 'event_posters' . DIRECTORY_SEPARATOR;

$uploadUrl = '../uploads/event_posters/';
$maxSize = 2 * 1024 * 1024;

/* Check that the upload folder exists */
if (!is_dir($uploadDir)) {
    http_response_code(500);
    exit('Upload folder not found. Create this folder: ' . $uploadDir);
}

if (!is_writable($uploadDir)) {
    http_response_code(500);
    exit('Upload folder is not writable: ' . $uploadDir);
}

$message = '';
$messageType = 'error';

/* Escape output */
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* Upload and validate event poster */
function uploadPoster($field, $uploadDir, $maxSize, &$error) {
    if (!isset($_FILES[$field]) ||
        $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Poster upload failed.';
        return false;
    }

    if ($file['size'] <= 0 || $file['size'] > $maxSize) {
        $error = 'Poster must be smaller than or equal to 2 MB.';
        return false;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        $error = 'Only JPG, PNG, and WEBP images are allowed.';
        return false;
    }

    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        $error = 'The uploaded file is not a valid image.';
        return false;
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $error = 'Could not save the uploaded poster.';
        return false;
    }

    return $filename;
}

/* Delete an event poster */
function removePoster($filename, $uploadDir) {
    if ($filename !== '' && basename($filename) === $filename) {
        $path = $uploadDir . $filename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}

/* Redirect with a message */
function redirectWithMessage($type, $message) {
    header(
        'Location: event-management.php?type=' .
        urlencode($type) . '&message=' . urlencode($message)
    );
    exit;
}

/* Display redirected messages */
if (isset($_GET['message'])) {
    $message = (string)$_GET['message'];
    $messageType = ($_GET['type'] ?? '') === 'success'
        ? 'success'
        : 'error';
}

/* Handle form submissions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ADD EVENT */
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $status = $_POST['status'] ?? 'Open';

        if (
            $title === '' ||
            $description === '' ||
            $eventDate === '' ||
            $location === '' ||
            $category === '' ||
            !in_array($status, ['Open', 'Closed'], true)
        ) {
            redirectWithMessage('error', 'Please complete all required fields.');
        }

        $error = '';
        $poster = uploadPoster('poster', $uploadDir, $maxSize, $error);

        if ($poster === false) {
            redirectWithMessage('error', $error);
        }

        $stmt = $conn->prepare(
            'INSERT INTO admin_events
            (title, description, event_date, location, category, status, poster)
            VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->bind_param(
            'sssssss',
            $title,
            $description,
            $eventDate,
            $location,
            $category,
            $status,
            $poster
        );

        if ($stmt->execute()) {
            $stmt->close();
            redirectWithMessage('success', 'Event added successfully.');
        }

        $stmt->close();
        removePoster($poster ?? '', $uploadDir);
        redirectWithMessage('error', 'Failed to add event.');
    }

    /* UPDATE EVENT */
    if ($action === 'update') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $status = $_POST['status'] ?? 'Open';

        if (
            !$id ||
            $title === '' ||
            $description === '' ||
            $eventDate === '' ||
            $location === '' ||
            $category === '' ||
            !in_array($status, ['Open', 'Closed'], true)
        ) {
            redirectWithMessage('error', 'Invalid event details.');
        }

        $find = $conn->prepare(
            'SELECT poster FROM admin_events WHERE id = ?'
        );
        $find->bind_param('i', $id);
        $find->execute();
        $existing = $find->get_result()->fetch_assoc();
        $find->close();

        if (!$existing) {
            redirectWithMessage('error', 'Event not found.');
        }

        $error = '';
        $newPoster = uploadPoster('poster', $uploadDir, $maxSize, $error);

        if ($newPoster === false) {
            redirectWithMessage('error', $error);
        }

        $poster = $newPoster ?? $existing['poster'];

        $stmt = $conn->prepare(
            'UPDATE admin_events
             SET title = ?, description = ?, event_date = ?,
                 location = ?, category = ?, status = ?, poster = ?
             WHERE id = ?'
        );

        $stmt->bind_param(
            'sssssssi',
            $title,
            $description,
            $eventDate,
            $location,
            $category,
            $status,
            $poster,
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();

            if ($newPoster !== null) {
                removePoster($existing['poster'] ?? '', $uploadDir);
            }

            redirectWithMessage('success', 'Event updated successfully.');
        }

        $stmt->close();

        if ($newPoster !== null) {
            removePoster($newPoster, $uploadDir);
        }

        redirectWithMessage('error', 'Failed to update event.');
    }

    /* DELETE EVENT */
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            redirectWithMessage('error', 'Invalid event ID.');
        }

        $find = $conn->prepare(
            'SELECT poster FROM admin_events WHERE id = ?'
        );
        $find->bind_param('i', $id);
        $find->execute();
        $existing = $find->get_result()->fetch_assoc();
        $find->close();

        if (!$existing) {
            redirectWithMessage('error', 'Event not found.');
        }

        $stmt = $conn->prepare(
            'DELETE FROM admin_events WHERE id = ?'
        );
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $stmt->close();
            removePoster($existing['poster'] ?? '', $uploadDir);
            redirectWithMessage('success', 'Event deleted successfully.');
        }

        $stmt->close();
        redirectWithMessage('error', 'Failed to delete event.');
    }
}

/* Load event for editing */
$editEvent = null;

if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

    if ($editId) {
        $stmt = $conn->prepare(
            'SELECT * FROM admin_events WHERE id = ?'
        );
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $editEvent = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

/* Search and filter */
$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = 'SELECT * FROM admin_events WHERE 1=1';
$types = '';
$params = [];

if ($search !== '') {
    $sql .= ' AND (title LIKE ? OR location LIKE ? OR description LIKE ?)';
    $term = '%' . $search . '%';
    $types .= 'sss';
    array_push($params, $term, $term, $term);
}

if ($categoryFilter !== '') {
    $sql .= ' AND category = ?';
    $types .= 's';
    $params[] = $categoryFilter;
}

if (in_array($statusFilter, ['Open', 'Closed'], true)) {
    $sql .= ' AND status = ?';
    $types .= 's';
    $params[] = $statusFilter;
}

$sql .= ' ORDER BY event_date DESC, id DESC';

$stmt = $conn->prepare($sql);

if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$events = $stmt->get_result();

$categories = $conn->query(
    'SELECT DISTINCT category FROM admin_events ORDER BY category'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Management | StudentHub</title>
   <link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/event-management.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 25px;
            background: #f5f6fa;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .panel {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

        input, textarea, select, button {
            box-sizing: border-box;
            padding: 10px;
            margin: 5px 0;
            max-width: 100%;
        }

        input, textarea, select {
            width: 100%;
        }

        button, .button {
            cursor: pointer;
            padding: 9px 14px;
        }

        .message {
            padding: 12px;
            margin: 12px 0;
            border-radius: 5px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #eee;
        }

        .poster {
            width: 90px;
            height: 65px;
            object-fit: cover;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .actions form {
            margin: 0;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Event Management</h1>
    <p>Manage StudentHub events and posters.</p>

    <p><a href="admin-dashboard.php">← Back to Admin Dashboard</a></p>

    <?php if ($message !== ''): ?>
        <div class="message <?= e($messageType) ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <!-- Add or Edit Event -->
    <section class="panel">
        <h2><?= $editEvent ? 'Edit Event' : 'Add New Event' ?></h2>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action"
                   value="<?= $editEvent ? 'update' : 'add' ?>">

            <?php if ($editEvent): ?>
                <input type="hidden" name="id"
                       value="<?= (int)$editEvent['id'] ?>">
            <?php endif; ?>

            <label for="title">Event Title *</label>
            <input id="title" name="title" maxlength="150" required
                   value="<?= e($editEvent['title'] ?? '') ?>">

            <label for="description">Description *</label>
            <textarea id="description" name="description"
                      rows="4" required><?= e($editEvent['description'] ?? '') ?></textarea>

            <label for="event_date">Event Date *</label>
            <input type="date" id="event_date" name="event_date" required
                   value="<?= e($editEvent['event_date'] ?? '') ?>">

            <label for="location">Location *</label>
            <input id="location" name="location" maxlength="200" required
                   value="<?= e($editEvent['location'] ?? '') ?>">

            <label for="category">Category *</label>
            <input id="category" name="category" maxlength="100" required
                   placeholder="Workshop, Seminar, Sports..."
                   value="<?= e($editEvent['category'] ?? '') ?>">

            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Open"
                    <?= ($editEvent['status'] ?? 'Open') === 'Open' ? 'selected' : '' ?>>
                    Open
                </option>
                <option value="Closed"
                    <?= ($editEvent['status'] ?? '') === 'Closed' ? 'selected' : '' ?>>
                    Closed
                </option>
            </select>

            <label for="poster">Event Poster (JPG, PNG, WEBP; max 2 MB)</label>
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
            <input type="file" id="poster" name="poster"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <?php if (!empty($editEvent['poster'])): ?>
                <p>Current poster:</p>
                <img class="poster"
                     src="<?= e($uploadUrl . $editEvent['poster']) ?>"
                     alt="Current event poster">
                <p>Leave the poster field empty to keep the current poster.</p>
            <?php endif; ?>

            <button type="submit">
                <?= $editEvent ? 'Update Event' : 'Add Event' ?>
            </button>

            <?php if ($editEvent): ?>
                <a href="event-management.php">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- Search and Filter -->
    <section class="panel">
        <h2>Search and Filter Events</h2>

        <form method="GET">
            <label for="search">Search title, description, or location</label>
            <input id="search" name="search" value="<?= e($search) ?>">

            <label for="category-filter">Category</label>
            <select id="category-filter" name="category">
                <option value="">All Categories</option>

                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= e($cat['category']) ?>"
                        <?= $categoryFilter === $cat['category'] ? 'selected' : '' ?>>
                        <?= e($cat['category']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="status-filter">Status</label>
            <select id="status-filter" name="status">
                <option value="">All Statuses</option>
                <option value="Open"
                    <?= $statusFilter === 'Open' ? 'selected' : '' ?>>
                    Open
                </option>
                <option value="Closed"
                    <?= $statusFilter === 'Closed' ? 'selected' : '' ?>>
                    Closed
                </option>
            </select>

            <button type="submit">Search / Filter</button>
            <a href="event-management.php">Reset</a>
        </form>
    </section>

    <!-- Event Records -->
    <section class="panel">
        <h2>Event Records</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($events->num_rows === 0): ?>
                    <tr>
                        <td colspan="8">No events found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$event['id'] ?></td>

                            <td>
                                <?php if (!empty($event['poster'])): ?>
                                    <img class="poster"
                                         src="<?= e($uploadUrl . $event['poster']) ?>"
                                         alt="Event poster">
                                <?php else: ?>
                                    No poster
                                <?php endif; ?>
                            </td>

                            <td><?= e($event['title']) ?></td>
                            <td><?= e($event['event_date']) ?></td>
                            <td><?= e($event['location']) ?></td>
                            <td><?= e($event['category']) ?></td>
                            <td><?= e($event['status']) ?></td>

                            <td>
                                <div class="actions">
                                    <a href="event-management.php?edit=<?= (int)$event['id'] ?>">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this event?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$event['id'] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>
</body>
</html>