<?php
/**
 * --------------------------------------------------------------------------
 * FILE: logic_common.php
 * PURPOSE: 
 *   1. Establishes the connection to the SQLite Database.
 *   2. Sets performance settings (Memory Limit).
 *   3. Fetches unique values for the Global Filter Dropdowns (Year, Programme, Code, Status).
 *   CHANGES: Added Session Start and Authentication Check.
 * 
 * USED BY: 
 *   - All Tabs (Required at the top of index.php)
 * --------------------------------------------------------------------------
 */

// --- SECURITY: START SESSION AND CHECK AUTHENTICATION ---
session_start();

// Define the pages that DON'T require login
$publicPages = ['login.php', 'auth.php']; 

// Get the current file name
$currentPage = basename($_SERVER['PHP_SELF']);

// Redirect to login page if not logged in and the current page requires auth
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (!in_array($currentPage, $publicPages)) {
        header('Location: login.php');
        exit;
    }
}
// --------------------------------------------------------

// --- PERFORMANCE SETTINGS ---
ini_set('memory_limit', '512M');
ini_set('display_errors', 0);

$dbFile = 'database.sqlite';
$error = "";

// Dropdown Containers
$uniqueYears = [];
$uniqueProgs = [];
$uniqueCodes = [];
$uniqueStatus = [];

// Inputs (Global)
$filterProg = isset($_GET['prog']) ? $_GET['prog'] : '';
$filterYear = isset($_GET['year']) ? $_GET['year'] : '';
$filterCode = isset($_GET['code']) ? $_GET['code'] : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$sortOrder  = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
$activeTab  = isset($_GET['active_tab']) ? $_GET['active_tab'] : 'list'; 

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // GET DROPDOWN OPTIONS (Lightweight Queries)
    $uniqueYears = $pdo->query("SELECT DISTINCT SUBSTR(intake_no, 1, 4) as yr FROM students WHERE yr IS NOT NULL AND yr != '' ORDER BY yr DESC")->fetchAll(PDO::FETCH_COLUMN);
    $uniqueProgs = $pdo->query("SELECT DISTINCT level_category FROM students WHERE level_category IS NOT NULL AND level_category != '' ORDER BY level_category")->fetchAll(PDO::FETCH_COLUMN);
    $uniqueCodes = $pdo->query("SELECT DISTINCT level_code FROM students WHERE level_code IS NOT NULL AND level_code != '' ORDER BY level_code")->fetchAll(PDO::FETCH_COLUMN);
    $uniqueStatus = $pdo->query("SELECT DISTINCT status FROM students WHERE status IS NOT NULL AND status != '' ORDER BY status")->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}

// Helper for Titles
$titleParts = [];
if($filterProg) $titleParts[] = $filterProg;
if($filterCode) $titleParts[] = $filterCode;
if($filterStatus) $titleParts[] = $filterStatus;
if($filterYear) $titleParts[] = $filterYear;

$dynamicTitle = empty($titleParts) ? "(All Data)" : "(" . implode(" - ", $titleParts) . ")";
?>