<?php
require_once '../db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $lineup_id = $data['lineup_id'] ?? null;
    $song_id = $data['song_id'] ?? null;

    if ($lineup_id && $song_id) {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("DELETE FROM lineup_songs WHERE lineup_id = ? AND song_id = ?");
            $stmt->execute([$lineup_id, $song_id]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
?>