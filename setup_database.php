<?php
$dbFile = 'database.sqlite';
$csvStudents = 'students.csv';
$csvSubjects = 'subjects.csv'; // Make sure your file matches this name!
$csvSemester = 'semester.csv';

// 1. RESET: Delete old database to prevent duplicates
if (file_exists($dbFile)) {
    unlink($dbFile);
    echo "♻️ Old database deleted. Starting fresh...<br>";
}

// 2. Connect
try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("❌ Connection Failed: " . $e->getMessage()); }

// 3. Create Tables
$pdo->exec("CREATE TABLE students (
    student_id TEXT PRIMARY KEY, name TEXT, admission_no TEXT, gender TEXT, nationality TEXT, 
    level_code TEXT, intake_no TEXT, level_cohort TEXT, spm_bm TEXT, bka TEXT, 
    level_category TEXT, status TEXT, pass_bm TEXT
)");

$pdo->exec("CREATE TABLE subject_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT, student_id TEXT, level_cohort TEXT, 
    semester_no INTEGER, subject_code TEXT, completion_status TEXT, active_status TEXT, 
    credit_hours INTEGER, marks REAL, grade TEXT
)");

$pdo->exec("CREATE TABLE semester_performance (
    id INTEGER PRIMARY KEY AUTOINCREMENT, student_id TEXT, level_cohort TEXT, 
    semester_no INTEGER, session TEXT, gpa REAL, cgpa REAL, academic_status TEXT, 
    academic_awards TEXT, credits_earned INTEGER, credits_remaining INTEGER, 
    credits_progression INTEGER
)");

// 4. Import Function
function import($pdo, $file, $sql, $map) {
    if (!file_exists($file)) { echo "<span style='color:red'>❌ Error: $file not found!</span><br>"; return; }
    
    $h = fopen($file, "r");
    fgetcsv($h); // Skip header
    $stmt = $pdo->prepare($sql);
    $c = 0;
    while (($row = fgetcsv($h)) !== FALSE) {
        if(count($row) < 2) continue;
        $data = [];
        foreach($map as $idx) $data[] = isset($row[$idx]) ? trim($row[$idx]) : null;
        try { $stmt->execute($data); $c++; } catch(Exception $e){}
    }
    fclose($h);
    echo "✅ Imported <strong>$c</strong> rows from $file<br>";
}

// 5. Run Imports
echo "<hr><strong>Importing Data...</strong><br>";

// Students (Map based on your columns)
// Database expects: student_id, name, admission_no, gender, nationality, level_code, intake_no, level_cohort, spm_bm, bka, level_category, status, pass_bm
// CSV Columns: No(0), Name(1), ID(2), Adm(3), Gen(4), Nat(5), Code(6), Intake(7), Cohort(8), SPM(9), BKA(10), Cat(11), Stat(12), PassBM(13)
$sql = "INSERT OR IGNORE INTO students VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
import($pdo, $csvStudents, $sql, [2,1,3,4,5,6,7,8,9,10,11,12,13]);

// Subjects
// Database: student_id, level_cohort, semester_no, subject_code, completion, active, credit, marks, grade
// CSV: No(0), ID(1), Cohort(2), Sem(3), Code(4), Status1(5), Status2(6), Credit(7), Mark(8), Grade(9)
$sql = "INSERT INTO subject_results (student_id, level_cohort, semester_no, subject_code, completion_status, active_status, credit_hours, marks, grade) VALUES (?,?,?,?,?,?,?,?,?)";
import($pdo, $csvSubjects, $sql, [1,2,3,4,5,6,7,8,9]);

// Semester
// Database: student_id, level_cohort, semester_no, session, gpa, cgpa, status, awards, earned, remaining, progression
// CSV: No(0), ID(1), Cohort(2), Sem(3), Sess(4), GPA(5), CGPA(6), Stat(7), Award(8), Earn(9), Rem(10), Prog(11)
$sql = "INSERT INTO semester_performance (student_id, level_cohort, semester_no, session, gpa, cgpa, academic_status, academic_awards, credits_earned, credits_remaining, credits_progression) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
import($pdo, $csvSemester, $sql, [1,2,3,4,5,6,7,8,9,10,11]);

// 6. Final Verification
echo "<hr><strong>📊 Final Database Status:</strong><br>";
$counts = [
    'students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'subject_results' => $pdo->query("SELECT COUNT(*) FROM subject_results")->fetchColumn(),
    'semester_performance' => $pdo->query("SELECT COUNT(*) FROM semester_performance")->fetchColumn()
];

foreach($counts as $table => $count) {
    $color = $count > 0 ? 'green' : 'red';
    echo "Table <strong>$table</strong>: <span style='color:$color'>$count records</span><br>";
}
?>