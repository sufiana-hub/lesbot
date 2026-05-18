<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="google-site-verification" content="ZzO5CLldp_eWizT5IFW6oUvs_ViGd49GW_un7BfK1qc" />
  <meta name="description" content="LesBot - UTeM Lestari Dormitory Management System Student Project">
  <meta name="robots" content="index, follow">
  <meta charset="utf-8">
  <title>LesBot | Neural Hub</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
        --lesbot-cyan: #00d4ff; 
        --obsidian: #080a0f; 
        --glass-bg: rgba(8, 10, 15, 0.7);
        --glass-border: rgba(0, 212, 255, 0.2);
    }

    body {
        background-color: var(--obsidian);
        /* Deep neural background glow */
        background-image: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.08) 0%, transparent 70%);
        color: #ffffff;
        font-family: 'Rajdhani', sans-serif;
        margin: 0;
        overflow-x: hidden;
    }

    /* --- Floating Glass Navigation --- */
    #header {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 50px;
        margin: 20px auto;
        padding: 10px 30px;
        width: 90%;
        max-width: 1200px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    .logo-text {
        font-family: 'Orbitron', sans-serif;
        color: var(--lesbot-cyan);
        font-weight: 900;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0;
        font-size: 1.2rem;
    }

    .nav-links-modern {
        font-family: 'Orbitron', sans-serif;
        font-weight: 400;
        font-size: 0.7rem;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        margin-right: 20px;
        transition: 0.3s;
    }

    .nav-links-modern:hover {
        color: var(--lesbot-cyan);
        text-shadow: 0 0 10px var(--lesbot-cyan);
    }

    /* --- Hero Section --- */
    #hero {
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding-top: 80px;
    }

#hero h1 {
    font-family: 'Orbitron';
    font-weight: 900;
    font-size: 4.5rem;
    margin-bottom: 10px;
    background: linear-gradient(to right, #fff, var(--lesbot-cyan));
    
    /* Standard property for compatibility */
    background-clip: text; 
    
    /* Vendor-specific prefixes */
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

    #hero h2 {
        font-family: 'Rajdhani';
        font-weight: 300;
        letter-spacing: 12px;
        color: rgba(255,255,255,0.4);
        text-transform: uppercase;
        font-size: 1rem;
    }

    /* --- Auth Buttons --- */
    .btn-signup-pill {
        border: 1px solid var(--lesbot-cyan);
        color: var(--lesbot-cyan);
        border-radius: 50px;
        padding: 5px 20px;
        font-family: 'Orbitron';
        font-size: 0.7rem;
        text-decoration: none;
        transition: 0.3s;
    }

    .btn-signup-pill:hover {
        background: var(--lesbot-cyan);
        color: var(--obsidian);
        box-shadow: 0 0 20px var(--lesbot-cyan);
    }

    .btn-login-pill {
        background: #ffffff;
        color: var(--obsidian);
        border-radius: 50px;
        padding: 6px 25px;
        font-family: 'Orbitron';
        font-size: 0.7rem;
        font-weight: 900;
        text-decoration: none;
        transition: 0.3s;
    }

    .btn-login-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255,255,255,0.3);
    }

    /* --- Chatbot Styling Refined --- */
    .glass-card {
        background: rgba(8, 10, 15, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.8);
    }
  </style>
</head>

<body>

<header id="header">
    <h1 class="logo-text">LESBOT<span style="color:#fff">•</span></h1>

    <div class="d-flex align-items-center"> 
        <a href="index.php" class="nav-links-modern">UTAMA</a>
        <a href="javascript:void(0)" onclick="toggleLesBot()" class="nav-links-modern">CHATBOT</a>
        <a href="https://www.utem.edu.my/en/" target="_blank" class="nav-links-modern">UTeM <i class="bi bi-box-arrow-up-right small"></i></a>
        
        <div class="ms-3 d-flex gap-2 align-items-center">
            <a href="signup.php" class="btn-signup-pill">SIGNUP</a>
            <a href="login.php" class="btn-login-pill">LOGIN</a>
        </div>
    </div>
</header>

<section id="hero">
    <div class="container">
        <h1>INNOVATE <span style="color: var(--lesbot-cyan);">LIMITLESS</span></h1>
        <h2>Lestari Bot Dormitory System</h2>
        <div class="mt-4">
            <p class="text-white-50 small" style="letter-spacing: 2px;">NEURAL COMMAND CENTER v3.0 ONLINE</p>
        </div>
    </div>
</section>

