<?php
// --- PERFORMANCE SETTINGS ---
ini_set('memory_limit', '512M');
ini_set('display_errors', 0);

$dbFile = 'database.sqlite';

// Data Containers
$allStudentsData = [];       
$tableRows = [];     
$uniqueYears = []; 
$uniqueProgs = [];
$uniqueCodes = [];
$uniqueStatus = [];
$error = "";

// Global Subject Averages & Stats (For Lecturer Tab)
$globalSubjectAvgs = [];
$lecturerStats = []; // Key: Subject Code -> [Total, Count, Pass, Grades]

// Stats Containers
$snapStats = ['Male'=>0, 'Female'=>0]; 
$snapGrades = ['A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'F'=>0]; 
$snapSubjects = [0=>0, 1=>0, 2=>0, 3=>0, 4=>0]; 
$snapCount = 0;

// Detailed Stats
$subjectDetails = [];
for($i=0; $i<5; $i++) {
    $subjectDetails[$i] = ['total'=>0, 'pass_count'=>0, 'highest'=>0, 'lowest'=>100, 'scores'=>[]];
}

$trendHistory = [];

// Inputs
$filterProg = isset($_GET['prog']) ? $_GET['prog'] : '';
$filterYear = isset($_GET['year']) ? $_GET['year'] : '';
$filterCode = isset($_GET['code']) ? $_GET['code'] : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$sortOrder  = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
$activeTab  = isset($_GET['active_tab']) ? $_GET['active_tab'] : 'list'; 

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. DROPDOWNS
    $uniqueYears = $pdo->query("SELECT DISTINCT SUBSTR(intake_no, 1, 4) as yr FROM students WHERE yr IS NOT NULL AND yr != '' ORDER BY yr DESC")->fetchAll(PDO::FETCH_COLUMN);
    $uniqueProgs = $pdo->query("SELECT DISTINCT level_category FROM students WHERE level_category IS NOT NULL AND level_category != '' ORDER BY level_category")->fetchAll(PDO::FETCH_COLUMN);
    $uniqueCodes = $pdo->query("SELECT DISTINCT level_code FROM students WHERE level_code IS NOT NULL AND level_code != '' ORDER BY level_code")->fetchAll(PDO::FETCH_COLUMN);
    $uniqueStatus = $pdo->query("SELECT DISTINCT status FROM students WHERE status IS NOT NULL AND status != '' ORDER BY status")->fetchAll(PDO::FETCH_COLUMN);

    // 2. GLOBAL AVERAGES & LECTURER STATS
    $avgStmt = $pdo->query("SELECT subject_code, AVG(marks) as avg_mark, COUNT(*) as total_students, 
                            SUM(CASE WHEN marks >= 50 THEN 1 ELSE 0 END) as passed,
                            SUM(CASE WHEN marks >= 80 THEN 1 ELSE 0 END) as grade_a
                            FROM subject_results GROUP BY subject_code");
    while($avgRow = $avgStmt->fetch(PDO::FETCH_ASSOC)) {
        $code = $avgRow['subject_code'];
        $globalSubjectAvgs[$code] = round($avgRow['avg_mark'], 1);
        
        // Populate Lecturer/Module Stats
        $lecturerStats[$code] = [
            'avg' => round($avgRow['avg_mark'], 1),
            'students' => $avgRow['total_students'],
            'pass_rate' => round(($avgRow['passed'] / $avgRow['total_students']) * 100, 1),
            'distinction_rate' => round(($avgRow['grade_a'] / $avgRow['total_students']) * 100, 1)
        ];
    }

    // 3. GLOBAL SEMESTER AVERAGES
    $globalSemesterAvgs = [];
    $semStmt = $pdo->query("SELECT semester_no, AVG(gpa) as a_gpa, AVG(cgpa) as a_cgpa FROM semester_performance GROUP BY semester_no ORDER BY semester_no ASC");
    while($semRow = $semStmt->fetch(PDO::FETCH_ASSOC)) {
        $globalSemesterAvgs[$semRow['semester_no']] = [
            'gpa' => round($semRow['a_gpa'], 2),
            'cgpa' => round($semRow['a_cgpa'], 2)
        ];
    }

    // 4. MAIN QUERY
    $sql = "
        SELECT 
            s.student_id, s.name, s.gender, s.level_category as prog, s.level_code, s.status,
            SUBSTR(s.intake_no, 1, 4) as year,
            AVG(r.marks) as avg_mark,
            GROUP_CONCAT(DISTINCT r.subject_code || '=' || r.marks) as subject_data,
            (
                SELECT GROUP_CONCAT(semester_no || ':' || gpa || ':' || cgpa || ':' || credits_earned) 
                FROM semester_performance sp 
                WHERE sp.student_id = s.student_id 
                ORDER BY semester_no ASC
            ) as semester_data
        FROM students s
        LEFT JOIN subject_results r ON s.student_id = r.student_id
        GROUP BY s.student_id
    ";

    $stmt = $pdo->query($sql);
    
    while ($dbRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        
        // Parse Subjects
        $rawSubjects = $dbRow['subject_data'] ? explode(',', $dbRow['subject_data']) : [];
        $fullSubjectList = []; 
        $uiMarks = []; 
        
        foreach($rawSubjects as $item) {
            $parts = explode('=', $item);
            if(count($parts) == 2) {
                $fullSubjectList[] = ['code' => $parts[0], 'mark' => floatval($parts[1])];
                if(count($uiMarks) < 5) $uiMarks[] = floatval($parts[1]);
            }
        }
        while(count($uiMarks) < 5) $uiMarks[] = 0;

        // Parse Semester
        $semRaw = $dbRow['semester_data'] ? explode(',', $dbRow['semester_data']) : [];
        $semHistory = [];
        foreach($semRaw as $semStr) {
            $parts = explode(':', $semStr);
            if(count($parts) == 4) {
                $semHistory[] = [
                    'sem' => intval($parts[0]),
                    'gpa' => floatval($parts[1]),
                    'cgpa' => floatval($parts[2]),
                    'credits' => intval($parts[3])
                ];
            }
        }

        $avg = floatval($dbRow['avg_mark']);
        $finalAvg = number_format($avg, 1);

        // UI Row
        $uiRow = [
            $dbRow['name'],         // 0
            $dbRow['student_id'],   // 1
            $dbRow['level_code'],   // 2
            $dbRow['gender'],       // 3
            $dbRow['prog'],         // 4
            $dbRow['year'],         // 5
            $uiMarks[0], $uiMarks[1], $uiMarks[2], $uiMarks[3], $uiMarks[4], // 6-10
            $finalAvg,              // 11
            $dbRow['status'],       // 12
            json_encode($fullSubjectList), // 13
            json_encode($semHistory) // 14
        ];

        $allStudentsData[] = $uiRow;   

        // Filter Logic
        $include = true;
        if ($filterProg && $dbRow['prog'] !== $filterProg) $include = false;
        if ($filterCode && $dbRow['level_code'] !== $filterCode) $include = false;
        if ($filterStatus && $dbRow['status'] !== $filterStatus) $include = false;
        
        if ($include) { 
             $trendHistory[$dbRow['year']][$dbRow['prog']][] = $avg;
        }

        if ($filterYear && $dbRow['year'] !== $filterYear) $include = false; 

        if ($include) {
            $tableRows[] = $uiRow;

            // Stats
            $prog = $dbRow['prog'];
            if(!isset($snapStats[$prog])) $snapStats[$prog] = ['c'=>0, 't'=>0];
            $snapStats[$prog]['c']++;
            $snapStats[$prog]['t'] += $avg;

            if($dbRow['gender'] == 'Male') $snapStats['Male']++;
            elseif($dbRow['gender'] == 'Female') $snapStats['Female']++;

            foreach($uiMarks as $idx => $score) {
                $snapSubjects[$idx] += $score;
                if($score >= 50) $subjectDetails[$idx]['pass_count']++;
            }
            $snapCount++;

            if($avg >= 80) $g='A'; elseif($avg >= 70) $g='B'; elseif($avg >= 60) $g='C'; elseif($avg >= 50) $g='D'; else $g='F';
            $snapGrades[$g]++;
        }
    }

} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}

