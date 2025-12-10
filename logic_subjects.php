<?php
/**
 * File: logic_subjects.php
 * Purpose: API for Subject Performance Analysis.
 * Changes: Removed Overall Pass/Fail 3D Pie KPI.
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db_connection.php'; 
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    // 1. Get Filters
    if ($action === 'get_filters') {
        $stmtSub = $pdo->query("SELECT DISTINCT subject_code FROM subject_results ORDER BY subject_code ASC");
        $stmtCohort = $pdo->query("SELECT DISTINCT level_cohort FROM subject_results ORDER BY level_cohort ASC");
        
        $statuses = ['Active', 'Graduated', 'Withdrawn']; // Fallback
        try {
            $stmtStatus = $pdo->query("SELECT DISTINCT status FROM students ORDER BY status ASC");
            if($stmtStatus) $statuses = $stmtStatus->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) { /* Ignore if column missing */ }

        echo json_encode([
            'subjects' => $stmtSub->fetchAll(PDO::FETCH_COLUMN), 
            'cohorts' => $stmtCohort->fetchAll(PDO::FETCH_COLUMN),
            'statuses' => $statuses
        ]);
        exit;
    }

    // 2. Get Semesters
    if ($action === 'get_semesters') {
        $cohort = $_GET['cohort'] ?? 'All';
        $sql = "SELECT DISTINCT semester_no FROM subject_results WHERE 1=1";
        $params = [];
        if ($cohort !== 'All') {
            $sql .= " AND level_cohort = ?";
            $params[] = $cohort;
        }
        $sql .= " ORDER BY semester_no ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
        exit;
    }

    // 3. Get Chart Data
    if ($action === 'get_data') {
        $subject = $_GET['subject'] ?? 'All';
        $cohort = $_GET['cohort'] ?? 'All';
        $semester = $_GET['semester'] ?? 'All';

        $whereSQL = " WHERE 1=1";
        $params = [];
        if ($cohort !== 'All') { $whereSQL .= " AND level_cohort = ?"; $params[] = $cohort; }
        if ($semester !== 'All') { $whereSQL .= " AND semester_no = ?"; $params[] = $semester; }

        $mainChart = [];
        $failChart = null;
        $lowestChart = null;
        $scatterChart = null;
        $aceChart = null; 
        $heatmapChart = null; 
        $trendChart = null;
        // $kpiPieChart = null; // REMOVED

        // SCENARIO A: Overview
        if ($subject === 'All') {
            
            // REMOVED: Overall Pass/Fail Breakdown (3D Pie Chart Data)

            // Chart 1: Avg Marks
            $stmt = $pdo->prepare("SELECT subject_code, AVG(marks) as val FROM subject_results $whereSQL GROUP BY subject_code ORDER BY val DESC");
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $mainChart = [
                'type' => 'overview',
                'categories' => array_column($data, 'subject_code'),
                'data' => array_map(fn($v) => round((float)$v, 2), array_column($data, 'val')),
                'title' => 'Average Marks by Subject'
            ];

            // Chart 2: Fail Rates
            $stmt = $pdo->prepare("SELECT subject_code, SUM(CASE WHEN grade = 'F' THEN 1 ELSE 0 END) as failed, SUM(CASE WHEN grade != 'F' THEN 1 ELSE 0 END) as passed FROM subject_results $whereSQL GROUP BY subject_code ORDER BY failed DESC LIMIT 15");
            $stmt->execute($params);
            $fData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $failChart = [
                'categories' => array_column($fData, 'subject_code'),
                'passed' => array_map('intval', array_column($fData, 'passed')),
                'failed' => array_map('intval', array_column($fData, 'failed'))
            ];

            // Chart 3: Lowest Scores
            $stmt = $pdo->prepare("SELECT subject_code, AVG(marks) as val, SUM(CASE WHEN grade = 'F' THEN 1 ELSE 0 END) as failed, SUM(CASE WHEN grade != 'F' THEN 1 ELSE 0 END) as passed FROM subject_results $whereSQL GROUP BY subject_code ORDER BY val ASC LIMIT 15");
            $stmt->execute($params);
            $lData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $lowestChart = [
                'categories' => array_column($lData, 'subject_code'),
                'passed' => array_map('intval', array_column($lData, 'passed')),
                'failed' => array_map('intval', array_column($lData, 'failed'))
            ];

            // Chart 4: Scoring Matrix (Scatter)
            $stmt = $pdo->prepare("SELECT subject_code, AVG(marks) as avg_mark, COUNT(*) as total, SUM(CASE WHEN grade = 'F' THEN 1 ELSE 0 END) as fails FROM subject_results $whereSQL GROUP BY subject_code");
            $stmt->execute($params);
            $sData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $scatterSeries = [];
            foreach($sData as $row) {
                $total = (int)$row['total'];
                if($total == 0) continue;
                $failRate = round(((int)$row['fails'] / $total) * 100, 1);
                $scatterSeries[] = [
                    'name' => $row['subject_code'],
                    'x' => round((float)$row['avg_mark'], 1),
                    'y' => $failRate,
                    'z' => $total
                ];
            }
            $scatterChart = ['data' => $scatterSeries];

            // Chart 5: Top "Ace" Subjects
            $stmt = $pdo->prepare("
                SELECT 
                    subject_code, 
                    SUM(CASE WHEN grade IN ('A', 'A+', 'A-') THEN 1 ELSE 0 END) as ace_count, 
                    COUNT(*) as total_count
                FROM subject_results 
                $whereSQL 
                GROUP BY subject_code
                ORDER BY ace_count DESC
                LIMIT 15
            ");
            $stmt->execute($params);
            $aceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $aceChartData = [];
            foreach($aceData as $row) {
                $total = (int)$row['total_count'];
                if($total == 0) continue;
                $aceRate = round(((int)$row['ace_count'] / $total) * 100, 1);
                $aceChartData[] = [
                    'subject_code' => $row['subject_code'],
                    'ace_rate' => $aceRate,
                    'student_count' => $total
                ];
            }
            $aceChart = [
                'categories' => array_column($aceChartData, 'subject_code'),
                'data' => array_column($aceChartData, 'ace_rate'),
                'counts' => array_column($aceChartData, 'student_count')
            ];

            // Chart 6: Grade Distribution Heatmap
            $stmt = $pdo->prepare("
                SELECT 
                    subject_code, 
                    grade, 
                    COUNT(*) as count
                FROM subject_results 
                $whereSQL 
                GROUP BY subject_code, grade
            ");
            $stmt->execute($params);
            $hData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $standardGrades = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F']; 
            $allGradesInDb = array_unique(array_column($hData, 'grade'));
            
            $presentAndOrderedGrades = [];
            foreach ($standardGrades as $std) {
                if (in_array($std, $allGradesInDb)) {
                    $presentAndOrderedGrades[] = $std;
                }
            }
            foreach ($allGradesInDb as $dbGrade) {
                 if (!in_array($dbGrade, $presentAndOrderedGrades)) {
                     $presentAndOrderedGrades[] = $dbGrade;
                 }
            }

            $allGrades = $presentAndOrderedGrades;
            $allSubjects = array_values(array_unique(array_column($hData, 'subject_code')));
            $heatmapData = [];
            $maxCount = 0;
            $gradeMap = array_flip($allGrades); 

            foreach ($hData as $row) {
                $subjectIndex = array_search($row['subject_code'], $allSubjects);
                $gradeIndex = $gradeMap[$row['grade']] ?? null; 
                $count = (int)$row['count'];

                if ($subjectIndex !== false && $gradeIndex !== null) {
                    $heatmapData[] = [
                        'x' => $gradeIndex, 
                        'y' => $subjectIndex, 
                        'value' => $count
                    ];
                    if ($count > $maxCount) $maxCount = $count;
                }
            }

            $heatmapChart = [
                'grades' => $allGrades,
                'subjects' => $allSubjects,
                'data' => $heatmapData,
                'max_count' => $maxCount
            ];
            
            // Chart 7: Subject Performance Trend
            $whereTrendSQL = " WHERE 1=1";
            $paramsTrend = [];
            if ($cohort !== 'All') { $whereTrendSQL .= " AND level_cohort = ?"; $paramsTrend[] = $cohort; }

            $stmt = $pdo->prepare("
                SELECT 
                    subject_code, 
                    semester_no, 
                    AVG(marks) as avg_mark 
                FROM subject_results 
                $whereTrendSQL
                GROUP BY subject_code, semester_no
                ORDER BY subject_code ASC, semester_no ASC
            ");
            $stmt->execute($paramsTrend);
            $tData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $trendSeries = [];
            $allSemesters = [];
            
            foreach ($tData as $row) {
                $subjectCode = $row['subject_code'];
                $semesterNo = (int)$row['semester_no'];
                $avgMark = round((float)$row['avg_mark'], 2);

                if (!in_array($semesterNo, $allSemesters)) {
                    $allSemesters[] = $semesterNo;
                }

                if (!isset($trendSeries[$subjectCode])) {
                    $trendSeries[$subjectCode] = [
                        'name' => $subjectCode,
                        'data' => []
                    ];
                }
                
                $trendSeries[$subjectCode]['data'][] = ['x' => $semesterNo, 'y' => $avgMark];
            }
            
            sort($allSemesters); 

            $trendChart = [
                'semesters' => array_map(fn($s) => "S{$s}", $allSemesters),
                'series' => array_values($trendSeries)
            ];

        } 
        // SCENARIO B: Detail
        else {
            $subParams = array_merge($params, [$subject]);
            $stmt = $pdo->prepare("SELECT grade, COUNT(*) as count FROM subject_results $whereSQL AND subject_code = ? GROUP BY grade ORDER BY grade ASC");
            $stmt->execute($subParams);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pieData = [];
            foreach($rows as $r) $pieData[] = ['name' => $r['grade'], 'y' => (int)$r['count']];
            $mainChart = ['type' => 'detail', 'data' => $pieData, 'title' => "Grade Distribution: $subject"];
        }

        echo json_encode([
            'main' => $mainChart,
            'failChart' => $failChart,
            'lowestChart' => $lowestChart,
            'scatterChart' => $scatterChart,
            'aceChart' => $aceChart, 
            'heatmapChart' => $heatmapChart,
            'trendChart' => $trendChart,
            // 'kpiPieChart' is removed from the final response
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
    exit;
}
?>