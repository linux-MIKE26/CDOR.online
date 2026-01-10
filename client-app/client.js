const axios = require('axios');
const {
    spawn
} = require('child_process');
const path = require('path');
const fs = require('fs');
const readline = require('readline');

// CONFIG
const API_URL = 'http://localhost:8090/minecraft/api.php';
const CONFIG_FILE = path.join(__dirname, 'config.json');

// STATE
// serverId -> { process, info, ... }
const activeServers = {};
let nodeSecret = null;

function log(msg) {
    console.log(`[NODE] ${new Date().toLocaleTimeString()} - ${msg}`);
}

// --- UTILS ---

async function prompt(question) {
    const rl = readline.createInterface({
        input: process.stdin,
        output: process.stdout
    });
    return new Promise(resolve => {
        rl.question(question, (answer) => {
            rl.close();
            resolve(answer.trim());
        });
    });
}

async function apiCall(action, data = {}, extraHeaders = {}) {
    try {
        const payload = {
            ...data
        };
        const res = await axios.post(`${API_URL}?action=${action}`, payload, {
            headers: extraHeaders
        });
        return res.data;
    } catch (e) {
        log(`API Error (${action}): ${e.message}`);
        return {
            error: e.message
        };
    }
}

// --- CORE ---

async function init() {
    console.log("⚡ CDOR Distributed NODE Client v2.0 (Multi-Server)");

    // 1. Load Config
    if (fs.existsSync(CONFIG_FILE)) {
        try {
            const config = JSON.parse(fs.readFileSync(CONFIG_FILE, 'utf8'));
            if (config.node_secret) nodeSecret = config.node_secret;
        } catch (e) {
            log("Error reading config.json");
        }
    }

    // 2. Arg Override
    const args = process.argv.slice(2);
    args.forEach(arg => {
        if (arg.startsWith('--secret=')) nodeSecret = arg.split('=')[1];
    });

    // 3. Prompt if missing
    if (!nodeSecret) {
        // default dev secret
        console.log("No secret found. Defaulting to 'secret_node_token' for dev.");
        nodeSecret = 'secret_node_token';
        // nodeSecret = await prompt("Enter Node Secret: ");
        fs.writeFileSync(CONFIG_FILE, JSON.stringify({
            node_secret: nodeSecret
        }));
    }

    startPolling();
}

async function sync() {
    // 1. Get Servers
    const res = await apiCall('node_sync', {
        secret: nodeSecret
    });
    if (!res.success) {
        log(`Sync Failed: ${res.error}`);
        return;
    }

    const servers = res.servers || [];
    const completedCmds = [];

    // 2. Process Sync
    for (const s of servers) {
        // Ensure Active State
        let active = activeServers[s.id];
        if (!active) {
            activeServers[s.id] = {
                process: null,
                info: s
            };
            active = activeServers[s.id];
        }
        active.info = s; // Update info

        // Check Commands
        if (s.pending_commands && s.pending_commands.length > 0) {
            for (const cmd of s.pending_commands) {
                log(`[${s.name}] Command: ${cmd.action}`);
                if (cmd.action === 'START') await startServer(s);
                if (cmd.action === 'STOP') await stopServer(s);
                completedCmds.push(cmd.id);
            }
        }

        // Heartbeat
        const isRunning = !!active.process;
        // Only update if status mismatch or periodical?
        // Let's just update always for now with current status
        const currentStatus = isRunning ? 'online' : 'offline';
        // Optimize: Only call if changed or every X ticks? 
        // For MVP, call every sync (3s).
        await apiCall('client_update', {
            token: s.device_token,
            status: currentStatus,
            public_ip: isRunning ? `127.0.0.1:${s.port}` : null
        });
    }

    // 3. Ack Commands
    if (completedCmds.length > 0) {
        await apiCall('node_update_cmd', {
            completed_ids: completedCmds
        });
    }
}

async function startServer(serverInfo) {
    const active = activeServers[serverInfo.id];
    if (active.process) {
        log(`[${serverInfo.name}] Already running.`);
        return;
    }

    log(`[${serverInfo.name}] Starting... (Owner: ${serverInfo.owner_name})`);

    // Setup Dir
    const serverDir = path.join(__dirname, 'servers', serverInfo.id);
    if (!fs.existsSync(serverDir)) fs.mkdirSync(serverDir, {
        recursive: true
    });

    // Copy Script
    const scriptSrc = path.resolve(__dirname, '../minecraft/mc_world.py');
    const scriptDest = path.join(serverDir, 'mc_world.py');
    try {
        if (fs.existsSync(scriptSrc)) fs.copyFileSync(scriptSrc, scriptDest);
    } catch (e) {
        log("Error copying script: " + e.message);
        return;
    }

    // Spawn
    const child = spawn('python3', ['mc_world.py', serverInfo.port, `--owner=${serverInfo.owner_name}`], {
        cwd: serverDir
    });

    active.process = child;

    child.stdout.on('data', d => {
        const line = d.toString().trim();
        if (line) log(`[${serverInfo.name}] ${line}`);
    });

    child.stderr.on('data', d => { // Python buffers stderr?
        const line = d.toString().trim();
        if (line) log(`[${serverInfo.name} ERR] ${line}`);
    });

    child.on('close', (code) => {
        log(`[${serverInfo.name}] Exited (${code})`);
        active.process = null;
        // Will update status on next sync
    });
}

async function stopServer(serverInfo) {
    const active = activeServers[serverInfo.id];
    if (!active || !active.process) return;
    log(`[${serverInfo.name}] Stopping...`);
    active.process.kill();
    active.process = null;
}

function startPolling() {
    log("Started Node Polling Loop (3s)...");
    setInterval(sync, 3000);
    sync(); // Run immediately
}

// Start
init();