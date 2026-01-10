<?php
// CDOR v5.5 COMMAND CENTER - ULTRA PREMIUM
require_once __DIR__ . '/../app/config/bootstrap.php';
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: admin-login.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDOR // COMMAND CENTER</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg: #020202;
            --panel: #0a0a0c;
            --border: rgba(255, 255, 255, 0.05);
            --primary: #FFD700;
            --primary-glow: rgba(255, 215, 0, 0.4);
            --text: #ffffff;
            --text-dim: #94a3b8;
            --font-main: 'Rajdhani', sans-serif;
            --font-display: 'Orbitron', sans-serif;
            --radius-lg: 20px;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg);
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(255, 215, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(255, 136, 0, 0.03) 0%, transparent 50%);
            color: var(--text);
            height: 100vh;
            display: flex;
            overflow: hidden;
            margin: 0;
        }

        /* SIDEBAR */
        aside {
            width: 280px;
            background: rgba(8, 8, 10, 0.85);
            backdrop-filter: blur(25px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 40px 20px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 2000;
        }

        .brand-box { margin-bottom: 50px; padding: 0 10px; }
        .brand-box h1 { font-family: var(--font-display); font-size: 1.8rem; letter-spacing: 5px; color: #fff; margin: 0; }
        .brand-box span { color: var(--primary); text-shadow: 0 0 15px var(--primary-glow); }

        .menu-item {
            padding: 16px 20px;
            margin-bottom: 8px;
            border-radius: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--text-dim);
            font-weight: 600;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            border: 1px solid transparent;
        }

        .menu-item i { font-size: 1.2rem; width: 24px; text-align: center; }
        .menu-item:hover { color: #fff; background: rgba(255, 255, 255, 0.05); transform: translateX(5px); }
        .menu-item.active { background: rgba(255, 215, 0, 0.08); border-color: rgba(255, 215, 0, 0.2); color: var(--primary); }

        .logout-link {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ef4444;
            text-decoration: none;
            font-weight: 800;
            padding: 15px;
            border-radius: 12px;
            transition: 0.3s;
            border: 1px solid rgba(239, 68, 68, 0.1);
            justify-content: center;
            font-size: 0.8rem;
        }

        .logout-link:hover { background: rgba(239, 68, 68, 0.1); box-shadow: 0 0 20px rgba(239, 68, 68, 0.2); }

        /* MAIN */
        main { flex: 1; overflow-y: auto; background: radial-gradient(circle at 50% 50%, rgba(20, 20, 22, 0.3) 0%, transparent 100%); }
        header {
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(2, 2, 2, 0.7);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-title { display: flex; align-items: center; gap: 20px; }
        .header-title h2 { font-family: var(--font-display); font-size: 1.3rem; letter-spacing: 2px; margin: 0; }
        .user-pill {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .container { padding: 40px; max-width: 1400px; margin: 0 auto; }
        .section { display: none; animation: slideUp 0.5s ease; }
        .section.active { display: block; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* CARDS */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px; }
        .stat-card {
            background: rgba(15, 15, 18, 0.5);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            position: relative;
            transition: 0.4s;
        }
        .stat-card:hover { border-color: var(--primary); transform: translateY(-5px); }
        .stat-label { color: var(--text-dim); text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem; margin-bottom: 15px; }
        .stat-value { font-family: var(--font-display); font-size: 3rem; font-weight: 900; }

        /* TABLES */
        .data-card { background: rgba(10, 10, 12, 0.7); border: 1px solid var(--border); border-radius: 24px; padding: 30px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 20px; color: var(--primary); font-family: var(--font-display); font-size: 0.7rem; letter-spacing: 2px; border-bottom: 1px solid var(--border); }
        td { padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.02); font-size: 0.95rem; }
        tr:hover { background: rgba(255, 255, 255, 0.02); }

        .btn-action {
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-del { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-del:hover { background: #ef4444; color: #fff; }
        .btn-primary { background: var(--primary); color: #000; }
        .btn-primary:hover { transform: scale(1.05); box-shadow: 0 0 20px var(--primary-glow); }

        /* MODAL */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            backdrop-filter: blur(10px);
            z-index: 5000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-card {
            background: #0a0a0b;
            border: 1px solid var(--primary);
            width: 100%;
            max-width: 800px;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 0 100px rgba(0,0,0,0.5);
        }
        .form-control {
            width: 100%;
            background: #151517;
            border: 1px solid #333;
            color: #fff;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-family: inherit;
            outline: none;
        }
        .form-control:focus { border-color: var(--primary); }

        @media (max-width: 768px) {
            aside { transform: translateX(-100%); position: absolute; }
            aside.open { transform: translateX(0); }
            header { padding: 20px; }
            .container { padding: 20px; }
        }
    </style>
</head>
<body>

    <aside id="admSide">
        <div class="brand-box">
            <h1>CDOR<span>//</span></h1>
            <p style="font-size: 0.5rem; letter-spacing: 4px; color: #444; margin-top: 5px; text-transform: uppercase;">Command Nucleus</p>
        </div>
        <nav style="flex:1">
            <div class="menu-item active" onclick="nav('dash',this)">
                <i class="fa-solid fa-house-chimney-window"></i> <span>Dashboard</span>
            </div>
            <div class="menu-item" onclick="nav('inbox',this)">
                <i class="fa-solid fa-satellite-dish"></i> <span>Comlink</span>
                <span id="bMsg" style="margin-left:auto; background:var(--primary); color:#000; font-size:10px; padding:2px 8px; border-radius:10px; display:none;">0</span>
            </div>
            <div class="menu-item" onclick="nav('users',this)">
                <i class="fa-solid fa-users-viewfinder"></i> <span>Operativos</span>
            </div>
        </nav>
        <a href="admin-login.php?logout=1" class="logout-link">
            <i class="fa-solid fa-terminal"></i> DESCONECTAR
        </a>
    </aside>

    <main>
        <header>
            <div class="header-title">
                <i class="fa-solid fa-bars-staggered" style="color:var(--primary); cursor:pointer;" onclick="toggleAdmMenu()"></i>
                <h2 id="pTitle">VISTA GENERAL</h2>
            </div>
            <div class="user-pill">
                <i class="fa-solid fa-id-card-clip" style="color:var(--primary)"></i>
                <b><?= htmlspecialchars($user['name']) ?></b>
            </div>
        </header>

        <div class="container">
            <!-- DASHBOARD -->
            <div id="dash" class="section active">
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-label">Cuentas Activas</div>
                        <div class="stat-value" id="nUser">--</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Comunicaciones</div>
                        <div class="stat-value" id="nMsg">--</div>
                    </div>
                    <div class="stat-card" style="border-color: rgba(255,215,0,0.3)">
                        <div class="stat-label">Unread Logs</div>
                        <div class="stat-value" style="color:var(--primary)" id="nPen">--</div>
                    </div>
                </div>
                <div class="data-card" style="height: 450px;">
                    <h3 style="font-family: var(--font-display); font-size: 0.8rem; letter-spacing: 2px; color: var(--text-dim); margin-bottom: 30px;">INTERNAL DATA FLOW / 7D</h3>
                    <canvas id="gr"></canvas>
                </div>
            </div>

            <!-- INBOX -->
            <div id="inbox" class="section">
                <div style="display:flex; justify-content:space-between; margin-bottom:30px; align-items:center;">
                    <h3 style="font-family:var(--font-display); letter-spacing:2px; margin:0;">INBOX</h3>
                    <button class="btn-action btn-primary" onclick="showCompose()">
                        <i class="fa-solid fa-paper-plane"></i> REDACTAR
                    </button>
                </div>
                <div class="data-card">
                    <table id="tMsg">
                        <thead>
                            <tr>
                                <th>TIMESTAMP</th>
                                <th>REMITENTE</th>
                                <th>ASUNTO</th>
                                <th>ESTADO</th>
                                <th>ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- USERS -->
            <div id="users" class="section">
                <h3 style="font-family:var(--font-display); letter-spacing:2px; margin-bottom:30px;">OPERATIVOS AUTORIZADOS</h3>
                <div class="data-card">
                    <table id="tUser">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE</th>
                                <th>IDENTIDAD</th>
                                <th>ESTADO</th>
                                <th>CONTROL</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL -->
    <div class="modal-overlay" id="msgModal">
        <div class="modal-card">
            <div id="mHeader" style="display:flex; justify-content:space-between; margin-bottom:30px; border-bottom:1px solid #222; padding-bottom:20px;">
                <h3 style="font-family:var(--font-display); color:var(--primary); margin:0;">DATA DECRYPTED</h3>
                <i class="fa-solid fa-xmark" onclick="closeModal()" style="cursor:pointer; font-size:1.5rem;"></i>
            </div>
            <div id="mView" style="margin-bottom:30px; max-height:400px; overflow-y:auto; color:#ccc; line-height:1.7;"></div>
            <div id="mReplyArea" style="display:none">
                <textarea id="replyText" class="form-control" style="min-height:150px;" placeholder="Encrypt response..."></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:15px;">
                <button class="btn-action btn-del" id="btnDelete" onclick="deleteMsg()" style="margin-right:auto">ELIMINAR</button>
                <button class="btn-action" style="background:#222; color:#fff;" onclick="closeModal()">CERRAR</button>
                <button class="btn-action btn-primary" id="btnReply" onclick="sendReply()">RESPONDER</button>
                <button class="btn-action btn-primary" id="btnSend" style="display:none" onclick="doSend()">ENVIAR AHORA</button>
                <button class="btn-action btn-primary" id="btnComposeSend" style="display:none" onclick="doComposeSend()">ENVIAR CORREO</button>
            </div>
        </div>
    </div>

    <script>
        let ch = null;
        let currentMsg = null;

        function nav(id, el) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
            if(el) el.classList.add('active');
            document.getElementById('pTitle').innerText = id.toUpperCase();
            load();
        }

        function toggleAdmMenu() { document.getElementById('admSide').classList.toggle('open'); }

        async function load() {
            // Stats
            const ds = await fetch('api-admin.php?action=stats').then(r => r.json());
            document.getElementById('nUser').innerText = ds.users;
            document.getElementById('nMsg').innerText = ds.messages;
            document.getElementById('nPen').innerText = ds.unread;
            if(ds.unread > 0) {
                const b = document.getElementById('bMsg');
                b.style.display = 'inline-block';
                b.innerText = ds.unread;
            }

            // Chart
            const dc = await fetch('api-admin.php?action=chart_data').then(r => r.json());
            if(ch) ch.destroy();
            const ctx = document.getElementById('gr').getContext('2d');
            ch = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dc.labels,
                    datasets: [
                        { label: 'Usuarios', data: dc.users, borderColor: '#FFD700', backgroundColor: 'rgba(255,215,0,0.1)', fill: true, tension: 0.4 },
                        { label: 'Mensajes', data: dc.messages, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { x: { grid: { display: false }, ticks: { color: '#444' } }, y: { grid: { color: '#111' }, ticks: { color: '#444' } } }
                }
            });

            // Lists
            if(document.getElementById('inbox').classList.contains('active')) {
                const msgs = await fetch('api-admin.php?action=get_mails').then(r => r.json());
                let html = '';
                msgs.forEach(m => {
                    html += `<tr onclick="openMsg(${m.id})" style="cursor:pointer; ${m.is_read==0 ? 'font-weight:900; background:rgba(255,215,0,0.02)' : 'opacity:0.6'}">
                        <td>${new Date(m.created_at).toLocaleString()}</td>
                        <td>${m.name}</td>
                        <td>${m.subject}</td>
                        <td>${m.is_read==1 ? 'READ' : '<span style="color:var(--primary)">NEW</span>'}</td>
                        <td onclick="event.stopPropagation()"><button class="btn-action btn-del" onclick="deleteMsg(${m.id})"><i class="fa-solid fa-trash"></i></button></td>
                    </tr>`;
                });
                document.getElementById('tMsg').querySelector('tbody').innerHTML = html;
            }

            if(document.getElementById('users').classList.contains('active')) {
                const usersArr = await fetch('api-admin.php?api=users').then(r => r.json());
                let html = '';
                usersArr.forEach(u => {
                    const isBanned = u.is_banned == 1;
                    html += `<tr>
                        <td>#${u.id}</td>
                        <td>${u.name}</td>
                        <td>${u.email}</td>
                        <td>${isBanned ? '<span style="color:#ef4444">BANNED</span>' : '<span style="color:#10b981">ACTIVE</span>'}</td>
                        <td>
                            <button class="btn-action" style="background:#222; color:#fff" onclick="banUser(${u.id})">${isBanned ? 'UNBAN' : 'BAN'}</button>
                            <button class="btn-action btn-del" onclick="deleteUser(${u.id})"><i class="fa-solid fa-user-xmark"></i></button>
                        </td>
                    </tr>`;
                });
                document.getElementById('tUser').querySelector('tbody').innerHTML = html;
            }
        }

        async function openMsg(id) {
            const res = await fetch(`api-admin.php?action=get_message&id=${id}`).then(r => r.json());
            currentMsg = res;
            document.getElementById('mHeader').querySelector('h3').innerText = 'COMMUNICATION DECRYPTED';
            document.getElementById('mView').innerHTML = `
                <div style="background:#000; padding:20px; border-radius:12px; border:1px solid #222; margin-bottom:20px; font-size:0.8rem">
                    <div><b>FROM:</b> ${res.name} (${res.email})</div>
                    <div><b>SUBJECT:</b> ${res.subject}</div>
                    <div><b>TIME:</b> ${res.nice_date}</div>
                </div>
                <div style="padding:0 10px">${res.message.replace(/\n/g, '<br>')}</div>
            `;
            document.getElementById('mReplyArea').style.display = 'none';
            document.getElementById('btnReply').style.display = 'block';
            document.getElementById('btnSend').style.display = 'none';
            document.getElementById('btnComposeSend').style.display = 'none';
            document.getElementById('btnDelete').style.display = 'block';
            document.getElementById('msgModal').style.display = 'flex';
        }

        function closeModal() { document.getElementById('msgModal').style.display = 'none'; }

        function sendReply() {
            document.getElementById('mReplyArea').style.display = 'block';
            document.getElementById('btnReply').style.display = 'none';
            document.getElementById('btnSend').style.display = 'block';
            document.getElementById('replyText').focus();
        }

        async function doSend() {
            const txt = document.getElementById('replyText').value;
            if(!txt) return alert('Message required.');
            const res = await fetch('api-admin.php?action=send_reply', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: currentMsg.id, to: currentMsg.email, name: currentMsg.name, subject: currentMsg.subject, message: txt})
            }).then(r => r.json());
            if(res.success) { alert('Transmission Successful.'); closeModal(); load(); }
            else alert('Error: ' + res.error);
        }

        function showCompose() {
            document.getElementById('mHeader').querySelector('h3').innerText = 'COMPOSE NEW BROADCAST';
            document.getElementById('mView').innerHTML = `
                <input id="cTo" class="form-control" placeholder="Recipient Email">
                <input id="cSub" class="form-control" placeholder="Subject">
                <textarea id="cBody" class="form-control" style="min-height:200px" placeholder="Secure content..."></textarea>
            `;
            document.getElementById('mReplyArea').style.display = 'none';
            document.getElementById('btnReply').style.display = 'none';
            document.getElementById('btnSend').style.display = 'none';
            document.getElementById('btnDelete').style.display = 'none';
            document.getElementById('btnComposeSend').style.display = 'block';
            document.getElementById('msgModal').style.display = 'flex';
        }

        async function doComposeSend() {
            const to = document.getElementById('cTo').value;
            const sub = document.getElementById('cSub').value;
            const body = document.getElementById('cBody').value;
            if(!to || !sub || !body) return alert('All fields required.');
            const res = await fetch('api-admin.php?action=send_new_email', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({to, subject: sub, message: body})
            }).then(r => r.json());
            if(res.success) { alert('Broadcast sent.'); closeModal(); }
            else alert('Error: ' + res.error);
        }

        async function deleteMsg(id) {
            const target = id || currentMsg.id;
            if(!confirm('ERASE LOG PERMANENTLY?')) return;
            await fetch('api-admin.php?action=delete_message', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: target})
            });
            closeModal(); load();
        }

        async function banUser(id) {
            if(!confirm('TOGGLE ACCESS STATE?')) return;
            await fetch('api-admin.php?action=ban_user', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id})
            });
            load();
        }

        async function deleteUser(id) {
            if(!confirm('TERMINATE OPERATIVE ACCOUNT? irreversible.')) return;
            const res = await fetch('api-admin.php?action=delete_user', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id})
            }).then(r => r.json());
            if(res.success) load(); else alert(res.error);
        }

        load();
    </script>
</body>
</html>