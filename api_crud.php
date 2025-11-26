<?php
/**
 * FILE: api_crud.php
 * PURPOSE: A single, secure API endpoint for all Create, Read (sensitive), Update, and Delete operations.
 * 
 * SECURITY: All operations are blocked unless $_SESSION['role'] is 'admin'.
 * CHANGES: Implemented full logic for 'read_single_user' and 'update_user' tasks, completing User CRUD.
 */

// 1. Load the database connection and session
require 'logic_common.php';

// Set standard response headers
header('Content-Type: application/json');

// Define API response structure
$response = ['success' => false, 'message' => 'An unknown error occurred.', 'data' => null];

// --- CRITICAL SECURITY CHECK (Authorization) ---
// Block ALL CRUD operations if the user is not logged in as 'admin'
if ($_SESSION['role'] !== 'admin') {
    $response['message'] = "Authorization failed. Administrator access is required for this operation.";
    http_response_code(403); // HTTP 403 Forbidden
    echo json_encode($response);
    exit;
}
// --------------------------------------------------

// 2. Utility Function to Safely Rewrite users.php
// This is critical for file-based CRUD.
function rewriteUsersFile($usersArray) {
    // 1. Convert PHP array to string representation
    // We use var_export for a valid PHP array structure
    $usersContent = var_export($usersArray, true);
    
    // 2. Format the final file content string
    $fileContent = "<?php\n\n/**\n * FILE: users.php\n * PURPOSE: Hardcoded user database for authentication.\n * NOTE: This file is automatically managed by the CRUD API.\n */\n\n";
    $fileContent .= "\$users = " . $usersContent . ";\n";
    $fileContent .= "?>";

    // 3. Write the content back to the file
    // Use LOCK_EX for safe, exclusive writing
    if (file_put_contents('users.php', $fileContent, LOCK_EX) !== false) {
        return true;
    }
    return false;
}


// 3. Process Request Data
$input = json_decode(file_get_contents('php://input'), true);
$task = $input['task'] ?? $_POST['task'] ?? null;

if (!$task) {
    $response['message'] = "Missing 'task' parameter.";
    http_response_code(400); // HTTP 400 Bad Request
    echo json_encode($response);
    exit;
}

// 4. Handle CRUD Tasks
try {
    // Load the current user list for all user-related tasks
    if (in_array($task, ['delete_user', 'create_user', 'update_user', 'read_users', 'read_single_user'])) {
        require 'users.php'; // This loads the $users array
    }

    switch ($task) {
        
        // --- READ: Fetching ALL Users ---
        case 'read_users':
            $userList = [];
            foreach ($users as $username => $userData) {
                $userList[] = [
                    'username' => $username,
                    'role' => $userData['role'],
                    'name' => $userData['name']
                ];
            }
            $response['success'] = true;
            $response['message'] = "User list fetched successfully.";
            $response['data'] = $userList;
            break;
            
        // --- READ: Fetching Single User (For Edit Form) ---
        case 'read_single_user':
            $targetUsername = $input['username'] ?? null;
            if (!$targetUsername || !isset($users[$targetUsername])) {
                $response['message'] = "User not found for edit.";
                http_response_code(404);
                break;
            }
            
            $userData = $users[$targetUsername];
            $response['success'] = true;
            $response['message'] = "User data fetched.";
            $response['data'] = [
                'username' => $targetUsername,
                'name' => $userData['name'],
                'role' => $userData['role']
                // HASH is deliberately omitted
            ];
            break;

        // --- CREATE: Create User ---
        case 'create_user':
            $newUsername = trim($input['username'] ?? '');
            $newPassword = $input['password'] ?? '';
            $newName = trim($input['name'] ?? 'New User');
            $newRole = strtolower(trim($input['role'] ?? 'user'));
            
            // Validation
            if (empty($newUsername) || empty($newPassword) || empty($newName)) {
                $response['message'] = "Username, Name, and Password are required."; http_response_code(400); break;
            }
            if (strlen($newPassword) < 8) {
                $response['message'] = "Password must be at least 8 characters."; http_response_code(400); break;
            }
            if (isset($users[$newUsername])) {
                $response['message'] = "Username '{$newUsername}' already exists."; http_response_code(409); break;
            }
            if (!in_array($newRole, ['admin', 'user'])) { $newRole = 'user'; }

            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $users[$newUsername] = [
                'hash' => $passwordHash,
                'role' => $newRole,
                'name' => $newName
            ];
            
            if (rewriteUsersFile($users)) {
                $response['success'] = true;
                $response['message'] = "User '{$newUsername}' created successfully with role '{$newRole}'.";
            } else {
                $response['message'] = "Failed to write data to users.php file. Check file permissions."; http_response_code(500);
            }
            break;

        // --- UPDATE: Update User (NEW LOGIC) ---
        case 'update_user':
            $targetUsername = trim($input['originalUsername'] ?? '');
            $newName = trim($input['name'] ?? '');
            $newPassword = $input['password'] ?? '';
            $newRole = strtolower(trim($input['role'] ?? 'user'));

            // Validation
            if (!$targetUsername || !isset($users[$targetUsername])) {
                $response['message'] = "User not found for update."; http_response_code(404); break;
            }
            if (empty($newName)) {
                $response['message'] = "Display Name is required."; http_response_code(400); break;
            }
            if ($newPassword && strlen($newPassword) > 0 && strlen($newPassword) < 8) {
                $response['message'] = "New password must be at least 8 characters."; http_response_code(400); break;
            }
            if (!in_array($newRole, ['admin', 'user'])) { $newRole = $users[$targetUsername]['role']; } // Keep old role if invalid input

            // Update Name and Role
            $users[$targetUsername]['name'] = $newName;
            $users[$targetUsername]['role'] = $newRole;

            // Update Password (if provided)
            if ($newPassword && strlen($newPassword) >= 8) {
                $users[$targetUsername]['hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            // Rewrite the users.php file
            if (rewriteUsersFile($users)) {
                $response['success'] = true;
                $response['message'] = "User '{$targetUsername}' updated successfully.";
            } else {
                $response['message'] = "Failed to write data to users.php file. Check file permissions."; http_response_code(500);
            }
            break;

        // --- DELETE: Delete User ---
        case 'delete_user':
            $targetUsername = $input['username'] ?? null;
            if (!$targetUsername) {
                $response['message'] = "Missing username for deletion."; http_response_code(400); break;
            }
            
            if (!isset($users[$targetUsername])) {
                $response['message'] = "User '{$targetUsername}' not found."; http_response_code(404); break;
            }
            
            if ($targetUsername === $_SESSION['username']) {
                $response['message'] = "Cannot delete the currently logged-in user."; http_response_code(403);
                break;
            }
            
            unset($users[$targetUsername]);

            if (rewriteUsersFile($users)) {
                $response['success'] = true;
                $response['message'] = "User '{$targetUsername}' deleted successfully.";
            } else {
                $response['message'] = "Failed to write data to users.php file. Check file permissions."; http_response_code(500);
            }
            break;

        default:
            $response['message'] = "Invalid task specified: " . htmlspecialchars($task); http_response_code(400); break;
    }

} catch (Exception $e) {
    $response['message'] = "Database/Server Exception: " . $e->getMessage();
    http_response_code(500);
}

// 5. Send Final JSON Response
echo json_encode($response);
exit;
?>