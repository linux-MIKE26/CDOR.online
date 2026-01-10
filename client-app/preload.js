const {
    contextBridge,
    ipcRenderer
} = require('electron');

contextBridge.exposeInMainWorld('api', {
    // Auth
    login: (token) => ipcRenderer.invoke('login', token),

    // UI Updates
    onStatusUpdate: (callback) => ipcRenderer.on('status-update', (event, data) => callback(data)),
    onLog: (callback) => ipcRenderer.on('log', (event, msg) => callback(msg)),

    // Controls
    startServer: () => ipcRenderer.invoke('start-server'),
    stopServer: () => ipcRenderer.invoke('stop-server')
});