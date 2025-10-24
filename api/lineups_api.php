<?php
require_once __DIR__ . '/../db/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'error' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'create':
                if (!empty($data['name'])) {
                    try {
                        $pdo = db();
                        $stmt = $pdo->prepare("INSERT INTO lineups (name) VALUES (?)");
                        $stmt->execute([$data['name']]);
                        $response = ['success' => true, 'lineup_id' => $pdo->lastInsertId()];
                    } catch (PDOException $e) {
                        $response['error'] = 'Database error: ' . $e->getMessage();
                    }
                } else {
                    $response['error'] = 'Lineup name is required.';
                }
                break;

            default:
                $response['error'] = 'Unknown action.';
                break;
        }
    }
}

echo json_encode($response);
