<!-- chatbot_component.php -->
<div id="lesbot-chat-container" class="glass-card shadow-lg" style="position: fixed; bottom: 100px; right: 30px; width: 350px; display: none; z-index: 9999; border: 1px solid var(--lesbot-cyan); background: rgba(8, 10, 15, 0.95); backdrop-filter: blur(20px);">
    <div class="card-header d-flex justify-content-between align-items-center p-3 border-bottom border-secondary">
        <span style="font-family: 'Orbitron'; font-size: 0.7rem; color: var(--lesbot-cyan); letter-spacing: 2px;">LESBOT 24/7 HELPFLOW</span>
        <button onclick="toggleLesBot()" class="btn-close btn-close-white" style="font-size: 0.6rem;"></button>
    </div>
    <div id="chat-body" class="p-3" style="height: 350px; overflow-y: auto; font-family: 'Rajdhani'; color: white;">
        <div class="mb-3"><small class="text-info">LesBot:</small><br>Identity verified. How can I assist you in the Command Center?</div>
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

<script>
function toggleLesBot() {
    const chatContainer = document.getElementById('lesbot-chat-container');
    chatContainer.style.display = (chatContainer.style.display === "none" || chatContainer.style.display === "") ? "block" : "none";
}

function sendNeuralMessage() {
    const msgInput = document.getElementById('user-msg');
    const chatBody = document.getElementById('chat-body');
    const msg = msgInput.value.trim();
    if (!msg) return;
    
    chatBody.innerHTML += `<div class='text-end mb-3'><small class='text-info' style='font-family:Orbitron; font-size:0.6rem;'>YOU:</small><br><div class='d-inline-block p-2 rounded' style='background:rgba(0,212,255,0.1); border:1px solid #00d4ff;'>${msg}</div></div>`;
    msgInput.value = '';

    const loaderId = "loader-" + Date.now();
    chatBody.innerHTML += `<div id='${loaderId}' class='mb-3'><small style='color: #00d4ff; font-family:Orbitron; font-size:0.6rem;'>Thinking...</small></div>`;
    chatBody.scrollTop = chatBody.scrollHeight;

    fetch('chat_process.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById(loaderId).remove();
        chatBody.innerHTML += `<div class='mb-3'><small style='color: #00d4ff; font-family:Orbitron; font-size:0.6rem;'>LesBot:</small><br><div class='p-2 rounded' style='background:rgba(255,255,255,0.05); border-left:3px solid #00d4ff;'>${data}</div></div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    });
}
</script>