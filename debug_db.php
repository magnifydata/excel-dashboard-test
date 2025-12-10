<?php
/**
 * FILE: debug_db.php
 * PURPOSE: Connects to the SQLite database and outputs the schema (tables and columns).
 * 
 * NOTE: This file assumes 'logic_common.php' correctly defines and establishes the $pdo object.
 */

// Load common logic to establish the $pdo connection
require 'logic_common.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Schema Debug</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        h2 { color: #007bff; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f0f0f0; color: #333; }
        .error { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>SQLite Database Schema Inspection</h1>

        <?php if (isset($error) && $error): ?>
            <p class="error">Connection Error: <?php echo htmlspecialchars($error); ?></p>
        <?php else: ?>
            
            <?php
            try {
                // 1. Get all table names
                $tableQuery = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
                $tables = $tableQuery->fetchAll(PDO::FETCH_COLUMN);

                if (empty($tables)) {
                    echo "<p>No application tables found in the database.</p>";
                } else {
                    echo "<h2>Found Tables: " . count($tables) . "</h2>";
                    
                    // 2. Loop through each table and get its column info
                    foreach ($tables as $tableName) {
                        echo "<h3>Table: " . htmlspecialchars($tableName) . "</h3>";
                        
                        // SQLite PRAGMA table_info is a standard way to get column details
                        $columnQuery = $pdo->query("PRAGMA table_info(" . $tableName . ")");
                        $columns = $columnQuery->fetchAll(PDO::FETCH_ASSOC);

                        if (empty($columns)) {
                            echo "<p>No columns found.</p>";
                            continue;
                        }

                        echo "<table>";
                        echo "<thead><tr><th>CID</th><th>Name</th><th>Type</th><th>Not Null</th><th>Default</th><th>Primary Key</th></tr></thead>";
                        echo "<tbody>";
                        
                        foreach ($columns as $col) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($col['cid']) . "</td>";
                            echo "<td><strong>" . htmlspecialchars($col['name']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($col['type']) . "</td>";
                            echo "<td>" . ($col['notnull'] ? 'YES' : 'NO') . "</td>";
                            echo "<td>" . htmlspecialchars($col['dflt_value'] ?? 'NULL') . "</td>";
                            echo "<td>" . ($col['pk'] ? '✅ PK' : 'NO') . "</td>";
                            echo "</tr>";
                        }
                        
                        echo "</tbody></table>";
                    }
                }

            } catch (Exception $e) {
                echo "<p class='error'>Schema Retrieval Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>

        <?php endif; ?>
    </div>
</body>
</html>