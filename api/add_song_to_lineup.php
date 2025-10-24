<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/config.php';

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['lineup_id']) || !isset($data['song_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$lineup_id = $data['lineup_id'];
$song_id = $data['song_id'];

try {
    $pdo = db();

    // Check if the song is already in the lineup
    $stmt = $pdo->prepare("SELECT 1 FROM lineup_songs WHERE lineup_id = ? AND song_id = ?");
    $stmt->execute([$lineup_id, $song_id]);
    if ($stmt->fetch()) {
        http_response_code(409); // 409 Conflict
        echo json_encode(['success' => false, 'message' => 'השיר כבר קיים בליינאפ.']);
        exit;
    }

    // Get the current number of songs in the lineup to determine the new order.
    // This is much faster than calculating MAX(song_order).
    $stmt = $pdo->prepare("SELECT COUNT(*) as song_count FROM lineup_songs WHERE lineup_id = ?");
    $stmt->execute([$lineup_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $new_order = $result ? (int)$result['song_count'] : 0;


    // Insert the new song
    $stmt = $pdo->prepare("INSERT INTO lineup_songs (lineup_id, song_id, song_order) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$lineup_id, $song_id, $new_order])) {
        // Fetch the added song details to return to the client
        $song_stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
        $song_stmt->execute([$song_id]);
        $song = $song_stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'message' => 'השיר נוסף בהצלחה!', 'song' => $song]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'לא ניתן היה להוסיף את השיר.']);
    }

} catch (PDOException $e) {
    // Log error to a file for debugging
    error_log("Add song to lineup failed: " . $e->getMessage());
    http_response_code(500);
    // Return a more specific error message for debugging
    echo json_encode(['success' => false, 'message' => 'שגיאת שרת: ' . $e->getMessage()]);
}
?>