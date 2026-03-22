<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../src/db.php';

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $db->query('SELECT key, value FROM settings');
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        echo json_encode($settings);

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['key']) || !isset($input['value'])) {
            http_response_code(400);
            echo json_encode(['error' => 'key ve value gerekli']);
            exit;
        }

        $stmt = $db->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value');
        $stmt->execute([$input['key'], $input['value']]);
        echo json_encode(['success' => true]);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
