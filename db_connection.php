<?php
// db_connection.php

// Connect to the SQLite file in the same directory
$db_path = __DIR__ . '/database.sqlite';

try {
    // Create the connection
    $pdo = new PDO("sqlite:" . $db_path);
    
    // Set error mode to exceptions (helps debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Default to fetching data as arrays
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, stop and show error
    die("Database Connection Failed: " . $e->getMessage());
}
?>