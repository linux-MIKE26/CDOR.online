// --- ADMIN JS LOGIC ---

document.addEventListener("DOMContentLoaded", () => {
    loadStats();
    initChart();
});

// 1. CAMBIO DE PESTAÑAS
function switchTab(viewId, btn) {
    // Ocultar todas las vistas
    document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
    
    // Mostrar la seleccionada
    document.getElementById('view-' + viewId).classList.add('active');
    btn.classList.add('active');
    
    // Cambiar título
    const titles = { 'dashboard': 'Resumen General', 'inbox': 'Buzón de Entrada', 'users': 'Gestión de Usuarios' };
    document.getElementById('pageTitle').innerText = titles[viewId];

    // Cargar datos según la vista
    if (viewId === 'inbox') loadInbox();
    if (viewId === 'users') loadUsers();
    if (viewId === 'dashboard') { loadStats(); updateChart(); }
}

// 2. CARGAR ESTADÍSTICAS
async function loadStats() {
    try {
        const res = await fetch('/admin/api-admin.php?action=stats');
        const data = await res.json();
        
        document.getElementById('statUsers').innerText = data.users;
        document.getElementById('statMsgs').innerText = data.messages;
        document.getElementById('statUnread').innerText = data.unread;
        
        // System Info
        document.getElementById('sysPhp').innerText = data.php_version;
        document.getElementById('sysDisk').innerText = data.disk_usage;
        document.getElementById('sysDb').innerText = data.db_status;

        // Badge en el menú
        const badge = document.getElementById('badgeUnread');
        if(data.unread > 0) {
            badge.style.display = 'inline-block';
            badge.innerText = data.unread;
        } else {
            badge.style.display = 'none';
        }

    } catch (e) { console.error("Error loading stats", e); }
}

// 3. GRÁFICA CHART.JS
let myChart;
async function initChart() {
    const ctx = document.getElementById('activityChart').getContext('2d');
    
    // Gradiente bonito
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // Azul fuerte
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)'); // Transparente

    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Nuevos Usuarios',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4, // Curvas suaves
                    pointBackgroundColor: '#fff'
                },
                {
                    label: 'Mensajes',
                    data: [],
                    borderColor: '#10b981', // Verde
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#94a3b8' } } },
            scales: {
                y: { grid: { color: '#27272a' }, ticks: { color: '#94a3b8' }, beginAtZero: true },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            }
        }
    });
    updateChart();
}

async function updateChart() {
    try {
        const res = await fetch('/admin/api-admin.php?action=chart_data');
        const data = await res.json();
        
        myChart.data.labels = data.labels;
        myChart.data.datasets[0].data = data.users;
        myChart.data.datasets[1].data = data.messages;
        myChart.update();
    } catch(e) { console.error("Chart error", e); }
}

// 4. BUZÓN (INBOX)
async function loadInbox() {
    const list = document.getElementById('mailList');
    list.innerHTML = '<div style="padding:20px; text-align:center; color:#555;">Actualizando...</div>';
    
    try {
        const res = await fetch('/admin/api-admin.php?action=get_mails');
        const mails = await res.json();
        
        list.innerHTML = '';
        if(mails.length === 0) {
            list.innerHTML = '<div style="padding:20px; text-align:center; color:#555;">No hay mensajes</div>';
            return;
        }

        mails.forEach(mail => {
            const div = document.createElement('div');
            div.className = `mail-item ${mail.is_read == 0 ? 'unread' : ''}`;
            div.onclick = () => openMail(mail.id, div);
            div.innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="color:#94a3b8; font-size:0.8rem;">${mail.name}</span>
                    <span style="color:#555; font-size:0.75rem;">${new Date(mail.created_at).toLocaleDateString()}</span>
                </div>
                <h4 style="margin:0; font-size:0.95rem; color:#e4e4e7; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${mail.subject}</h4>
            `;
            list.appendChild(div);
        });
    } catch(e) { console.error(e); }
}

let currentMailId = null;
let currentMailEmail = null;
let currentMailName = null;
let currentSubject = null;

async function openMail(id, element) {
    // UI selection
    document.querySelectorAll('.mail-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    element.classList.remove('unread'); // Visualmente leído
    
    // Mobile view handling
    if(window.innerWidth <= 1000) {
        document.getElementById('mailViewer').classList.add('active');
        document.getElementById('closeMailBtn').style.display = 'block';
    }

    // Fetch details
    const res = await fetch(`/admin/api-admin.php?action=get_message&id=${id}`);
    const mail = await res.json();

    currentMailId = mail.id;
    currentMailEmail = mail.email;
    currentMailName = mail.name;
    currentSubject = mail.subject;

    document.getElementById('emptyMail').style.display = 'none';
    document.getElementById('mailContent').style.display = 'block';
    
    document.getElementById('mSubject').innerText = mail.subject;
    document.getElementById('mName').innerText = mail.name;
    document.getElementById('mEmail').innerText = mail.email;
    document.getElementById('mDate').innerText = mail.nice_date;
    document.getElementById('mBody').innerText = mail.message;
    document.getElementById('replyToName').innerText = mail.name;
    document.getElementById('replyText').value = ''; // Clear reply
    
    loadStats(); // Refresh unread count
}

function closeMailMobile() {
    document.getElementById('mailViewer').classList.remove('active');
}

async function sendReply() {
    const text = document.getElementById('replyText').value.trim();
    if(!text) return alert("Escribe un mensaje");

    const btn = document.getElementById('btnSendReply');
    btn.disabled = true;
    btn.innerText = "Enviando...";

    try {
        const res = await fetch('/admin/api-admin.php?action=send_reply', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                to: currentMailEmail,
                name: currentMailName,
                subject: currentSubject,
                message: text
            })
        });
        const resp = await res.json();
        
        if(resp.success) {
            alert("Respuesta enviada correctamente");
            document.getElementById('replyText').value = '';
        } else {
            alert("Error: " + resp.error);
        }
    } catch(e) { alert("Error de conexión"); }
    
    btn.disabled = false;
    btn.innerText = "Enviar Respuesta";
}

// 5. USUARIOS
async function loadUsers() {
    const tbody = document.getElementById('usersTable');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Cargando...</td></tr>';
    
    try {
        const res = await fetch('/admin/api-admin.php?action=get_users');
        const users = await res.json();
        
        tbody.innerHTML = '';
        users.forEach(u => {
            const status = u.email_verified == 1 
                ? '<span class="status-badge badge-green">Verificado</span>' 
                : '<span class="status-badge badge-red">Pendiente</span>';
                
            const row = `
                <tr>
                    <td>#${u.id}</td>
                    <td><div style="font-weight:600;">${u.name}</div><div style="font-size:0.8rem; color:#aaa;">${u.role}</div></td>
                    <td>${u.email}</td>
                    <td>${status}</td>
                    <td>${new Date(u.created_at).toLocaleDateString()}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    } catch(e) { console.error(e); }
}