<?php
session_start();
require_once 'db/config.php';

// --- Logic ---
$pdo = db();
$notification = null;

// Handle POST requests for CUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                throw new Exception("שם השיר הוא שדה חובה.");
            }

            $bpm = !empty($_POST['bpm']) ? (int)$_POST['bpm'] : null;
            $key_note = $_POST['key_note'] ?? '';
            $key_scale = $_POST['key_scale'] ?? '';
            $song_key = trim($key_note . ' ' . $key_scale);
            $notes = trim($_POST['notes'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            
            $minutes = !empty($_POST['duration_minutes']) ? (int)$_POST['duration_minutes'] : 0;
            $seconds = !empty($_POST['duration_seconds']) ? (int)$_POST['duration_seconds'] : 0;
            $duration_seconds = ($minutes * 60) + $seconds;

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO songs (name, bpm, song_key, duration_seconds, notes, tags) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $bpm, $song_key, $duration_seconds, $notes, $tags]);
                $_SESSION['notification'] = ['message' => 'השיר נוצר בהצלחה!', 'type' => 'success'];
            } else { // update
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE songs SET name=?, bpm=?, song_key=?, duration_seconds=?, notes=?, tags=? WHERE id=?");
                    $stmt->execute([$name, $bpm, $song_key, $duration_seconds, $notes, $tags, $id]);
                    $_SESSION['notification'] = ['message' => 'השיר עודכן בהצלחה!', 'type' => 'success'];
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM songs WHERE id=?");
                $stmt->execute([$id]);
                $_SESSION['notification'] = ['message' => 'השיר נמחק בהצלחה.', 'type' => 'danger'];
            }
        }
    } catch (Exception $e) {
        $_SESSION['notification'] = ['message' => 'אירעה שגיאה: ' . $e->getMessage(), 'type' => 'danger'];
    }

    // Redirect to avoid form resubmission
    header("Location: songs.php");
    exit();
}

// Check for notification from session
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}


// Fetch all songs to display
$songs = $pdo->query("SELECT * FROM songs ORDER BY name ASC")->fetchAll();

function format_duration($seconds) {
    if ($seconds === null || $seconds < 0) return '00:00';
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf('%02d:%02d', $mins, $secs);
}

// --- Presentation ---
include 'includes/header.php';
?>

<!-- Notification Toast -->
<?php if ($notification): ?>
<div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3">
    <div id="notificationToast" class="toast align-items-center text-bg-<?php echo htmlspecialchars($notification['type']); ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?php echo htmlspecialchars($notification['message']); ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">מאגר השירים</h1>
    <button id="addSongBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#songModal"><i class="bi bi-plus-circle me-2"></i>הוסף שיר חדש</button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>שם השיר</th>
                        <th>BPM</th>
                        <th>סולם</th>
                        <th>משך</th>
                        <th>תגים</th>
                        <th>פעולות</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($songs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">עדיין אין שירים במאגר. <a href="#" id="addSongBtnLink">הוסף את השיר הראשון שלך!</a></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($songs as $song): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($song['name']); ?></td>
                                <td><?php echo htmlspecialchars($song['bpm']); ?></td>
                                <td><?php echo htmlspecialchars($song['song_key']); ?></td>
                                <td><?php echo format_duration($song['duration_seconds']); ?></td>
                                <td>
                                    <?php foreach(explode(',', $song['tags']) as $tag): ?>
                                        <?php if(trim($tag)): ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-dark-emphasis"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-song='<?php echo htmlspecialchars(json_encode($song), ENT_QUOTES, 'UTF-8'); ?>'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="songs.php" method="POST" class="d-inline" onsubmit="return confirm('האם אתה בטוח שברצונך למחוק את השיר?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $song['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Song Modal -->
<div class="modal fade" id="songModal" tabindex="-1" aria-labelledby="songModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="songForm" action="songs.php" method="POST">
                <input type="hidden" name="action" id="action" value="create">
                <input type="hidden" name="id" id="song_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="songModalLabel">הוספת שיר חדש</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">שם השיר <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="bpm" class="form-label">BPM</label>
                            <input type="number" class="form-control" id="bpm" name="bpm">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">סולם</label>
                            <div class="input-group">
                                <select class="form-select" id="key_note" name="key_note">
                                    <option value="" selected disabled>תו</option>
                                    <option value="C">C</option>
                                    <option value="C#">C#</option>
                                    <option value="D">D</option>
                                    <option value="D#">D#</option>
                                    <option value="E">E</option>
                                    <option value="F">F</option>
                                    <option value="F#">F#</option>
                                    <option value="G">G</option>
                                    <option value="G#">G#</option>
                                    <option value="A">A</option>
                                    <option value="A#">A#</option>
                                    <option value="B">B</option>
                                </select>
                                <select class="form-select" id="key_scale" name="key_scale">
                                    <option value="Major">Major</option>
                                    <option value="Minor">Minor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">משך (mm:ss)</label>
                            <div class="input-group duration-input-group">
                                <input type="number" class="form-control text-center" id="duration_minutes" name="duration_minutes" placeholder="00" min="0" max="59">
                                <span class="input-group-text">:</span>
                                <input type="number" class="form-control text-center" id="duration_seconds" name="duration_seconds" placeholder="00" min="0" max="59">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">תגים / סגנון</label>
                        <input type="text" class="form-control" id="tags" name="tags" placeholder="מופרד בפסיקים, לדוגמה: רוק, קאבר, שקט">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">הערות</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ביטול</button>
                    <button type="submit" class="btn btn-primary">שמור שיר</button>
                </div>
            </form>
            <form id="deleteForm" action="songs.php" method="POST" class="d-none">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id">
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
