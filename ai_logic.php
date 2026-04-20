
<?php
function getGeminiResponse($userInput) {
    //
    $apiKey = "YOUR_API_KEY"; 
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    // We add structured prompts so Gemini can suggest actions based on your SQL tables
    $systemInstruction = "Your name is LesBot. You are a super-intelligent AI for UTeM Lestari. 
    1. If they have maintenance issues, mention the 'Maintenance Request' table.
    2. If they ask about fines, mention 'Student Penalties'.
    3. Keep it brief and cool. Current user is a UTeM Student.";

    $data = ["contents" => [["parts" => [["text" => $systemInstruction . "\n\nStudent: " . $userInput]]]]];
    
    // ... rest of curl logic from ai_logic.php ...

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Crucial for XAMPP
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);

    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Neural link unstable. Try again, friend!";
}
?>