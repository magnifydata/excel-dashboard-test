<?php
/**
 * FILE: logic_risk.php
 * PURPOSE: Fetches the list of students whose overall average mark across all subjects is < 50%, 
 *          INCLUDING ONLY STUDENTS WHERE STATUS = 'Active'.
 * 
 * DEPENDS ON: $pdo (from logic_common.php)
 */

if (!isset($pdo)) {
    $studentsAtRisk = [];
    $riskError = "Database connection not established.";
    return;
}

$studentsAtRisk = [];
$riskError = "";

$sqlRisk = "
    SELECT 
        s.student_id,
        s.name,
        s.nationality,
        s.status,
        s.level_category,
        s.level_code,
        AVG(sr.marks) AS overall_avg_mark,
        -- Subquery to get the latest (highest semester_no) CGPA
        (SELECT sp.cgpa
         FROM semester_performance sp
         WHERE sp.student_id = s.student_id
         ORDER BY sp.semester_no DESC
         LIMIT 1) AS latest_cgpa
    FROM subject_results sr
    JOIN students s ON sr.student_id = s.student_id
    WHERE s.status = 'Active' -- <<< NEW CRITICAL FILTER HERE <<<
    GROUP BY 
        s.student_id, s.name, s.nationality, s.status, s.level_category, s.level_code
    HAVING overall_avg_mark < 50
    ORDER BY overall_avg_mark ASC
";

try {
    $stmtRisk = $pdo->prepare($sqlRisk);
    $stmtRisk->execute(); 
    $studentsAtRisk = $stmtRisk->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $riskError = "SQL Error fetching Students At Risk: " . $e->getMessage();
}
?>