<div id="lesbot-chat-container" class="glass-card shadow-lg" style="position: fixed; bottom: 30px; right: 30px; width: 350px; display: none; z-index: 9999;">
    <div class="card-header d-flex justify-content-between align-items-center p-3 border-bottom border-secondary">
        <span style="font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 2px;">NEURAL LINK ACTIVE</span>
        <button onclick="toggleLesBot()" class="btn-close btn-close-white" style="font-size: 0.6rem;"></button>
    </div>
    <div id="chat-body" class="p-3" style="height: 400px; overflow-y: auto; font-family: 'Rajdhani'; font-size: 0.9rem;">
        <div class="bot-msg mb-3">
            <small style="color: var(--lesbot-cyan); font-family: 'Orbitron'; font-size: 0.6rem;">LesBot:</small><br>
            Greetings, friend. How can I assist your stay at Lestari today?
        </div>
    </div>
    <div class="p-3 bg-transparent border-top border-secondary">
        <div class="input-group">
            <input type="text" id="user-msg" class="form-control bg-dark text-white border-secondary small" placeholder="Send message..." style="font-size: 0.8rem;">
            <button class="btn btn-outline-info" onclick="sendNeuralMessage()"><i class="bi bi-send"></i></button>
        </div>
    </div>
</div>

<script>
function toggleLesBot() {
    const chatContainer = document.getElementById('lesbot-chat-container');
    chatContainer.style.display = (chatContainer.style.display === "none" || chatContainer.style.display === "") ? "block" : "none";
}

// Replace the script in your index.php with this:
function sendNeuralMessage() {
    const msgInput = document.getElementById('user-msg');
    const chatBody = document.getElementById('chat-body');
    const msg = msgInput.value.trim();
    if (!msg) return;
    
    // 1. Show User Message
    chatBody.innerHTML += `<div class='text-end mb-3'><small class='text-info' style='font-family:Orbitron; font-size:0.6rem;'>YOU:</small><br><div class='d-inline-block p-2 rounded' style='background:rgba(0,212,255,0.1); border:1px solid #00d4ff;'>${msg}</div></div>`;
    msgInput.value = '';

    // 2. Show "Thinking" animation
    const loaderId = "loader-" + Date.now();
    chatBody.innerHTML += `<div id='${loaderId}' class='mb-3'><small style='color: #00d4ff; font-family:Orbitron; font-size:0.6rem;'>LesBot is thinking...</small></div>`;
    chatBody.scrollTop = chatBody.scrollHeight;

    // 3. Request to PHP
    fetch('chat_process.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
    })
    .then(response => response.text())
    .then(data => {
        // Remove loader
        document.getElementById(loaderId).remove();
        
        // Show AI Response
        chatBody.innerHTML += `<div class='mb-3'><small style='color: #00d4ff; font-family:Orbitron; font-size:0.6rem;'>LesBot:</small><br><div class='p-2 rounded' style='background:rgba(255,255,255,0.05); border-left:3px solid #00d4ff;'>${data}</div></div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    });
}

</script>

<div id="lesbot-chat-container" class="glass-card shadow-lg" style="position: fixed; bottom: 30px; right: 30px; width: 350px; display: none; z-index: 9999; border: 1px solid var(--lesbot-cyan);">
    <div class="card-header d-flex justify-content-between align-items-center p-3 border-bottom border-secondary">
        <span style="font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 2px;">LESBOT 24/7 HELPFLOW</span>
        <button onclick="toggleLesBot()" class="btn-close btn-close-white" style="font-size: 0.6rem;"></button>
    </div>
    <div id="chat-body" class="p-3" style="height: 350px; overflow-y: auto; font-family: 'Rajdhani';">
        <div class="mb-3"><small class="text-info">LesBot:</small><br>Identity verified. How can I assist you tonight?</div>
    </div>
    <div class="p-3 border-top border-secondary">
        <div class="input-group">
            <input type="text" id="user-msg" class="form-control bg-dark text-white border-secondary small" placeholder="Ask anything...">
            <button class="btn btn-outline-info" onclick="sendNeuralMessage()"><i class="bi bi-send"></i></button>
        </div>
    </div>
</div>

<button onclick="toggleLesBot()" style="position: fixed; bottom: 30px; right: 30px; border-radius: 50%; width: 60px; height: 60px; background: var(--lesbot-cyan); border: none; box-shadow: 0 0 20px var(--lesbot-cyan); z-index: 9998;">
    <i class="bi bi-robot fs-3 text-dark"></i>
</button>

<?php include 'chatbot_component.php'; ?>

</body>
</html>