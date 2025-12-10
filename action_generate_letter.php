<?php
/**
 * FILE: action_generate_letter.php
 * PURPOSE: Outputs the final PDF/HTML for the customized counselling letter.
 * 
 * NOTE: PDF GENERATION LOGIC MUST BE ADDED HERE. This code currently outputs clean HTML for print preview/PDF download.
 */

// Load common logic to establish the $pdo connection
require 'logic_common.php';

// Ensure the request has a valid student ID
$studentId = $_GET['student_id'] ?? null;
if (!$studentId) {
    die("Error: Student ID is missing.");
}

if (!isset($pdo)) {
    die("Error: Database connection failed.");
}

// 1. Fetch Student-Specific Data for Customization
try {
    $stmt = $pdo->prepare("
        SELECT 
            s.name, 
            s.level_code, 
            s.student_id,
            AVG(sr.marks) AS overall_avg
        FROM students s
        JOIN subject_results sr ON s.student_id = sr.student_id
        WHERE s.student_id = ?
        GROUP BY s.student_id, s.name, s.level_code
    ");
    $stmt->execute([$studentId]);
    $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Database Error fetching student data: " . $e->getMessage());
}

if (!$studentData) {
    die("Error: Student profile not found for ID: " . htmlspecialchars($studentId));
}

// --- Dynamic Content Variables ---
$studentName = htmlspecialchars($studentData['name']);
$studentProgram = htmlspecialchars($studentData['level_code']);
$avgMark = round((float)$studentData['overall_avg'], 2);
$date = date('F jS, Y');
$counsellorEmail = "counsellor@university.edu"; // Placeholder

// --- FINAL LETTER CONTENT (Clean HTML for Print/PDF) ---
$htmlContent = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Academic Support Letter - {$studentName}</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                margin: 50px 75px; 
                width: 700px; /* Width for print simulation */
            }
            .header { margin-bottom: 40px; }
            .subject { font-weight: bold; margin-top: 20px; }
            .signature { margin-top: 50px; }
            .risk-score { color: #ef4444; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='header'>
            [School/Department Letterhead or Logo]
            <p>Date: {$date}</p>
        </div>

        <p>To: Mr./Ms. {$studentName}<br>
           Student ID: {$studentId}<br>
           Program: {$studentProgram}</p>

        <p class='subject'>Subject: Academic Support Initiative - Developing a Plan for Success</p>

        <p>Dear {$studentName},</p>

        <p>We are writing to you as part of our **Early Intervention and Academic Support Initiative**.</p> 

        <p>Our records indicate that your overall average mark across your modules is currently <span class='risk-score'>{$avgMark}%</span>. We noticed that this is below the expected level and want to reach out immediately to offer guidance and support to help you achieve your full academic potential.</p>

        <p>We believe in your success and want to assure you that **we are here to support and guide you** to achieve your academic goals. The purpose of this intervention is proactive—to help you develop better study strategies and overcome any challenges you may be facing *before* they become significant obstacles.</p>

        <p><strong>Action Required:</strong></p>
        <p>Please contact your academic counsellor at {$counsellorEmail} at your earliest opportunity to schedule a meeting. Together, you will develop a personalized **Academic Improvement Plan** that can help you dramatically enhance your performance.</p>

        <p>We look forward to meeting with you and helping you build a successful academic career.</p>

        <div class='signature'>
            <p>Sincerely,</p>
            <p>[Head of Department/Academic Support Office Name]</p>
        </div>
    </body>
    </html>
";
// -----------------------------


// 2. IMMEDIATE PDF/DOWNLOAD OUTPUT (No Preview Button)
// This will either download a PDF (if DomPDF is working) or download a clean HTML file (for testing).

$fileName = "Academic_Support_Letter_" . $studentId . ".pdf";

/* 
// --- DOMPDF IMPLEMENTATION ---
// REQUIRE DOMPDF LIBRARY HERE
// $dompdf = new Dompdf\Dompdf();
// $dompdf->loadHtml($htmlContent);
// $dompdf->setPaper('A4', 'portrait');
// $dompdf->render();

// --- FINAL PDF HEADER ---
// header("Content-type: application/pdf");
// header("Content-Disposition: attachment; filename={$fileName}");
// echo $dompdf->output();
*/


// --- TEMPORARY DEBUGGING FALLBACK (Simulates PDF Download with HTML content) ---
// Note: Some browsers will show this as HTML, others will download it.
header("Content-type: text/html");
header("Content-Disposition: inline; filename={$fileName}.html"); // inline tries to display in browser
echo $htmlContent;
// ---------------------------------------------------------------------------------

exit;
?>