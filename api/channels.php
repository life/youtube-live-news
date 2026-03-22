<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/channels.php';

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        echo json_encode(getAllChannels($db));

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['name']) || empty($input['channel_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'name ve channel_id gerekli']);
            exit;
        }

        $channel = addChannel($db, $input['name'], $input['channel_id']);
        http_response_code(201);
        echo json_encode($channel);

    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id gerekli']);
            exit;
        }

        $deleted = deleteChannel($db, (int)$input['id']);
        echo json_encode(['deleted' => $deleted]);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
