<?php
/**
 * --------------------------------------------------------------------------
 * FILE: logic_subjects.php
 * PURPOSE: 
 *   Aggregates performance data grouped by Subject Code.
 *   Calculates Pass Rates, Distinction Rates, and Highest/Lowest scores per module.
 * 
 * USED BY: 
 *   - Tab 3: Subject Performance (tab_subjects.php)
 *   - Tab 4: Lecturer Performance (tab_lecturers.php)
 * --------------------------------------------------------------------------
 */

$subjectDetails = [];
$lecturerList = [];

// Calculate Stats per Subject
$sql = "SELECT r.subject_code, AVG(r.marks) as avg, COUNT(*) as count,
        SUM(CASE WHEN r.marks >= 50 THEN 1 ELSE 0 END) as passed,
        SUM(CASE WHEN r.marks >= 80 THEN 1 ELSE 0 END) as dist,
        MAX(r.marks) as highest, MIN(r.marks) as lowest
        FROM subject_results r 
        JOIN students s ON r.student_id = s.student_id
        WHERE 1=1";

// Apply Filters
if ($filterProg) $sql .= " AND s.level_category = '$filterProg'";
if ($filterYear) $sql .= " AND SUBSTR(s.intake_no, 1, 4) = '$filterYear'";

$sql .= " GROUP BY r.subject_code";

$stmt = $pdo->query($sql);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $code = $row['subject_code'];
    $passRate = round(($row['passed'] / $row['count']) * 100, 1);
    
    // For Tab 3 (Heatmap)
    $subAvg[] = round($row['avg'], 1);
    $subPassRate[] = $passRate;
    
    // For Tab 4 (Lecturer List)
    $lecturerList[] = [
        'code' => $code,
        'avg' => round($row['avg'], 1),
        'pass' => $passRate,
        'dist' => round(($row['dist'] / $row['count']) * 100, 1),
        'students' => $row['count']
    ];

    // Detailed
    $subjectDetails[] = [
        'name' => $code,
        'avg' => round($row['avg'], 1),
        'pass' => $passRate,
        'high' => $row['highest'],
        'low' => $row['lowest']
    ];
}

// Sort Lecturer List
usort($lecturerList, function($a, $b) { return $b['avg'] <=> $a['avg']; });
?>