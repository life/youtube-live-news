<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/youtube.php';

$db = getDb();

try {
    $results = refreshAllChannels($db);
    echo json_encode(['success' => true, 'channels' => $results]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
