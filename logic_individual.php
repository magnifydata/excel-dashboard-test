<?php
/**
 * --------------------------------------------------------------------------
 * FILE: logic_individual.php
 * PURPOSE: 
 *   1. Fetches Global Subject Averages and Global Semester Averages (for context).
 *   2. Fetches the Master List of ALL students (unfiltered by global dropdowns), 
 *      including NEW: Nationality.
 *   3. Packs specific student data (Subjects, Marks, GPA History) into a JSON payload.
 * 
 * USED BY: 
 *   - Tab 5: Individual Performance (tab_individual.php)
 * --------------------------------------------------------------------------
 */

$allStudentsData = [];
$globalSubjectAvgs = [];
$globalSemesterAvgs = [];

// 1. Global Subject Avgs
$stmt = $pdo->query("SELECT subject_code, AVG(marks) as m FROM subject_results GROUP BY subject_code");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) $globalSubjectAvgs[$r['subject_code']] = round($r['m'], 1);

// 2. Global Sem Avgs
$stmt = $pdo->query("SELECT semester_no, AVG(gpa) as g, AVG(cgpa) as c FROM semester_performance GROUP BY semester_no");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) $globalSemesterAvgs[$r['semester_no']] = ['gpa'=>round($r['g'],2), 'cgpa'=>round($r['c'],2)];

// 3. Fetch All Students
$sql = "
    SELECT 
        s.student_id, s.name, s.gender, s.level_category as prog, s.level_code, s.status,
        SUBSTR(s.intake_no, 1, 4) as year, s.nationality, AVG(r.marks) as avg_mark,
        
        -- Get Subjects
        (SELECT GROUP_CONCAT(subject_code || '=' || marks) 
         FROM subject_results sr WHERE sr.student_id = s.student_id) as subjects,
         
        -- Get Semesters (Ordered)
        (SELECT GROUP_CONCAT(semester_no || ':' || gpa || ':' || cgpa || ':' || credits_earned) 
         FROM (SELECT * FROM semester_performance sp WHERE sp.student_id = s.student_id ORDER BY semester_no ASC)) as sems
         
    FROM students s
    LEFT JOIN subject_results r ON s.student_id = r.student_id
    GROUP BY s.student_id
";

$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    // Process Subjects
    $subList = [];
    if($row['subjects']) {
        $pairs = explode(',', $row['subjects']);
        foreach($pairs as $p) {
            $parts = explode('=', $p);
            if(count($parts)==2) $subList[] = ['code'=>$parts[0], 'mark'=>floatval($parts[1])];
        }
    }

    // Process Semesters
    $semHistory = [];
    if($row['sems']) {
        $sems = explode(',', $row['sems']);
        foreach($sems as $s) {
            $p = explode(':', $s);
            if(count($p)==4) $semHistory[] = ['sem'=>intval($p[0]), 'gpa'=>floatval($p[1]), 'cgpa'=>floatval($p[2]), 'credits'=>intval($p[3])];
        }
    }

    // Index Map for JS:
    // [0] Name, [1] Student ID, [2] Level Code, [3] Gender, [4] Program, [5] Year, [6] Nationality (NEW), [7-11] Unused/Zeros, [12] Avg Mark (FIXED INDEX), [13] Status, [14] Subject List, [15] Semester List
    $allStudentsData[] = [
        $row['name'], $row['student_id'], $row['level_code'], $row['gender'],
        $row['prog'], $row['year'], $row['nationality'], // Index 6 is Nationality
        0,0,0,0,0,
        number_format($row['avg_mark'], 1), // Index 12 is Avg Mark
        $row['status'], // Index 13 is Status
        json_encode($subList), // Index 14
        json_encode($semHistory) // Index 15
    ];
}

// Sort A-Z
usort($allStudentsData, function($a, $b) { return strcasecmp($a[0], $b[0]); });
?>