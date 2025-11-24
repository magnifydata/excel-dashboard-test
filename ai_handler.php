<?php
// ai_handler.php - Hybrid SQL + Chat Mode
error_reporting(0); 
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    // --- CONFIGURATION ---
    $apiKey = 'AIzaSyAHKAiHlixV4fIKMdu2M7qs0jBS39J9J_Y'; 
    $dbFile = __DIR__ . '/database.sqlite'; 
    $model = 'gemini-2.0-flash'; 

    // 1. Get Input
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    $question = $input['question'] ?? '';
    if (!$question) { throw new Exception('No question provided'); }

    // 2. The "Hybrid" Prompt
    // We tell Gemini to act as a router: SQL or Chat
    $schema = "
    CONTEXT: You are an AI Assistant for a Student Information System.
    I have a SQLite database:
    1. students (student_id, name, gender, nationality, level_category, level_code, intake_no, status)
    2. subject_results (student_id, subject_code, marks, grade, credit_hours)
    3. semester_performance (student_id, semester_no, gpa, cgpa, academic_status)

    INSTRUCTIONS:
    Determine if the user is asking for DATA (requires SQL) or GENERAL INFO (requires text).
    
    You must return a JSON object with this format:
    {
      \"type\": \"sql\" OR \"chat\",
      \"content\": \"THE_SQL_QUERY\" OR \"THE_CHAT_RESPONSE\"
    }

    RULES FOR SQL:
    - Use LIKE for names.
    - Limit to 5 rows unless asked otherwise.
    - Return ONLY valid SQLite syntax in 'content'.

    RULES FOR CHAT:
    - If the user says 'Hi', 'Help', or asks generic questions (e.g. 'How to improve GPA?'), select type 'chat'.
    - Provide a helpful, professional answer in 'content'.
    ";

    // 3. Call API
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;
    $data = [ "contents" => [[ "parts" => [[ "text" => $schema . "\n\nUser Question: " . $question ]] ]] ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) { throw new Exception("API Error ($httpCode)"); }

    // 4. Parse AI Response
    $json = json_decode($response, true);
    $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // Clean up code blocks if Gemini adds them
    $cleanJson = preg_replace('/^```json\s*|```$/m', '', $rawText);
    $aiResponse = json_decode($cleanJson, true);

    if (!$aiResponse || !isset($aiResponse['type'])) {
        // Fallback if AI didn't output JSON
        throw new Exception("AI response format invalid.");
    }

    // 5. Handle Logic based on Type
    if ($aiResponse['type'] === 'sql') {
        // --- SQL MODE ---
        $sql = trim($aiResponse['content'], "; \t\n\r\0\x0B");
        
        $pdo = new PDO("sqlite:" . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'mode' => 'sql',
            'query' => $sql,
            'data' => $results
        ]);

    } else {
        // --- CHAT MODE ---
        echo json_encode([
            'mode' => 'chat',
            'reply' => $aiResponse['content']
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>