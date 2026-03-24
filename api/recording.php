<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/recording.php';

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        checkAllActiveRecordings($db);
        echo json_encode(getRecordings($db));

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['channel_id']) || empty($input['duration'])) {
            http_response_code(400);
            echo json_encode(['error' => 'channel_id ve duration gerekli']);
            exit;
        }

        $recording = startRecording($db, (int)$input['channel_id'], (int)$input['duration']);
        http_response_code(201);
        echo json_encode($recording);

    } elseif ($method === 'PATCH') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id']) || ($input['action'] ?? '') !== 'stop') {
            http_response_code(400);
            echo json_encode(['error' => 'id ve action:stop gerekli']);
            exit;
        }

        $stopped = stopRecording($db, (int)$input['id']);
        echo json_encode(['stopped' => $stopped]);

    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id gerekli']);
            exit;
        }

        $deleted = deleteRecording($db, (int)$input['id']);
        echo json_encode(['deleted' => $deleted]);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
