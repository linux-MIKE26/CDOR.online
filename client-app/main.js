const electron = require('electron');
console.log('DEBUG ELECTRON IMPORT:', typeof electron, Object.keys(electron));
const {
    app,
    BrowserWindow,
    ipcMain
} = electron;
const path = require('path');
const axios = require('axios');

let mainWindow;
let deviceToken = null;
let pollInterval = null;
const API_URL = 'http://localhost:8090/minecraft/api.php'; // Hardcoded for dev

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 450,
        height: 600,
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            nodeIntegration: false,
            contextIsolation: true
        },
        autoHideMenuBar: true,
        backgroundColor: '#050505',
        icon: path.join(__dirname, 'icon.png')
    });

    mainWindow.loadFile('index.html');
}

app.whenReady().then(async () => {
    createWindow();

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) createWindow();
    });

    // HEADLESS TESTING AUTO-LOGIN
    if (process.env.AUTO_LOGIN_TOKEN) {
        console.log("Auto-logging in with token:", process.env.AUTO_LOGIN_TOKEN);
        setTimeout(async () => {
            deviceToken = process.env.AUTO_LOGIN_TOKEN;
            await apiCall('client_poll'); // Verify
            startPolling();
        }, 1000);
    }
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') app.quit();
});

// --- IPC HANDLERS ---

ipcMain.handle('login', async (event, token) => {
    try {
        deviceToken = token;
        // Verify token by trying to poll once
        const res = await apiCall('client_poll');
        if (res.error) return {
            success: false,
            error: res.error
        };

        // Start polling
        startPolling();
        return {
            success: true
        };
    } catch (e) {
        return {
            success: false,
            error: e.message
        };
    }
});

// --- API LOGIC ---

async function apiCall(action, data = {}) {
    try {
        const payload = {
            token: deviceToken,
            ...data
        };
        const res = await axios.post(`${API_URL}?action=${action}`, payload);
        return res.data;
    } catch (e) {
        console.error("API Error:", e.message);
        throw e;
    }
}

function startPolling() {
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(async () => {
        try {
            const res = await apiCall('client_poll');
            if (res.commands && res.commands.length > 0) {
                for (const cmd of res.commands) {
                    mainWindow.webContents.send('log', `Comando recibido: ${cmd.action}`);
                    processCommand(cmd);
                }
            }
        } catch (e) {
            mainWindow.webContents.send('log', 'Error de conexión con API...');
        }
    }, 3000);
}

function processCommand(cmd) {
    if (cmd.action === 'START') {
        mainWindow.webContents.send('log', 'Iniciando servidor...');
        mainWindow.webContents.send('status-update', {
            status: 'starting'
        });

        // MVP SIMULATION: Real logic comes next
        setTimeout(async () => {
            mainWindow.webContents.send('log', 'Servidor JAVA iniciado (Simulado).');
            mainWindow.webContents.send('status-update', {
                status: 'online',
                ip: '127.0.0.1:25565'
            });
            await apiCall('client_update', {
                status: 'online',
                public_ip: '127.0.0.1:25565'
            });
        }, 2000);

    } else if (cmd.action === 'STOP') {
        mainWindow.webContents.send('log', 'Deteniendo servidor...');

        setTimeout(async () => {
            mainWindow.webContents.send('log', 'Servidor detenido.');
            mainWindow.webContents.send('status-update', {
                status: 'offline'
            });
            await apiCall('client_update', {
                status: 'offline'
            });
        }, 1500);
    }
}