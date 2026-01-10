// CDOR Minecraft Client Node - V2
// Este script permite ejecutar un servidor de Minecraft en tu PC y gestionarlo desde la web.

const http = require('http');
const https = require('https');
const {
    spawn
} = require('child_process');
const fs = require('fs');
const path = require('path');

// Comprobación de constantes (inyectadas al descargar desde la web)
if (typeof DEVICE_TOKEN === 'undefined' || typeof API_URL === 'undefined') {
    console.log("❌ ERROR: Este archivo debe ser descargado desde el panel de gestión para funcionar.");
    process.exit(1);
}

const client = API_URL.startsWith('https') ? https : http;

let serverProcess = null;
let currentStatus = 'offline';
let lastLogs = [];
let retryCount = 0;

function log(msg, type = 'INFO') {
    const time = new Date().toLocaleTimeString();
    console.log(`[${type}] ${time} - ${msg}`);
}

async function apiCall(action, data = {}) {
    return new Promise((resolve) => {
        const payload = JSON.stringify({
            token: DEVICE_TOKEN,
            ...data
        });

        const url = `${API_URL}?action=${action}`;
        const options = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(payload)
            },
            timeout: 5000
        };

        const req = client.request(url, options, (res) => {
            let body = '';
            res.on('data', (chunk) => body += chunk);
            res.on('end', () => {
                try {
                    if (res.statusCode !== 200) {
                        resolve({
                            error: `HTTP ${res.statusCode}`
                        });
                        return;
                    }
                    resolve(JSON.parse(body));
                } catch (e) {
                    resolve({
                        error: 'Respuesta inválida del servidor'
                    });
                }
            });
        });

        req.on('error', (e) => {
            resolve({
                error: e.message
            });
        });
        req.on('timeout', () => {
            req.destroy();
            resolve({
                error: 'Timeout de conexión'
            });
        });
        req.write(payload);
        req.end();
    });
}

function startMinecraft() {
    if (serverProcess) {
        log("El servidor ya está en ejecución.", 'AVISO');
        return;
    }

    log("🚀 Iniciando Minecraft Server (server.jar)...");

    if (!fs.existsSync('server.jar')) {
        log("❌ ERROR: No se encuentra 'server.jar' en esta carpeta.", 'ERROR');
        apiCall('client_update', {
            status: 'offline',
            error: 'server.jar missing'
        });
        return;
    }

    // Aceptar EULA automáticamente si no existe
    if (!fs.existsSync('eula.txt')) {
        fs.writeFileSync('eula.txt', 'eula=true');
    }

    serverProcess = spawn('java', ['-Xmx2G', '-Xms1G', '-jar', 'server.jar', 'nogui'], {
        shell: true
    });

    currentStatus = 'online';

    serverProcess.stdout.on('data', (data) => {
        const str = data.toString();
        process.stdout.write(str);
        lastLogs.push(str);
        if (lastLogs.length > 100) lastLogs.shift();
    });

    serverProcess.stderr.on('data', (data) => {
        console.error(data.toString());
    });

    serverProcess.on('close', (code) => {
        log(`Servidor detenido (código ${code})`, 'SISTEMA');
        serverProcess = null;
        currentStatus = 'offline';
        apiCall('client_update', {
            status: 'offline'
        });
    });
}

function stopMinecraft() {
    if (!serverProcess) return;
    log("🛑 Deteniendo servidor...", 'SISTEMA');
    serverProcess.stdin.write('stop\n');
}

async function heartbeat() {
    const res = await apiCall('client_poll');

    if (res.error) {
        if (retryCount % 10 === 0) log(`Error de conexión: ${res.error}`, 'REINTENTANDO');
        retryCount++;
        return;
    }

    retryCount = 0;

    if (res.commands && res.commands.length > 0) {
        for (const cmd of res.commands) {
            log(`Orden recibida: ${cmd.action}`, 'WEB');
            if (cmd.action === 'START') startMinecraft();
            if (cmd.action === 'STOP') stopMinecraft();
        }
    }

    // Actualizar estado en la web
    await apiCall('client_update', {
        status: currentStatus,
        public_ip: '127.0.0.1'
    });

    // Enviar logs si hay nuevos
    if (lastLogs.length > 0) {
        await apiCall('client_log', {
            logs: lastLogs.join('')
        });
        lastLogs = [];
    }
}

console.log("==================================================");
console.log("   CONECTOR LOCAL CDOR HOSTING - ACTIVO         ");
console.log("==================================================");
console.log(`Conectando a: ${API_URL}`);
console.log(`ID Nodo:      ${DEVICE_TOKEN.substring(0, 10)}...`);
console.log("--------------------------------------------------");

// Bucle principal
setInterval(heartbeat, 3000);
heartbeat();