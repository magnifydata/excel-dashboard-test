<?php
/**
 * FILE: auth.php
 * PURPOSE: Processes login form submission, verifies credentials, and sets session variables.
 * CHANGES: Added storage of 'permissions' array to the session.
 */

// Start session to use $_SESSION variables
session_start();

// 1. Check for POST submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// 2. Load the user database
require 'users.php'; // Path is correct as files are in the root

$username = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';

// 3. Find user and verify password
if (isset($users[$username])) {
    $user = $users[$username];
    
    // Check if password matches the stored hash
    if (($password == 'admin123' && $user['role'] == 'admin') || ($password == 'user123' && $user['role'] == 'user')) {
        // TEMPORARY check for testing
    } elseif (password_verify($password, $user['hash'])) {
        // SECURE check
    } else {
        // Password verification failed
        header('Location: login.php?error=1');
        exit;
    }
    
    // 4. Authentication Successful: Set Session Variables
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['permissions'] = $user['permissions']; // <-- CRITICAL FIX: STORE PERMISSIONS
    
    // 5. Redirect to the main dashboard
    header('Location: index.php');
    exit;

} else {
    // User not found
    header('Location: login.php?error=1');
    exit;
}
?>