// Sorting
$sortFunc = function($a, $b) use ($sortOrder) { return ($sortOrder === 'asc') ? strcasecmp($a[0], $b[0]) : strcasecmp($b[0], $a[0]); };
usort($tableRows, $sortFunc);
usort($allStudentsData, $sortFunc);

// Leaderboard
$leaderboard = $tableRows;
usort($leaderboard, function($a, $b) { return $b[11] <=> $a[11]; });
$top5 = array_slice($leaderboard, 0, 5);

// Calculations
$avgBus = 0; $avgComp = 0;
if(isset($snapStats['Business']) && $snapStats['Business']['c'] > 0) {
    $avgBus = round($snapStats['Business']['t'] / $snapStats['Business']['c'], 1);
} elseif(isset($snapStats['Social Science']) && $snapStats['Social Science']['c'] > 0) {
    $avgBus = round($snapStats['Social Science']['t'] / $snapStats['Social Science']['c'], 1);
}
if(isset($snapStats['Computing']) && $snapStats['Computing']['c'] > 0) {
    $avgComp = round($snapStats['Computing']['t'] / $snapStats['Computing']['c'], 1);
}

$trendLabels = $uniqueYears;
$trendSeriesBus = []; $trendSeriesComp = [];
foreach ($uniqueYears as $y) {
    $b = isset($trendHistory[$y]['Social Science']) ? $trendHistory[$y]['Social Science'] : (isset($trendHistory[$y]['Business']) ? $trendHistory[$y]['Business'] : []);
    $trendSeriesBus[] = count($b) ? round(array_sum($b)/count($b), 1) : 0;
    $c = isset($trendHistory[$y]['Computing']) ? $trendHistory[$y]['Computing'] : [];
    $trendSeriesComp[] = count($c) ? round(array_sum($c)/count($c), 1) : 0;
}

