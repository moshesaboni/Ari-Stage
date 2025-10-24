<?php
$pageName = 'פרטי ליינאפ';
require_once 'includes/header.php';
require_once 'db/config.php';

// Check if lineup ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="container mt-4"><div class="alert alert-danger">מזהה ליינאפ לא תקין.</div></div>';
    require_once 'includes/footer.php';
    exit;
}

$lineup_id = intval($_GET['id']);
$db = db();

// Fetch lineup details
$stmt = $db->prepare("SELECT * FROM lineups WHERE id = ?");
$stmt->execute([$lineup_id]);
$lineup = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lineup) {
    echo '<div class="container mt-4"><div class="alert alert-danger">הליינאפ לא נמצא.</div></div>';
    require_once 'includes/footer.php';
    exit;
}

// Fetch songs for this lineup (to be implemented)
$songs_stmt = $db->prepare("
    SELECT s.* FROM songs s
    JOIN lineup_songs ls ON s.id = ls.song_id
    WHERE ls.lineup_id = ?
    ORDER BY ls.song_order ASC
");
$songs_stmt->execute([$lineup_id]);
$songs = $songs_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo htmlspecialchars($lineup['name']); ?></h1>
        <a href="lineups.php" class="btn btn-outline-secondary">חזרה לרשימת הליינאפים</a>
    </div>
    

    <div class="row">
        <!-- Add Songs -->
        <div class="col-md-6">
            <h3>הוספת שירים</h3>
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="song-search-input" class="form-label">חפש שיר</label>
                        <input type="text" class="form-control" id="song-search-input" placeholder="הקלד שם שיר או אמן...">
                    </div>
                    <div id="search-results" style="max-height: 300px; overflow-y: auto;">
                        <!-- Search results will appear here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Song List -->
        <div class="col-md-6">
            <h3>שירים בליינאפ</h3>
            <div id="lineup-songs-container">
                <ul class="list-group" id="lineup-song-list">
                    <?php if (empty($songs)): ?>
                        <li id="empty-lineup-message" class="list-group-item text-center text-muted">
                            אין עדיין שירים בליינאפ זה.
                        </li>
                    <?php else: ?>
                        <?php foreach ($songs as $song): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center" data-song-id="<?php echo $song['id']; ?>">
                                <span>
                                    <i class="fas fa-grip-vertical me-2"></i>
                                    <strong><?php echo htmlspecialchars($song['artist']); ?></strong> - <?php echo htmlspecialchars($song['name']); ?>
                                </span>
                                <button class="btn btn-sm btn-outline-danger remove-song-btn" data-song-id="<?php echo $song['id']; ?>" data-lineup-id="<?php echo $lineup_id; ?>">הסר</button>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="lineup-id" value="<?php echo $lineup_id; ?>">

<script src="assets/js/lineup_details_page.js?v=<?php echo time(); ?>"></script>

<?php
require_once 'includes/footer.php';
?>
