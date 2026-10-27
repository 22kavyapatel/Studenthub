<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/db_mysqli.php';

$sql = "SELECT id, title, description, event_date, location, category, status, poster, created_at
        FROM admin_events
        WHERE LOWER(TRIM(status)) = 'open'
        ORDER BY event_date ASC, id DESC";

$result = $conn->query($sql);
if (!$result) {
    error_log('get_events.php query failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load events.']);
    exit;
}

$events = [];
while ($row = $result->fetch_assoc()) {
    $poster = trim((string)($row['poster'] ?? ''));
    $events[] = [
        'id' => (int)$row['id'],
        'title' => (string)$row['title'],
        'description' => (string)($row['description'] ?? ''),
        'date' => (string)$row['event_date'],
        'location' => (string)($row['location'] ?? ''),
        'category' => (string)($row['category'] ?? 'General'),
        'status' => (string)($row['status'] ?? ''),
        'poster_url' => $poster !== '' ? '../uploads/event_posters/' . rawurlencode(basename($poster)) : '',
        'organizer' => 'StudentHub',
        'time' => '',
        'capacity' => null
    ];
}

$result->free();
echo json_encode(['success' => true, 'data' => $events], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