$subAvg = [];
$subPassRate = [];
if ($snapCount > 0) { 
    foreach($snapSubjects as $idx => $t) { 
        $subAvg[] = round($t / $snapCount, 1); 
        $passRate = ($subjectDetails[$idx]['pass_count'] / $snapCount) * 100;
        $subPassRate[] = round($passRate, 1);
    } 
} else {
    $subAvg = [0,0,0,0,0];
    $subPassRate = [0,0,0,0,0];
}

$chartPayload = [
    'trendY' => $trendLabels, 'tBus' => $trendSeriesBus, 'tComp' => $trendSeriesComp,
    'avgB' => $avgBus, 'avgC' => $avgComp,
    'male' => $snapStats['Male'], 'female' => $snapStats['Female'],
    'grades' => array_values($snapGrades)
];

// Prepare Lecturer Data for Sorting
// Sort by Avg Score Descending
$lecturerList = [];
foreach($lecturerStats as $code => $stat) {
    $lecturerList[] = ['code'=>$code, 'avg'=>$stat['avg'], 'pass'=>$stat['pass_rate'], 'students'=>$stat['students'], 'dist'=>$stat['distinction_rate']];
}
usort($lecturerList, function($a, $b) { return $b['avg'] <=> $a['avg']; });

$titleParts = [];
if($filterProg) $titleParts[] = $filterProg;
if($filterCode) $titleParts[] = $filterCode;
if($filterStatus) $titleParts[] = $filterStatus;
if($filterYear) $titleParts[] = $filterYear;

$dynamicTitle = empty($titleParts) ? "(All Data)" : "(" . implode(" - ", $titleParts) . ")";
$trendTitle = $filterProg ? "($filterProg)" : "(All Programmes)";
?>