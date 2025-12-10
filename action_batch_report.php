<?php
/**
 * FILE: action_batch_report.php
 * PURPOSE: Generates a single PDF containing the table of all students on the High Risk list.
 */

// Load common logic to establish the $pdo connection
require 'logic_common.php';

if (!isset($pdo)) {
    die("Error: Database connection failed.");
}

// 1. Fetch Students At Risk (Same logic as logic_risk.php)
$sqlRisk = "
    SELECT 
        s.student_id,
        s.name,
        s.nationality,
        s.status,
        s.level_category,
        s.level_code,
        AVG(sr.marks) AS overall_avg_mark,
        (SELECT sp.cgpa
         FROM semester_performance sp
         WHERE sp.student_id = s.student_id
         ORDER BY sp.semester_no DESC
         LIMIT 1) AS latest_cgpa
    FROM subject_results sr
    JOIN students s ON sr.student_id = s.student_id
    WHERE s.status = 'Active' 
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
    die("Database Error fetching Students At Risk: " . $e->getMessage());
}


// 2. HTML Content Generation for PDF Renderer
$tableRows = "";
foreach ($studentsAtRisk as $student) {
    $avgMark = round((float)($student['overall_avg_mark'] ?? 0), 2);
    $cgpa = round((float)($student['latest_cgpa'] ?? 0), 2);
    
    $tableRows .= "
        <tr>
            <td>" . htmlspecialchars($student['name']) . "</td>
            <td>" . htmlspecialchars($student['student_id']) . "</td>
            <td>" . htmlspecialchars($student['status']) . "</td>
            <td>" . htmlspecialchars($student['nationality']) . "</td>
            <td>" . $avgMark . "%</td>
            <td>" . $cgpa . "</td>
        </tr>
    ";
}

$htmlContent = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>High Risk Student Batch Report</title>
        <style>
            body { font-family: Arial, sans-serif; }
            h1 { color: #ef4444; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .count { margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <h1>High Risk Students Batch Report</h1>
        <p class='count'>Report Generated: " . date('Y-m-d H:i') . "</p>
        <p class='count'>Total Students: " . count($studentsAtRisk) . "</p>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Nationality</th>
                    <th>Overall Avg Mark</th>
                    <th>Latest CGPA</th>
                </tr>
            </thead>
            <tbody>
                " . $tableRows . "
            </tbody>
        </table>
    </body>
    </html>
";


// 3. PDF GENERATION LOGIC (Placeholder)
$fileName = "Batch_High_Risk_Report_" . date('Ymd') . ".pdf";

/* 
// --- DOMPDF IMPLEMENTATION ---
// require 'vendor/autoload.php'; 
// $dompdf = new Dompdf\Dompdf();
// $dompdf->loadHtml($htmlContent);
// $dompdf->setPaper('A4', 'landscape'); // Use landscape for wider table
// $dompdf->render();
// $dompdf->stream($fileName, array("Attachment" => true)); 
*/

// --- TEMPORARY DEBUGGING FALLBACK (Remove for Production) ---
header("Content-type: text/html");
echo $htmlContent;
// -----------------------------------------------------------

exit;
?>