<?php
require_once '../db/config.php';

// --- Logic ---
$pdo = db();

// Fetch songs with search functionality
$search = $_GET['q'] ?? $_GET['search'] ?? '';
$sql = "SELECT * FROM songs";
$params = [];
if (!empty($search)) {
    $sql .= " WHERE name LIKE ? OR artist LIKE ? OR bpm LIKE ? OR song_key LIKE ? OR notes LIKE ? OR tags LIKE ?";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
}
$sql .= " ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$songs = $stmt->fetchAll();

// --- Presentation ---
header('Content-Type: application/json');
echo json_encode($songs);
