{{-- AI Chat Bubble --}}
<div id="ai-bubble-wrap">

    {{-- Tombol buka chat --}}
    <button id="ai-toggle" onclick="toggleAI()" title="HoloBot AI Assistant">
        <span id="ai-icon-open">🤖</span>
        <span id="ai-icon-close" style="display:none;">✕</span>
        <span id="ai-notif" class="ai-notif"></span>
    </button>

    {{-- Panel chat --}}
    <div id="ai-panel">
        <div id="ai-header">
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:22px;">🤖</span>
                <div>
                    <div style="font-weight:700;font-size:14px;">HoloBot</div>
                    <div style="font-size:11px;opacity:0.8;">AI Assistant • Online</div>
                </div>
            </div>
            <button onclick="toggleAI()" style="background:none;border:none;color:white;font-size:18px;cursor:pointer;">✕</button>
        </div>

        <div id="ai-messages">
            <div class="ai-msg ai-msg-bot">
                <div class="ai-bubble">
                    👋 Halo <strong>{{ session('user_nama') }}</strong>! Saya HoloBot, asisten AI kamu.<br><br>
                    Saya bisa membantu:<br>
                    • 📅 Cek jadwal & slot tersedia<br>
                    • ✅ Booking HoloLens<br>
                    • ❌ Batalkan booking<br><br>
                    Mau apa hari ini?
                </div>
            </div>
        </div>

        {{-- Quick replies --}}
        <div id="ai-quick">
            <button class="ai-quick-btn" onclick="sendQuick('Cek booking saya')">📋 Booking saya</button>
            <button class="ai-quick-btn" onclick="sendQuick('Slot tersedia hari ini?')">🕐 Slot tersedia</button>
            <button class="ai-quick-btn" onclick="sendQuick('Berapa sisa kuota minggu ini dan sisa jam hari ini?')">⏱ Sisa kuota</button>
            <button class="ai-quick-btn" onclick="sendQuick('Saya mau booking HoloLens, bantu saya')">➕ Booking baru</button>
        </div>

        <div id="ai-input-wrap">
            <input type="text" id="ai-input" placeholder="Ketik pesan..." onkeydown="if(event.key==='Enter') sendMsg()">
            <button id="ai-send" onclick="sendMsg()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
#ai-bubble-wrap { position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: 'Plus Jakarta Sans', sans-serif; }

#ai-toggle {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    border: none; cursor: pointer; font-size: 24px;
    box-shadow: 0 4px 20px rgba(37,99,235,0.4);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}
#ai-toggle:hover { transform: scale(1.1); box-shadow: 0 6px 24px rgba(37,99,235,0.5); }

.ai-notif {
    position: absolute; top: -2px; right: -2px;
    width: 14px; height: 14px; background: #ef4444;
    border-radius: 50%; border: 2px solid white;
    display: none;
}

#ai-panel {
    position: absolute; bottom: 70px; right: 0;
    width: 340px; height: 500px;
    background: white; border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    display: none; flex-direction: column;
    overflow: hidden; animation: slideUp 0.25s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

#ai-header {
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: white; padding: 14px 16px;
    display: flex; align-items: center; justify-content: space-between;
}

#ai-messages {
    flex: 1; overflow-y: auto; padding: 14px;
    display: flex; flex-direction: column; gap: 10px;
    background: #f8fafc;
}

.ai-msg { display: flex; }
.ai-msg-bot { justify-content: flex-start; }
.ai-msg-user { justify-content: flex-end; }

.ai-bubble {
    max-width: 85%; padding: 10px 14px;
    border-radius: 16px; font-size: 13.5px; line-height: 1.6;
}

.ai-msg-bot .ai-bubble {
    background: white; color: #1e293b;
    border-radius: 4px 16px 16px 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.ai-msg-user .ai-bubble {
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: white; border-radius: 16px 16px 4px 16px;
}

.ai-typing .ai-bubble {
    display: flex; gap: 4px; align-items: center; padding: 12px 16px;
}
.ai-dot {
    width: 7px; height: 7px; background: #94a3b8;
    border-radius: 50%; animation: bounce 1.2s infinite;
}
.ai-dot:nth-child(2) { animation-delay: 0.2s; }
.ai-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-6px); }
}

#ai-quick {
    padding: 8px 12px; display: flex; gap: 6px;
    flex-wrap: wrap; background: white;
    border-top: 1px solid #f1f5f9;
}

.ai-quick-btn {
    background: #f1f5f9; border: 1px solid #e2e8f0;
    border-radius: 20px; padding: 4px 10px;
    font-size: 12px; cursor: pointer; color: #475569;
    transition: all 0.15s;
}
.ai-quick-btn:hover { background: #e2e8f0; color: #1e293b; }

#ai-input-wrap {
    display: flex; gap: 8px; padding: 12px;
    background: white; border-top: 1px solid #f1f5f9;
}

#ai-input {
    flex: 1; border: 1.5px solid #e2e8f0; border-radius: 20px;
    padding: 8px 14px; font-size: 13px; outline: none;
    transition: border-color 0.2s;
}
#ai-input:focus { border-color: #2563eb; }

#ai-send {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    border: none; cursor: pointer; color: white;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: transform 0.15s;
}
#ai-send:hover { transform: scale(1.05); }
</style>

<script>
let aiOpen = false;

function toggleAI() {
    aiOpen = !aiOpen;
    const panel = document.getElementById('ai-panel');
    const iconOpen = document.getElementById('ai-icon-open');
    const iconClose = document.getElementById('ai-icon-close');

    panel.style.display = aiOpen ? 'flex' : 'none';
    iconOpen.style.display = aiOpen ? 'none' : 'inline';
    iconClose.style.display = aiOpen ? 'inline' : 'none';

    if (aiOpen) {
        document.getElementById('ai-input').focus();
        scrollChat();
    }
}

function sendQuick(text) {
    document.getElementById('ai-input').value = text;
    sendMsg();
}

function sendMsg() {
    const input = document.getElementById('ai-input');
    const msg = input.value.trim();
    if (!msg) return;

    addMsg(msg, 'user');
    input.value = '';

    const typingId = addTyping();

    fetch('/ai/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ message: msg })
    })
    .then(r => r.json())
    .then(data => {
        removeTyping(typingId);
        addMsg(data.reply, 'bot');
        if (data.reload) setTimeout(() => location.reload(), 2000);
    })
    .catch(() => {
        removeTyping(typingId);
        addMsg('Maaf, terjadi kesalahan. Coba lagi!', 'bot');
    });
}

function addMsg(text, who) {
    const msgs = document.getElementById('ai-messages');
    const div = document.createElement('div');
    div.className = `ai-msg ai-msg-${who}`;
    div.innerHTML = `<div class="ai-bubble">${text.replace(/\n/g, '<br>')}</div>`;
    msgs.appendChild(div);
    scrollChat();
    return div;
}

function addTyping() {
    const msgs = document.getElementById('ai-messages');
    const id = 'typing-' + Date.now();
    const div = document.createElement('div');
    div.id = id;
    div.className = 'ai-msg ai-msg-bot ai-typing';
    div.innerHTML = '<div class="ai-bubble"><div class="ai-dot"></div><div class="ai-dot"></div><div class="ai-dot"></div></div>';
    msgs.appendChild(div);
    scrollChat();
    return id;
}

function removeTyping(id) {
    document.getElementById(id)?.remove();
}

function scrollChat() {
    const msgs = document.getElementById('ai-messages');
    msgs.scrollTop = msgs.scrollHeight;
}
</script>
