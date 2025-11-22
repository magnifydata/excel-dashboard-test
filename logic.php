<?php
$csvFile = 'data.csv';

// 1. Data Containers
$allRows = [];       // Master list for Individual Dropdown
$tableRows = [];     // Filtered list for Table & Leaderboard
$uniqueYears = []; 
$headers = [];
$error = "";

// Stats Containers (Snapshot - Responds to Filters)
$snapStats = ['Business'=>['c'=>0,'t'=>0], 'Computing'=>['c'=>0,'t'=>0], 'Male'=>0, 'Female'=>0];
$snapGrades = ['A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'F'=>0]; 
$snapSubjects = [0=>0, 1=>0, 2=>0, 3=>0, 4=>0]; // For Subject Breakdown
$snapCount = 0;

// Trend History (Responds to Programme, Ignores Year)
$trendHistory = [];

// Inputs
$filterProg = isset($_GET['prog']) ? $_GET['prog'] : '';
$filterYear = isset($_GET['year']) ? $_GET['year'] : '';
$sortOrder  = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
$activeTab  = isset($_GET['active_tab']) ? $_GET['active_tab'] : 'list'; 

// Read Data
if (file_exists($csvFile) && ($handle = fopen($csvFile, "r")) !== FALSE) {
    $headers = fgetcsv($handle); 
    while (($row = fgetcsv($handle)) !== FALSE) {
        if(count($row) < 10) continue; 

        $prog = $row[4];
        $year = $row[5]; 
        $gender = $row[3];

        $marks = array_slice($row, 6, 5);
        $marks = array_map('floatval', $marks);
        $avg = count($marks) > 0 ? array_sum($marks) / count($marks) : 0;
        $row[] = number_format($avg, 1); 

        // 1. Master List (Always add for Dropdown)
        $allRows[] = $row;
        if (!in_array($year, $uniqueYears)) { $uniqueYears[] = $year; }

        // 2. Trend Logic (Ignore Year Filter)
        if ($filterProg == '' || $filterProg == $prog) {
            $trendHistory[$year][$prog][] = $avg;
        }

        // 3. Snapshot Logic (Respects ALL Filters)
        $include = true;
        if ($filterProg && $prog !== $filterProg) $include = false;
        if ($filterYear && $year !== $filterYear) $include = false;

        if ($include) {
            $tableRows[] = $row;
            
            // Aggregate Snapshot Stats
            if(isset($snapStats[$prog])) { $snapStats[$prog]['c']++; $snapStats[$prog]['t'] += $avg; }
            if($gender == 'Male') $snapStats['Male']++;
            if($gender == 'Female') $snapStats['Female']++;
            
            // Sum up subject scores for Class Average calculation
            foreach($marks as $idx => $score) { $snapSubjects[$idx] += $score; }
            $snapCount++;

            // Grades
            if($avg >= 80) $g='A'; elseif($avg >= 70) $g='B'; elseif($avg >= 60) $g='C'; elseif($avg >= 50) $g='D'; else $g='F';
            $snapGrades[$g]++;
        }
    }
    fclose($handle);
    $headers[] = "Average";
    sort($uniqueYears);
} else {
    $error = "Error: Could not read data.csv.";
}

// Sorting
$sortFunc = function($a, $b) use ($sortOrder) { return ($sortOrder === 'asc') ? strcasecmp($a[0], $b[0]) : strcasecmp($b[0], $a[0]); };
usort($tableRows, $sortFunc);
usort($allRows, $sortFunc); // Sort Master List too so dropdown is A-Z

// Leaderboard
$leaderboard = $tableRows;
usort($leaderboard, function($a, $b) { return $b[11] <=> $a[11]; });
$top5 = array_slice($leaderboard, 0, 5);

// --- CALCULATIONS ---

// Snapshot Averages
$avgBus = $snapStats['Business']['c'] ? round($snapStats['Business']['t'] / $snapStats['Business']['c'], 1) : 0;
$avgComp = $snapStats['Computing']['c'] ? round($snapStats['Computing']['t'] / $snapStats['Computing']['c'], 1) : 0;

// Trend Lines
$trendLabels = $uniqueYears;
$trendSeriesBus = []; $trendSeriesComp = [];
foreach ($uniqueYears as $y) {
    $b = isset($trendHistory[$y]['Business']) ? $trendHistory[$y]['Business'] : [];
    $trendSeriesBus[] = count($b) ? round(array_sum($b)/count($b), 1) : 0;
    $c = isset($trendHistory[$y]['Computing']) ? $trendHistory[$y]['Computing'] : [];
    $trendSeriesComp[] = count($c) ? round(array_sum($c)/count($c), 1) : 0;
}

// Subject Averages (This was missing/misnamed before)
$subAvg = []; // Used by Tab 3
if ($snapCount > 0) { 
    foreach($snapSubjects as $t) { $subAvg[] = round($t / $snapCount, 1); } 
} else {
    $subAvg = [0,0,0,0,0]; // Prevent JS crash if no data found
}

// Chart Payload (For Tab 2)
$chartPayload = [
    'trendY' => $trendLabels, 'tBus' => $trendSeriesBus, 'tComp' => $trendSeriesComp,
    'avgB' => $avgBus, 'avgC' => $avgComp,
    'male' => $snapStats['Male'], 'female' => $snapStats['Female'],
    'grades' => array_values($snapGrades)
];

// Dynamic Titles
$titleParts = [];
if($filterProg) $titleParts[] = $filterProg;
if($filterYear) $titleParts[] = $filterYear;
$dynamicTitle = empty($titleParts) ? "(All Data)" : "(" . implode(" - ", $titleParts) . ")";
$trendTitle = $filterProg ? "($filterProg)" : "(All Programmes)";
?>