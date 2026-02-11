<?php
include 'includes/db.php';

$sqlFile = 'update_db_pembimbing.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found.");
}

$sql = file_get_contents($sqlFile);
$queries = explode(';', $sql);

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if (mysqli_query($conn, $query)) {
            echo "Success: " . substr($query, 0, 50) . "...\n";
        } else {
            // Ignore "Duplicate column" or "already exists" errors to be safe
            echo "Note/Error: " . mysqli_error($conn) . "\n";
        }
    }
}
echo "Database update completed.";
?>
