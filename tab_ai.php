<style>
    /* Keep existing styles... */
    /* ... (same styles as before) ... */
    .ai-wrapper { display: flex; justify-content: center; height: calc(100vh - 140px); min-height: 500px; }
    .chat-card { width: 100%; max-width: 900px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15); }
    .chat-header { padding: 15px 25px; background: var(--sidebar-bg); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .chat-branding { display: flex; align-items: center; gap: 15px; }
    .bot-avatar { width: 45px; height: 45px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #a855f7); display: flex; align-items: center; justify-content: center; font-size: 22px; color: white; box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3); }
    .chat-info h3 { margin: 0 0 4px 0; color: white; font-size: 16px; font-weight: 700; }
    .status-badge { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #cbd5e1; background: rgba(255,255,255,0.1); padding: 3px 10px; border-radius: 20px; width: fit-content; }
    .status-dot { width: 8px; height: 8px; background-color: #22c55e; border-radius: 50%; box-shadow: 0 0 10px #22c55e; animation: pulse 2s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    .reset-btn { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.2); padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 6px; }
    .reset-btn:hover { background: rgba(239, 68, 68, 0.25); border-color: rgba(239, 68, 68, 0.5); color: white; }
    .chat-box { flex: 1; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; background-color: var(--bg-body); }
    .msg-row { display: flex; gap: 12px; align-items: flex-start; animation: fadeIn 0.3s ease; }
    .msg-row.user { flex-direction: row-reverse; }
    .msg-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .avatar-ai { background: var(--bg-input); border: 1px solid var(--border); color: var(--text-main); }
    .avatar-user { background: var(--accent); color: white; }
    .msg-bubble { max-width: 85%; padding: 12px 18px; border-radius: 16px; font-size: 14px; line-height: 1.5; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .msg-row.ai .msg-bubble { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-main); border-top-left-radius: 4px; }
    .msg-row.user .msg-bubble { background: linear-gradient(135deg, var(--accent), #2563eb); color: white; border-top-right-radius: 4px; }
    .sql-box { background: rgba(0,0,0,0.2); padding: 8px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #a855f7; margin-bottom: 10px; border-left: 3px solid #a855f7; }
    .table-container { overflow-x: auto; max-width: 100%; border-radius: 8px; border: 1px solid var(--border); margin-top: 5px; }
    .ai-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .ai-table th { background: var(--bg-input); color: var(--text-muted); padding: 8px; text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
    .ai-table td { padding: 8px; color: var(--text-main); border-bottom: 1px solid var(--border); white-space: nowrap; }
    .ai-table tr:last-child td { border-bottom: none; }
    .input-area { padding: 20px; background: var(--bg-card); border-top: 1px solid var(--border); display: flex; gap: 12px; }
    .chat-input { flex: 1; padding: 14px 20px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-input); color: var(--text-main); font-size: 15px; outline: none; transition: 0.2s; }
    .chat-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
    .send-btn { background: var(--accent); color: white; border: none; width: 55px; border-radius: 12px; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .send-btn:hover { transform: scale(1.05); opacity: 0.9; }
    .send-btn:disabled { background: var(--text-muted); cursor: not-allowed; }
    .typing-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #ccc; margin-right: 4px; animation: typing 1.4s infinite; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
</style>

<div id="ai" class="tab-section">
    <div class="ai-wrapper">
        <div class="chat-card">
            <div class="chat-header">
                <div class="chat-branding"><div class="bot-avatar">✨</div><div class="chat-info"><h3>Data Analyst AI</h3><div class="status-badge"><span class="status-dot"></span> System Online</div></div></div>
                <button onclick="resetChat()" class="reset-btn">🗑️ Reset</button>
            </div>
            
            <div class="chat-box" id="chatBox">
                <div class="msg-row ai">
                    <div class="msg-avatar avatar-ai">✨</div>
                    <div class="msg-bubble">
                        <strong>Hello! I am your Hybrid Data Assistant.</strong><br><br>
                        You can now ask me anything:<br>
                        • 📊 <em>"Who are the top 5 students in Computing?"</em> (I will run a query)<br>
                        • 🧠 <em>"How do I improve student retention?"</em> (I will give advice)
                    </div>
                </div>
            </div>
            
            <div class="input-area">
                <input type="text" id="userQuery" class="chat-input" placeholder="Ask a question..." onkeypress="handleEnter(event)">
                <button id="sendBtn" class="send-btn" onclick="sendMessage()">➤</button>
            </div>
        </div>
    </div>
</div>

<script>
function handleEnter(e) { if (e.key === 'Enter') sendMessage(); }
function resetChat() { document.getElementById('chatBox').innerHTML = `<div class="msg-row ai"><div class="msg-avatar avatar-ai">✨</div><div class="msg-bubble">Chat cleared.</div></div>`; }

async function sendMessage() {
    const input = document.getElementById('userQuery');
    const btn = document.getElementById('sendBtn');
    const text = input.value.trim();
    if (!text) return;
    
    addBubble(text, 'user');
    input.value = '';
    btn.disabled = true;
    const loadingId = addBubble('<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>', 'ai');

    try {
        const response = await fetch('ai_handler.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ question: text })
        });
        const res = await response.json();
        const bubbleContent = document.getElementById(loadingId).querySelector('.msg-bubble');
        
        if (res.error) {
            bubbleContent.innerHTML = `⚠️ <strong>Error:</strong> ${res.error}`;
            bubbleContent.style.borderLeft = "4px solid #ef4444";
        } 
        // SCENARIO A: DATA (SQL)
        else if (res.mode === 'sql') {
            if (res.data && res.data.length > 0) {
                let html = `<div class="sql-box">SQL: ${res.query}</div>`;
                html += `<div class="table-container"><table class="ai-table"><thead><tr>`;
                Object.keys(res.data[0]).forEach(k => { html += `<th>${k}</th>`; });
                html += `</tr></thead><tbody>`;
                res.data.forEach(row => {
                    html += `<tr>`;
                    Object.values(row).forEach(val => { html += `<td>${val}</td>`; });
                    html += `</tr>`;
                });
                html += `</tbody></table></div>`;
                html += `<div style="margin-top:8px; font-size:10px; opacity:0.7;">Found ${res.data.length} results.</div>`;
                bubbleContent.innerHTML = html;
            } else {
                bubbleContent.innerHTML = `<div class="sql-box">SQL: ${res.query}</div>No data found.`;
            }
        } 
        // SCENARIO B: CHAT (TEXT)
        else if (res.mode === 'chat') {
            // Convert simple markdown bolding if any
            let formattedText = res.reply.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            formattedText = formattedText.replace(/\n/g, '<br>');
            bubbleContent.innerHTML = formattedText;
        }

    } catch (err) {
        document.getElementById(loadingId).querySelector('.msg-bubble').innerHTML = "❌ Network Error: " + err.message;
    }

    btn.disabled = false;
    setTimeout(() => input.focus(), 100);
}

function addBubble(html, type) {
    const box = document.getElementById('chatBox');
    const row = document.createElement('div');
    row.className = `msg-row ${type}`;
    row.id = 'msg_' + Date.now();
    const avatar = document.createElement('div'); avatar.className = `msg-avatar avatar-${type}`; avatar.innerText = type === 'ai' ? '✨' : '👤';
    const bubble = document.createElement('div'); bubble.className = 'msg-bubble'; bubble.innerHTML = html;
    if(type === 'ai') { row.appendChild(avatar); row.appendChild(bubble); } else { row.appendChild(avatar); row.appendChild(bubble); }
    box.appendChild(row); box.scrollTop = box.scrollHeight;
    return row.id;
}
</script>