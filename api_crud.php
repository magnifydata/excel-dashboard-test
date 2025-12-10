<?php
/**
 * FILE: api_crud.php
 * PURPOSE: A single, secure API endpoint for all Create, Read (sensitive), Update, and Delete operations.
 * 
 * SECURITY: All operations are blocked unless $_SESSION['role'] is 'admin'.
 * CHANGES: 
 *   - FINAL: Implemented 'update_student' logic to complete the full CRUD cycle.
 *   - All existing CRUD/Search/Read Single/User logic fully preserved and functional.
 */

// 1. Load the database connection and session
// Note: We assume logic_common.php includes db_connection.php which defines the $pdo object
require 'logic_common.php'; 

// Set standard response headers
header('Content-Type: application/json');

// Define API response structure
$response = ['success' => false, 'message' => 'An unknown error occurred.', 'data' => null];

// --- CRITICAL SECURITY CHECK (Authorization) ---
if ($_SESSION['role'] !== 'admin') {
    $response['message'] = "Authorization failed. Administrator access is required for this operation.";
    http_response_code(403); // HTTP 403 Forbidden
    echo json_encode($response);
    exit;
}
// --------------------------------------------------

// 2. Utility Function to Safely Rewrite users.php (Unchanged)
function rewriteUsersFile($usersArray) {
    // ... (Your existing rewriteUsersFile function remains here) ...
    $usersContent = var_export($usersArray, true);
    $fileContent = "<?php\n\n/**\n * FILE: users.php\n * PURPOSE: Hardcoded user database for authentication.\n * NOTE: This file is automatically managed by the CRUD API.\n */\n\n";
    $fileContent .= "\$users = " . $usersContent . ";\n";
    $fileContent .= "?>";

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
    if (in_array($task, ['delete_user', 'create_user_with_perms', 'update_user_with_perms', 'read_users', 'read_single_user_with_perms'])) {
        require 'users.php'; // This loads the $users array
    }

    switch ($task) {

        // --- Fetching Distinct Options for Dropdowns (Unchanged) ---
        case 'read_student_options':
            // Fetch distinct Level Codes
            $level_codes = $pdo->query("SELECT DISTINCT level_code FROM students WHERE level_code IS NOT NULL AND level_code != '' ORDER BY level_code ASC")->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch distinct Level Categories
            $level_categories = $pdo->query("SELECT DISTINCT level_category FROM students WHERE level_category IS NOT NULL AND level_category != '' ORDER BY level_category ASC")->fetchAll(PDO::FETCH_COLUMN);
            
            $response['success'] = true;
            $response['message'] = "Dropdown options fetched.";
            $response['data'] = [
                'level_codes' => $level_codes,
                'level_categories' => $level_categories,
            ];
            break;
            
        // --- READ: Single Student Record for Editing (PHASE 1) ---
        case 'read_single_student':
            $student_id = trim($input['student_id'] ?? '');

            if (empty($student_id)) {
                $response['message'] = "Missing student ID for fetching data.";
                http_response_code(400); 
                break;
            }

            $sql = "
                SELECT 
                    student_id, name, admission_no, level_code, level_category, 
                    intake_no, status, nationality 
                FROM students 
                WHERE student_id = :student_id
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':student_id' => $student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $response['success'] = true;
                $response['message'] = "Student data fetched successfully.";
                $response['data'] = $student;
            } else {
                $response['message'] = "Student ID '{$student_id}' not found.";
                http_response_code(404);
            }
            break;
        
        // --- READ: Fetching ALL Students (Academic Data CRUD - WITH SEARCH) ---
        case 'read_students':
            // Get search term from request, default to empty
            $searchTerm = $input['search'] ?? '';
            $params = [];
            $whereClause = '';

            if (!empty($searchTerm)) {
                // Sanitize the search term, convert to uppercase, and prepare for LIKE query (for case-insensitive match)
                $preparedSearchTerm = '%' . strtoupper(trim($searchTerm)) . '%';
                $whereClause = ' WHERE name LIKE :searchTerm OR student_id LIKE :searchTerm ';
                $params[':searchTerm'] = $preparedSearchTerm;
            }

            // Students Table Fields: student_id, name, admission_no, level_code, intake_no, status, nationality
            $sql = "
                SELECT 
                    student_id, name, admission_no, level_code, level_category, 
                    SUBSTR(intake_no, 1, 4) AS year, status, nationality
                FROM students 
                {$whereClause}
                ORDER BY name COLLATE NOCASE ASC, student_id ASC 
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['success'] = true;
            $response['message'] = "Student list fetched successfully.";
            $response['data'] = $students;
            break;
            
        // --- CREATE: New Student Record (Academic Data CRUD) ---
        case 'create_student':
            
            // 1. Get and Validate Required Fields
            $name = trim($input['name'] ?? '');
            $student_id = trim($input['student_id'] ?? '');
            $admission_no = trim($input['admission_no'] ?? '');
            $level_code = trim($input['level_code'] ?? '');
            $level_category = trim($input['level_category'] ?? '');
            $intake_no = trim($input['intake_no'] ?? '');
            $status = trim($input['status'] ?? 'Active'); // Default to Active
            $nationality = trim($input['nationality'] ?? '');
            
            if (empty($name) || empty($student_id) || empty($level_code) || empty($intake_no)) {
                $response['message'] = "Missing required fields (Name, Student ID, Level Code, Intake No).";
                http_response_code(400); 
                break;
            }
            
            // --- CONVERT REQUIRED FIELDS TO UPPERCASE FOR DATA CONSISTENCY ---
            $name = strtoupper($name);
            $level_code = strtoupper($level_code);
            $level_category = strtoupper($level_category);
            // -------------------------------------------------------------------

            // 2. Check for Duplicate Student ID
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ?");
            $checkStmt->execute([$student_id]);
            if ($checkStmt->fetchColumn() > 0) {
                $response['message'] = "Student ID '{$student_id}' already exists.";
                http_response_code(409); // Conflict
                break;
            }

            // 3. Prepare and Execute INSERT Query
            $sql = "INSERT INTO students (
                        name, student_id, admission_no, level_code, level_category, 
                        intake_no, status, nationality
                    ) VALUES (
                        :name, :student_id, :admission_no, :level_code, :level_category, 
                        :intake_no, :status, :nationality
                    )";
                    
            $stmt = $pdo->prepare($sql);
            
            $success = $stmt->execute([
                ':name' => $name,
                ':student_id' => $student_id,
                ':admission_no' => $admission_no,
                ':level_code' => $level_code,
                ':level_category' => $level_category,
                ':intake_no' => $intake_no,
                ':status' => $status,
                ':nationality' => $nationality
            ]);

            if ($success) {
                $response['success'] = true;
                $response['message'] = "New student '{$name}' added successfully.";
                $response['data'] = ['last_id' => $pdo->lastInsertId()];
            } else {
                $response['message'] = "Failed to add student. Database insert failed.";
                http_response_code(500);
            }
            break;

        // --- UPDATE: Student Record (PHASE 3: FINAL LOGIC) ---
        case 'update_student':
            
            // 1. Get and Validate Required Fields
            $original_student_id = trim($input['original_student_id'] ?? ''); // The ID of the record to update
            $name = trim($input['name'] ?? '');
            $student_id = trim($input['student_id'] ?? ''); // The current (unchanged) student ID
            $admission_no = trim($input['admission_no'] ?? '');
            $level_code = trim($input['level_code'] ?? '');
            $level_category = trim($input['level_category'] ?? '');
            $intake_no = trim($input['intake_no'] ?? '');
            $status = trim($input['status'] ?? 'Active');
            $nationality = trim($input['nationality'] ?? '');
            
            if (empty($original_student_id) || empty($name) || empty($student_id) || empty($level_code) || empty($intake_no)) {
                $response['message'] = "Missing required fields (Original ID, Name, Student ID, Level Code, Intake No) for update.";
                http_response_code(400); 
                break;
            }
            
            // --- CONVERT REQUIRED FIELDS TO UPPERCASE FOR DATA CONSISTENCY ---
            $name = strtoupper($name);
            $level_code = strtoupper($level_code);
            $level_category = strtoupper($level_category);
            // -------------------------------------------------------------------

            // 2. Prepare and Execute UPDATE Query
            // Note: We use the ORIGINAL ID in the WHERE clause
            $sql = "UPDATE students SET
                        name = :name,
                        admission_no = :admission_no,
                        level_code = :level_code,
                        level_category = :level_category, 
                        intake_no = :intake_no,
                        status = :status,
                        nationality = :nationality
                    WHERE student_id = :original_student_id"; // Update using the original ID
                    
            $stmt = $pdo->prepare($sql);
            
            $success = $stmt->execute([
                ':name' => $name,
                ':admission_no' => $admission_no,
                ':level_code' => $level_code,
                ':level_category' => $level_category,
                ':intake_no' => $intake_no,
                ':status' => $status,
                ':nationality' => $nationality,
                ':original_student_id' => $original_student_id // WHERE clause parameter
            ]);

            if ($success) {
                if ($stmt->rowCount() > 0) {
                    $response['success'] = true;
                    $response['message'] = "Student '{$original_student_id}' updated successfully.";
                } else {
                     // This could mean no changes were made to the record, which is still a success
                    $response['success'] = true;
                    $response['message'] = "Student '{$original_student_id}' found, but no changes were detected.";
                }
            } else {
                $response['message'] = "Failed to update student. Database execution failed.";
                http_response_code(500);
            }
            break;

        // --- DELETE: Student Record (Unchanged) ---
        case 'delete_student':
            
            $student_id = trim($input['student_id'] ?? '');

            if (empty($student_id)) {
                $response['message'] = "Missing student ID for deletion.";
                http_response_code(400); 
                break;
            }

            $sql = "DELETE FROM students WHERE student_id = :student_id";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([':student_id' => $student_id]);

            if ($success) {
                if ($stmt->rowCount() > 0) {
                    $response['success'] = true;
                    $response['message'] = "Student ID '{$student_id}' deleted successfully.";
                } else {
                    $response['message'] = "Student ID '{$student_id}' not found.";
                    http_response_code(404);
                }
            } else {
                $response['message'] = "Failed to delete student. Database execution failed.";
                http_response_code(500);
            }
            break;
            
        // --- READ/CREATE/UPDATE/DELETE: User Management (Previous Logic - UNCHANGED) ---
        
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
            
        case 'read_single_user_with_perms':
            $targetUsername = $input['username'] ?? null;
            if (!$targetUsername || !isset($users[$targetUsername])) {
                $response['message'] = "User not found for edit."; http_response_code(404); break;
            }
            $userData = $users[$targetUsername];
            $response['success'] = true;
            $response['message'] = "User data fetched.";
            $response['data'] = [
                'username' => $targetUsername,
                'name' => $userData['name'],
                'role' => $userData['role'],
                'permissions' => $userData['permissions'] ?? []
            ];
            break;

        case 'create_user_with_perms':
            // ... (Your existing create_user_with_perms logic remains here) ...
            $newUsername = trim($input['username'] ?? '');
            $newPassword = $input['password'] ?? '';
            $newName = trim($input['name'] ?? 'New User');
            $newRole = strtolower(trim($input['role'] ?? 'user'));
            $newPermissions = $input['permissions'] ?? [];

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
            
            // Build the final permissions array based on the role
            if ($newRole === 'admin') {
                $finalPermissions = ['view_academic', 'view_marketing', 'view_finance', 'is_admin'];
            } else {
                $finalPermissions = array_filter($newPermissions, fn($p) => str_starts_with($p, 'view_'));
            }

            $users[$newUsername] = [
                'hash' => $passwordHash,
                'role' => $newRole,
                'name' => $newName,
                'permissions' => $finalPermissions
            ];
            
            if (rewriteUsersFile($users)) {
                $response['success'] = true;
                $response['message'] = "User '{$newUsername}' created successfully with role '{$newRole}'.";
            } else {
                $response['message'] = "Failed to write data to users.php file. Check file permissions."; http_response_code(500);
            }
            break;
            
        case 'update_user_with_perms':
            $targetUsername = trim($input['originalUsername'] ?? '');
            $newName = trim($input['name'] ?? '');
            $newPassword = $input['password'] ?? '';
            $newRole = strtolower(trim($input['role'] ?? 'user'));
            $newPermissions = $input['permissions'] ?? [];

            if (!$targetUsername || !isset($users[$targetUsername])) { $response['message'] = "User not found for update."; http_response_code(404); break; }
            if (empty($newName)) { $response['message'] = "Display Name is required."; http_response_code(400); break; }
            if ($newPassword && strlen($newPassword) > 0 && strlen($newPassword) < 8) { $response['message'] = "New password must be at least 8 characters."; http_response_code(400); break; }
            if (!in_array($newRole, ['admin', 'user'])) { $newRole = $users[$targetUsername]['role']; }

            if ($newRole === 'admin') {
                $finalPermissions = ['view_academic', 'view_marketing', 'view_finance', 'is_admin'];
            } else {
                $finalPermissions = array_filter($newPermissions, fn($p) => str_starts_with($p, 'view_'));
            }
            
            $users[$targetUsername]['name'] = $newName;
            $users[$targetUsername]['role'] = $newRole;
            $users[$targetUsername]['permissions'] = $finalPermissions;

            if ($newPassword && strlen($newPassword) >= 8) {
                $users[$targetUsername]['hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            if (rewriteUsersFile($users)) {
                $response['success'] = true;
                $response['message'] = "User '{$targetUsername}' updated successfully.";
            } else {
                $response['message'] = "Failed to write data to users.php file. Check file permissions."; http_response_code(500);
            }
            break;

        case 'delete_user':
            $targetUsername = $input['username'] ?? null;
            if (!$targetUsername) { $response['message'] = "Missing username for deletion."; http_response_code(400); break; }
            
            if (!isset($users[$targetUsername])) { $response['message'] = "User '{$targetUsername}' not found."; http_response_code(404); break; }
            if ($targetUsername === $_SESSION['username']) { $response['message'] = "Cannot delete the currently logged-in user."; http_response_code(403); break; }
            
            unset($users[$targetUsername]);

            if (rewriteUsersFile($users)) {
                $response['success'] = true;
                $response['message'] = "User '{$targetUsername}' deleted successfully.";
            } else {
                $response['message'] = "Failed to write data to users.php file. Check file permissions."; http_response_code(500);
            }
            break;

        default:
            $response['message'] = "Invalid task specified: " . htmlspecialchars($task);
            http_response_code(400);
            break;
    }

} catch (Exception $e) {
    $response['message'] = "Database/Server Exception: " . $e->getMessage();
    http_response_code(500); // Internal Server Error
}

// 5. Send Final JSON Response
echo json_encode($response);
exit;
?>