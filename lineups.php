<?php
$pageTitle = 'הליינאפים שלי';
$pageName = 'lineups';
require_once __DIR__ . '/db/config.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>הליינאפים שלי</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLineupModal">צור ליינאפ חדש</button>
    </div>

    <?php
    try {
        $pdo = db();
        $stmt = $pdo->query("SELECT * FROM lineups ORDER BY created_at DESC");
        $lineups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle DB error
        $lineups = [];
        echo "<div class='alert alert-danger'>שגיאה בטעינת הליינאפים: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>

    <?php if (empty($lineups)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-music-note-list" style="font-size: 3rem;"></i>
            <p class="mt-3">עדיין לא יצרת ליינאפים.</p>
            <p>לחץ על "צור ליינאפ חדש" כדי להתחיל.</p>
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($lineups as $lineup): ?>
                <a href="lineup_details.php?id=<?php echo $lineup['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($lineup['name']); ?></h5>
                        <small class="text-muted">נוצר ב: <?php echo date('d/m/Y', strtotime($lineup['created_at'])); ?></small>
                    </div>
                    <i class="bi bi-chevron-left"></i>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Create Lineup Modal -->
<div class="modal fade" id="createLineupModal" tabindex="-1" aria-labelledby="createLineupModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createLineupModalLabel">יצירת ליינאפ חדש</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="createLineupForm">
          <div class="mb-3">
            <label for="lineupName" class="form-label">שם הליינאפ</label>
            <input type="text" class="form-control" id="lineupName" name="lineupName" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">סגור</button>
        <button type="submit" form="createLineupForm" class="btn btn-primary">צור</button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
