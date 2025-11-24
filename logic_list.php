<?php
/**
 * --------------------------------------------------------------------------
 * FILE: logic_list.php
 * PURPOSE: 
 *   Fetches the raw list of students and their average scores based on 
 *   the active global filters.
 * 
 * USED BY: 
 *   - Tab 1: Student List (index.php main view)
 * --------------------------------------------------------------------------
 */

$tableRows = [];

// Base SQL
$sql = "SELECT 
            s.name, s.student_id, s.level_code, s.gender, s.level_category as prog, s.status,
            SUBSTR(s.intake_no, 1, 4) as year, AVG(r.marks) as avg_mark
        FROM students s
        LEFT JOIN subject_results r ON s.student_id = r.student_id
        WHERE 1=1";

// Apply Filters
if ($filterProg) $sql .= " AND s.level_category = '$filterProg'";
if ($filterCode) $sql .= " AND s.level_code = '$filterCode'";
if ($filterStatus) $sql .= " AND s.status = '$filterStatus'";
if ($filterYear) $sql .= " AND SUBSTR(s.intake_no, 1, 4) = '$filterYear'";

$sql .= " GROUP BY s.student_id";

// Fetch
$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $avg = number_format(floatval($row['avg_mark']), 1);
    
    // Map to UI Array
    $tableRows[] = [
        $row['name'], $row['student_id'], $row['level_code'], $row['gender'],
        $row['prog'], $row['year'], 0, 0, 0, 0, 0, // Placeholders for subjects not needed in list
        $avg, $row['status']
    ];
}

// Sort
$sortFunc = function($a, $b) use ($sortOrder) { return ($sortOrder === 'asc') ? strcasecmp($a[0], $b[0]) : strcasecmp($b[0], $a[0]); };
usort($tableRows, $sortFunc);
?>