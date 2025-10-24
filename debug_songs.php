<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo '<h1>מצב מאגר השירים</h1>';
echo '<p>בעמוד זה מוצגת רשימת השירים כפי שהיא שמורה כרגע במסד הנתונים.</p>';

try {
    require_once 'db/config.php';
    $pdo = db();
    
    $stmt = $pdo->query("SELECT * FROM songs ORDER BY id ASC");
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($songs)) {
        echo '<div style="border: 1px solid #ccc; padding: 15px; background-color: #f0f0f0;"><strong>המאגר ריק.</strong> לא נמצאו שירים.</div>';
    } else {
        echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
        echo '<thead style="background-color: #f2f2f2;"><tr><th>ID</th><th>Name</th><th>Artist</th><th>BPM</th><th>Key</th><th>Duration</th><th>Tags</th><th>Notes</th></tr></thead>';
        echo '<tbody>';
        foreach ($songs as $song) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($song['id']) . '</td>';
            echo '<td>' . htmlspecialchars($song['name']) . '</td>';
            echo '<td>' . htmlspecialchars($song['artist'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($song['bpm'] ?? 'N/A') . '</td>';
            echo '<td>'  . htmlspecialchars($song['song_key'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($song['duration_seconds'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($song['tags'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($song['notes'] ?? 'N/A') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p style="margin-top: 15px;"><strong>סה"כ שירים: ' . count($songs) . '</strong></p>';
    }
    
} catch (Exception $e) {
    echo '<div style="border: 1px solid red; padding: 15px; background-color: #ffebeb; color: red;">';
    echo '<strong>שגיאה חמורה!</strong><br>';
    echo 'לא ניתן היה להתחבר למסד הנתונים או לשלוף את המידע.<br>';
    echo 'פרטי השגיאה: ' . $e->getMessage();
    echo '</div>';
}
?>
