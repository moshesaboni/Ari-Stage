<?php
header('Content-Type: text/plain; charset=utf-8');

require_once 'db/config.php';

echo "Starting database migration...\n\n";

try {
    $pdo = db();

    // 1. Create migrations tracking table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `migration_file` VARCHAR(255) NOT NULL,
        `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `migration_file_unique` (`migration_file`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "1. 'migrations' table is ready.\n";

    // 2. Get all migrations that have already been run
    $stmt = $pdo->query("SELECT `migration_file` FROM `migrations`");
    $applied_migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "2. Found " . count($applied_migrations) . " applied migrations.\n";

    // 3. Find all migration files on disk
    $migration_files = glob('db/migrations/*.sql');
    sort($migration_files);
    echo "3. Found " . count($migration_files) . " migration files on disk.\n";


    // 4. Apply pending migrations
    $migrations_applied_count = 0;
    foreach ($migration_files as $file) {
        $basename = basename($file);
        if (in_array($basename, $applied_migrations)) {
            echo "- Skipping already applied migration: {$basename}\n";
            continue;
        }

        echo "+ Applying new migration: {$basename}...\n";
        $sql = file_get_contents($file);
        if (empty(trim($sql))) {
            echo "  ...file is empty, skipping.\n";
            continue;
        }

        try {
            $pdo->beginTransaction();

            $pdo->exec($sql);

            $insert_stmt = $pdo->prepare("INSERT INTO `migrations` (migration_file) VALUES (?)");
            $insert_stmt->execute([$basename]);

            $pdo->commit();
            echo "  ...SUCCESS!\n";
            $migrations_applied_count++;

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "  ...ERROR: " . $e->getMessage() . "\n";
            echo "\nMigration failed. No changes were made to the database schema.\n";
            exit;
        }
    }

    if ($migrations_applied_count > 0) {
        echo "\nFinished. Applied {" . $migrations_applied_count . "} new migrations successfully.\n";
    } else {
        echo "\nFinished. Database is already up to date.\n";
    }

} catch (Exception $e) {
    echo "A critical error occurred: " . $e->getMessage() . "\n";
}

?>