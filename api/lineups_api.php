<?php
require_once __DIR__ . '/../db/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'error' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['lineup_id']) && is_numeric($_GET['lineup_id'])) {
        try {
            $lineup_id = intval($_GET['lineup_id']);
            $pdo = db();
            $stmt = $pdo->prepare("
                SELECT s.*, ls.song_order FROM songs s
                JOIN lineup_songs ls ON s.id = ls.song_id
                WHERE ls.lineup_id = ?
                ORDER BY ls.song_order ASC
            ");
            $stmt->execute([$lineup_id]);
            $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($songs);
            exit;
        } catch (PDOException $e) {
            $response['error'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['error'] = 'Lineup ID is required.';
    }
}

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