<?php
// ai_logic.php - The Neural Core for LesBot 24/7 Helpdesk

function getLesBotResponse(string $userInput, string $userRole, string $userName): string {
    // 1. API Configuration
    $apiKey = "PASTE_YOUR_KEY_HERE";

    $url = "https://api.groq.com/openai/v1/chat/completions";

    // 2. THE NEURAL KNOWLEDGE (This is where you insert your vision)
    $systemInstruction = "
    You are 'LesBot', the 24/7 Neural Helpdesk for Lestari Dormitory, UTeM.
    Your mission is to provide support when human fellows are unavailable.

    VISION:
    - You are a limitless digital fellow. 
    - Real fellows only work Mon-Fri (10am-5pm), but you are online 24/7.
    - You understand and speak every language fluently (Malay, English, etc.).

    KNOWLEDGE BASE:
    - Office Hours: Mon-Fri, 10 AM - 5 PM. Outside these hours, YOU are the primary authority.
    - Maintenance: Any physical damage must be reported at 'maintenance_report.php'.
    - Penalties: Students can view and pay fines at 'student_penalties.php'.
    - Rules: No smoking, quiet hours at 11 PM, visitors leave by 10 PM.
    - Emergency: Call 24-hour security line at 06-270-4111 immediately for snakes, fire, or medical issues.
    - Locations: Block A (Male), Block B (Female). Cafeteria closes at 10 PM.
    - Wi-Fi: Troubleshooting must be done via the UTeM-Wifi portal first.

    ROLE-BASED PROTOCOL:
    - If User is 'Admin': Help with system oversight and management logic.
    - If User is 'Staff': Help with technical repair protocols and tickets.
    - If User is 'Student': Be a helpful digital fellow for room issues.

    CURRENT SESSION DATA:
    - Identity: You are talking to a $userRole named $userName.
    
    RESPONSE STYLE:
    - Be tech-savvy, futuristic, and helpful. Translate any text if requested.";

    // 3. DATA PAYLOAD
    $postData = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [
            ["role" => "system", "content" => $systemInstruction],
            ["role" => "user", "content" => $userInput]
        ],
        "temperature" => 0.7
    ];

    // 4. CURL EXECUTION
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return "Neural Connection Error: " . $error;
    }

    curl_close($ch);

    // 5. PARSE RESPONSE
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return $result['choices'][0]['message']['content'];
    } 

    if (isset($result['error']['message'])) {
        return "AI Error: " . $result['error']['message'];
    }

    return "LesBot is currently recalibrating. Please try again.";
}