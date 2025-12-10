<?php
/**
 * FILE: logic_lecturers.php
 * PURPOSE: API logic to fetch and calculate module/subject performance metrics
 *          to populate the charts and tables on the Lecturer/Module tab.
 * 
 * DEPENDS ON: $pdo (from logic_common.php)
 * DEPENDS ON: $filterProg, $filterYear, $filterCode, $filterStatus (from logic_common.php)
 */

if (!isset($pdo)) {
    // If $pdo is not defined, stop execution (logic_common.php failed to load)
    $lecturerList = [];
    return;
}

// 1. Build Dynamic WHERE Clause (Reusing global filters from logic_common.php)
$whereClauses = [];
$params = [];

// NOTE: To filter subject_results by student details (cohort/status), a JOIN is required.
// For now, we only filter if subject_results *also* contains the student filters (which it doesn't 
// based on the previous schema). For simplicity, we'll only filter by available columns. 

// To correctly filter subject_results by student criteria, we would typically do a JOIN:
$whereStudentClauses = [];
$studentParams = [];
if ($filterProg) { $whereStudentClauses[] = "s.level_category = ?"; $studentParams[] = $filterProg; }
if ($filterCode) { $whereStudentClauses[] = "s.level_code = ?"; $studentParams[] = $filterCode; }
if ($filterStatus) { $whereStudentClauses[] = "s.status = ?"; $studentParams[] = $filterStatus; }
// Year requires a special join if needed: SUBSTR(s.intake_no, 1, 4) = ?

$studentJoinSQL = "";
if (!empty($whereStudentClauses)) {
    // Assuming a 'students' table with 'student_id', and 'subject_results' has 'student_id'
    $studentJoinSQL = " JOIN students s ON sr.student_id = s.student_id WHERE " . implode(" AND ", $whereStudentClauses);
    $params = array_merge($params, $studentParams);
}


// 2. Fetch and Aggregate Data
// We assume subject_results has: subject_code, marks, grade, student_id
$sql = "
    SELECT 
        sr.subject_code AS code,
        AVG(sr.marks) AS avg_mark,
        SUM(CASE WHEN sr.grade = 'F' THEN 0 ELSE 1 END) AS passed_count,
        SUM(CASE WHEN sr.marks >= 80 THEN 1 ELSE 0 END) AS distinction_count,
        COUNT(sr.student_id) AS total_students
    FROM subject_results sr
    $studentJoinSQL
    GROUP BY sr.subject_code
    ORDER BY avg_mark DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rawResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle SQL error
    $error = "SQL Error in Lecturer Tab: " . $e->getMessage();
    $rawResults = [];
}


// 3. Format Data into $lecturerList
$lecturerList = [];
foreach ($rawResults as $row) {
    $total = (int)$row['total_students'];
    $passed = (int)$row['passed_count'];
    $distinction = (int)$row['distinction_count'];
    
    // Calculate Rates
    $passRate = $total > 0 ? round(($passed / $total) * 100) : 0;
    $distRate = $total > 0 ? round(($distinction / $total) * 100) : 0;
    
    // Format Avg Mark
    $avgMark = round((float)$row['avg_mark'], 2);
    
    $lecturerList[] = [
        'code' => $row['code'],
        'avg' => $avgMark,
        'pass' => $passRate,
        'dist' => $distRate,
        'students' => $total,
        // Optional: Lecturer Name would go here if a JOIN with a 'lecturers' table existed
    ];
}
// Sort again as the JS code expects it sorted DESC by average score for slicing
usort($lecturerList, function($a, $b) {
    return $b['avg'] <=> $a['avg'];
});

// Final array will be available as $lecturerList
?>