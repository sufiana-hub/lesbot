<!DOCTYPE html>
<html lang="en">
<head>
  <title>LesBot | Neural Command Center</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body style="display: block;">
<style>
    :root {
        --lesbot-black: #1a1a1a;
        --lesbot-cyan: #00d4ff; /* The vibrant blue from your reference */
        --lesbot-border: #e0e0e0;
    }

    #header {
        background: #ffffff;
        border-bottom: 1px solid var(--lesbot-border);
        padding: 15px 0;
    }

    .logo-text {
        font-family: 'Orbitron', sans-serif;
        color: var(--lesbot-black);
        font-weight: 900;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0;
    }

    /* Modern Navigation Links */
    .nav-links-modern {
        font-family: 'Rajdhani', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        color: #444;
        text-decoration: none;
        margin-right: 25px;
        transition: 0.2s;
    }

    .nav-links-modern:hover {
        color: var(--lesbot-cyan);
    }

    /* Button Styles from image_0b7f42.png */
    .btn-signup-pill {
        border: 2px solid var(--lesbot-cyan);
        color: var(--lesbot-cyan);
        border-radius: 50px;
        padding: 8px 25px;
        font-weight: 700;
        text-decoration: none;
        transition: 0.3s;
    }

    .btn-login-pill {
        background: var(--lesbot-cyan);
        color: #ffffff;
        border-radius: 50px;
        padding: 10px 30px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
        transition: 0.3s;
    }

    .btn-login-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 212, 255, 0.4);
    }
</style>

<header id="header" class="fixed-top shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    
    <h1 class="logo-text">LESBOT <span style="color: var(--lesbot-cyan);">•</span></h1>

    <div class="d-flex align-items-center"> 
      <a href="index.php" class="nav-links-modern">UTAMA</a>
      <a href="javascript:void(0)" onclick="toggleLesBot()" class="nav-links-modern">CHATBOT</a>
      <a href="student_penalties.php" class="nav-links-modern">PENALTIES</a>
      <a href="student_history.php" class="nav-links-modern">HISTORY</a>
      <a href="https://portal.utem.edu.my/" target="_blank" class="nav-links-modern">UTeM <i class="bi bi-box-arrow-up-right small"></i></a>
      
      <div class="ms-3 d-flex gap-3 align-items-center">
          <a href="signup.php" class="btn-signup-pill">Signup</a>
          <a href="login.php" class="btn-login-pill">Login</a>
      </div>
    </div>
  </div>
</header>


  <section id="hero" style="height: 100vh; display: flex; align-items: center; text-align: center;">
    <div class="container">
        <h1 style="font-size: 4.5rem;">INNOVATE <span class="glow-text">WITHOUT LIMITS</span></h1>
        <h2 style="letter-spacing: 10px; color: #888;">LESTARI BOT DORMITORY SYSTEM</h2>
    </div>
  </section>

  <div id="lesbot-chat-container" class="glass-card shadow-lg" style="position: fixed; bottom: 20px; right: 20px; width: 350px; display: none; z-index: 9999; border-radius: 20px; border: 1px solid var(--pastel-blue);">
    <div class="card-header d-flex justify-content-between align-items-center p-3" style="background: rgba(167, 199, 231, 0.2); border-radius: 20px 20px 0 0;">
        <span style="font-family: 'Orbitron'; font-size: 0.9rem; color: var(--pastel-blue);">NEURAL LINK ACTIVE</span>
        <button onclick="toggleLesBot()" class="btn-close btn-close-white"></button>
    </div>
    <div id="chat-body" class="p-3" style="height: 400px; overflow-y: auto; font-family: 'Rajdhani';">
        <div class="bot-msg mb-2"><small style="color: var(--pastel-blue);">LesBot:</small><br>Greetings, friend. How can I assist your stay at Lestari today?</div>
    </div>
    <div class="p-3 bg-dark border-top border-secondary" style="border-radius: 0 0 20px 20px;">
        <div class="input-group">
            <input type="text" id="user-msg" class="form-control bg-transparent text-white border-info" placeholder="Send message...">
            <button class="btn btn-info" onclick="sendNeuralMessage()"><i class="bi bi-send"></i></button>
        </div>
    </div>
</div>

<script>
// 1. Function to show/hide the Chatbot window
function toggleLesBot() {
    const chatContainer = document.getElementById('lesbot-chat-container');
    // Check current display state
    if (chatContainer.style.display === "none" || chatContainer.style.display === "") {
        chatContainer.style.display = "block";
    } else {
        chatContainer.style.display = "none";
    }
}

// 2. Function to send messages to the AI
function sendNeuralMessage() {
    const msgInput = document.getElementById('user-msg');
    const chatBody = document.getElementById('chat-body');
    const msg = msgInput.value.trim();
    
    if (!msg) return;
    
    // Append User Message to UI
    chatBody.innerHTML += `<div class='text-end mb-2'><small class='text-info'>You:</small><br>${msg}</div>`;
    msgInput.value = '';

    // AJAX call to the backend
    fetch('chat_process.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
    })
    .then(response => response.text())
    .then(data => {
        // Append AI Response
        chatBody.innerHTML += `<div class='mb-2'><small style='color: var(--pastel-blue);'>LesBot:</small><br>${data}</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    })
    .catch(error => {
        console.error('Error:', error);
        chatBody.innerHTML += `<div class='mb-2 text-danger'><small>System Error: Neural link interrupted.</small></div>`;
    });
}

// 3. Add 'Enter' key support for convenience
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('user-msg');
    if (input) {
        input.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendNeuralMessage();
            }
        });
    }
});
</script>

</body>
</html>