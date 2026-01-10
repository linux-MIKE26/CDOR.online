const DEVICE_TOKEN = 'mike_secret_token_2025';
const API_URL = 'https://www.cdor.online/minecraft/api.php';

// CDOR Minecraft Client Node - V3 (Final)
const http = require('http');
const https = require('https');
const {
    spawn
} = require('child_process');
const fs = require('fs');

const client = API_URL.startsWith('https') ? https : http;
let serverProcess = null;
let currentStatus = 'offline';
let lastLogs = [];

function log(msg) {
    console.log(`[${new Date().toLocaleTimeString()}] ${msg}`);
}

async function apiCall(action, data = {}) {
    return new Promise((resolve) => {
        const payload = JSON.stringify({
            token: DEVICE_TOKEN,
            ...data
        });
        const url = `${API_URL}?action=${action}&token=${DEVICE_TOKEN}`;
        const options = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(payload)
            }
        };
        const req = client.request(url, options, (res) => {
            let body = '';
            res.on('data', (d) => body += d);
            res.on('end', () => {
                try {
                    resolve(JSON.parse(body));
                } catch (e) {
                    resolve({});
                }
            });
        });
        req.on('error', (e) => resolve({
            error: e.message
        }));
        req.write(payload);
        req.end();
    });
}

function startMinecraft() {
    if (serverProcess) return log("Ya está corriendo.");
    log("🚀 Arrancando Minecraft (server.jar)...");
    if (!fs.existsSync('server.jar')) return log("❌ Error: No existe 'server.jar'.");
    if (!fs.existsSync('eula.txt')) fs.writeFileSync('eula.txt', 'eula=true');

    serverProcess = spawn('java', ['-Xmx2G', '-Xms1G', '-jar', 'server.jar', 'nogui'], {
        shell: true
    });
    currentStatus = 'online';

    serverProcess.stdout.on('data', (d) => {
        process.stdout.write(d);
        lastLogs.push(d.toString());
        if (lastLogs.length > 50) lastLogs.shift();
    });
    serverProcess.on('close', () => {
        log("🛑 Servidor detenido.");
        serverProcess = null;
        currentStatus = 'offline';
    });
}

async function loop() {
    const res = await apiCall('client_poll');
    if (res.commands) {
        for (const cmd of res.commands) {
            log(`Orden: ${cmd.action}`);
            if (cmd.action === 'START') startMinecraft();
            if (cmd.action === 'STOP' && serverProcess) serverProcess.stdin.write('stop\n');
        }
    }
    await apiCall('client_update', {
        status: currentStatus
    });
    if (lastLogs.length > 0) {
        await apiCall('client_log', {
            logs: lastLogs.join('')
        });
        lastLogs = [];
    }
}

log("✅ Nodo de Mike Conectado. Esperando órdenes de www.cdor.online...");
setInterval(loop, 3000);
loop();