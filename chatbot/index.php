<?php
// CHATBOT INTERFACE (High Voltage v5.0)
require_once __DIR__ . '/../app/config/bootstrap.php';

// AUTH CHECK
if (!isset($_SESSION['user'])) {
    header("Location: /login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include __DIR__ . '/../partials/head.php'; ?>
    <style>
        .chat-main {
            height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .chat-container {
            width: 100%;
            max-width: 1000px;
            height: 100%;
            background: rgba(8, 8, 9, 0.9);
            backdrop-filter: blur(25px);
            border: 1px solid var(--border);
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.8),
                0 0 50px rgba(255, 215, 0, 0.05);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .chat-header {
            padding: 25px 40px;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-title {
            font-family: var(--font-display);
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .bot-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), #e6c200);
            border-radius: 14px;
            /* Squircle style */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 0 25px rgba(255, 215, 0, 0.3);
            animation: glowPulse 2s infinite ease-in-out;
        }

        @keyframes glowPulse {
            0% {
                box-shadow: 0 0 15px rgba(255, 215, 0, 0.2);
            }

            50% {
                box-shadow: 0 0 30px rgba(255, 215, 0, 0.4);
            }

            100% {
                box-shadow: 0 0 15px rgba(255, 215, 0, 0.2);
            }
        }

        .chat-box {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 32px;
            scroll-behavior: smooth;
            background: radial-gradient(circle at center, rgba(255, 215, 0, 0.02) 0%, transparent 70%);
        }

        /* Customize Scrollbar */
        .chat-box::-webkit-scrollbar {
            width: 6px;
        }

        .chat-box::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-box::-webkit-scrollbar-thumb {
            background: rgba(255, 215, 0, 0.2);
            border-radius: 10px;
        }

        .chat-box::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 215, 0, 0.4);
        }

        .msg {
            max-width: 85%;
            display: flex;
            gap: 15px;
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(10px);
        }

        .msg.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .msg-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #111;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }

        .msg.bot .msg-avatar {
            color: var(--primary);
            background: rgba(255, 215, 0, 0.05);
            border-color: rgba(255, 215, 0, 0.2);
        }

        .msg-content {
            padding: 16px 20px;
            font-size: 1.05rem;
            line-height: 1.7;
            border-radius: 16px;
            position: relative;
            word-break: break-word;
        }

        .msg.bot .msg-content {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-top-left-radius: 4px;
            color: #d1d1d6;
        }

        .msg.user .msg-content {
            background: linear-gradient(135deg, var(--primary), #ffa000);
            color: #000;
            font-weight: 600;
            border-top-right-radius: 4px;
            box-shadow: 0 10px 25px rgba(255, 160, 0, 0.15);
        }

        /* ADVANCED CODE BLOCKS */
        .code-block {
            background: #0d0d0d;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin: 20px 0;
            overflow: hidden;
            font-family: 'Consolas', 'Monaco', monospace;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .code-header {
            background: #1a1a1a;
            padding: 10px 18px;
            font-size: 0.8rem;
            color: #aaa;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: var(--font-display);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .copy-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ccc;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .copy-btn:hover {
            background: var(--primary);
            color: #000;
            border-color: var(--primary);
        }

        .code-content {
            padding: 20px;
            color: #f8f8f2;
            font-size: 0.95rem;
            overflow-x: auto;
            white-space: pre;
            line-height: 1.5;
        }

        .input-area {
            padding: 30px 40px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 20px;
            background: rgba(5, 5, 5, 0.5);
            align-items: flex-end;
        }

        .input-wrapper {
            flex: 1;
            position: relative;
        }

        .chat-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 18px 24px;
            border-radius: 18px;
            color: #fff;
            font-family: var(--font-body);
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            resize: none;
            max-height: 200px;
            overflow-y: auto;
        }

        .chat-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 215, 0, 0.05);
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.05);
        }

        .btn-send {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: var(--primary);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            color: #000;
            font-size: 1.4rem;
            flex-shrink: 0;
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.2);
        }

        .btn-send:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 15px 30px rgba(255, 215, 0, 0.3);
            background: #fff;
        }

        @media (max-width: 768px) {
            .chat-main {
                height: calc(100vh - 100px);
                padding: 10px;
            }

            .chat-container {
                border-radius: 16px;
            }

            .chat-header {
                padding: 15px 20px;
            }

            .chat-box {
                padding: 20px;
                gap: 20px;
            }

            .input-area {
                padding: 15px 20px;
                gap: 10px;
            }

            .msg {
                max-width: 92%;
            }

            .btn-send {
                width: 50px;
                height: 50px;
                border-radius: 12px;
            }

            .chat-input {
                padding: 14px 18px;
                font-size: 1rem;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../partials/menu.php'; ?>

    <main class="container chat-main">
        <div class="chat-container">
            <!-- Header -->
            <div class="chat-header">
                <div class="chat-title">
                    <div class="bot-avatar">🤖</div>
                    <div>
                        <div style="font-weight:900; letter-spacing:1.5px; text-transform:uppercase;">NEURAL CORE</div>
                        <div
                            style="font-size:0.7rem; color:var(--primary); font-family:var(--font-display); letter-spacing:2px; font-weight:700;">
                            <span
                                style="display:inline-block; width:8px; height:8px; background:var(--primary); border-radius:50%; margin-right:5px; animation: glowPulse 1s infinite;"></span>
                            SYSTEM ACTIVE
                        </div>
                    </div>
                </div>
                <div>
                    <button class="btn btn-outline" style="padding: 10px 20px; font-size: 0.75rem; border-radius: 10px;"
                        onclick="resetChat()">LIMPIAR</button>
                </div>
            </div>

            <!-- Messages -->
            <div class="chat-box" id="chatBox">
                <!-- Intro Message -->
                <div class="msg bot" style="animation-delay: 0.2s;">
                    <div class="msg-avatar">🤖</div>
                    <div class="msg-content">
                        Bienvenido, **<?= htmlspecialchars($_SESSION['user']['name'] ?? 'Usuario') ?>**.
                        <br><br>
                        Soy la interfaz de inteligencia artificial de este portafolio. Estoy entrenado para ayudarte con
                        dudas sobre desarrollo web, arquitectura de sistemas y seguridad.
                        <br><br>
                        *¿En qué puedo asistirte hoy?*
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="input-area">
                <div class="input-wrapper">
                    <textarea id="userInput" class="chat-input" placeholder="Envía un mensaje o pega tu código..."
                        rows="1" oninput="autoResize(this)"></textarea>
                </div>
                <button onclick="send()" class="btn-send">➤</button>
            </div>
        </div>
    </main>

    <script>
        const userInput = document.getElementById('userInput');
        const chatBox = document.getElementById('chatBox');

        userInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                send();
            }
        });

        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }

        function resetChat() {
            chatBox.innerHTML = '';
            appendMessage("Memoria del sistema purgada. ¿En qué puedo ayudarte ahora?", 'bot');
        }

        function appendMessage(text, sender) {
            const div = document.createElement('div');
            div.className = `msg ${sender}`;

            let content = formatMessage(text);

            div.innerHTML = `
        <div class="msg-avatar">${sender === 'user' ? '👤' : '🤖'}</div>
        <div class="msg-content">${content}</div>
    `;

            chatBox.appendChild(div);
            const scrollOption = { top: chatBox.scrollHeight, behavior: 'smooth' };
            chatBox.scrollTo(scrollOption);
        }

        function formatMessage(text) {
            // Detect code blocks with language ```language code ```
            let formatted = text.replace(/```(\w+)?([\s\S]*?)```/g, (match, lang, code) => {
                const language = lang || 'code';
                return `<div class="code-block">
                    <div class="code-header">
                        <span>${language}</span>
                        <button class="copy-btn" onclick="copyCode(this)">
                            <i class="far fa-copy"></i> COPIAR
                        </button>
                    </div>
                    <div class="code-content">${escapeHtml(code.trim())}</div>
                </div>`;
            });

            // Simple markdown support
            formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');

            // Convert newlines to breaks if not already handled
            return formatted.replace(/\n/g, '<br>');
        }

        function copyCode(btn) {
            const code = btn.parentElement.nextElementSibling.innerText;
            navigator.clipboard.writeText(code).then(() => {
                btn.innerHTML = '<i class="fas fa-check"></i> COPIADO';
                btn.style.background = 'var(--primary)';
                btn.style.color = '#000';
                setTimeout(() => {
                    btn.innerHTML = '<i class="far fa-copy"></i> COPIAR';
                    btn.style.background = '';
                    btn.style.color = '';
                }, 2000);
            });
        }

        function escapeHtml(text) {
            const p = document.createElement('p');
            p.textContent = text;
            return p.innerHTML;
        }

        async function send() {
            const text = userInput.value.trim();
            if (!text) return;

            userInput.value = '';
            userInput.style.height = 'auto';
            appendMessage(text, 'user');

            const loadingId = 'loading-' + Date.now();
            const loadDiv = document.createElement('div');
            loadDiv.id = loadingId;
            loadDiv.className = 'msg bot';
            loadDiv.innerHTML = `<div class="msg-avatar">🤖</div><div class="msg-content" style="font-style:italic; opacity:0.7">Analizando secuencia...</div>`;
            chatBox.appendChild(loadDiv);
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });

            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text })
                });
                const data = await res.json();

                document.getElementById(loadingId).remove();

                if (data.reply) {
                    appendMessage(data.reply, 'bot');
                } else {
                    appendMessage("⚠️ Error: El motor de respuesta ha fallado.", 'bot');
                }
            } catch (e) {
                document.getElementById(loadingId).remove();
                appendMessage("📡 Error: Conexión perdida con la red neuronal.", 'bot');
            }
        }
    </script>


</body>

</html>