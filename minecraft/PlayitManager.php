<?php
/**
 * Playit.gg Tunnel Manager
 * Manages Playit tunnels for Minecraft servers
 */

class PlayitManager {
    private $playitBinary;
    private $secretFile;
    private $tunnelsDir;
    
    public function __construct() {
        // Robust binary searching
        $bin = __DIR__ . '/playit/playit';
        if (!file_exists($bin)) $bin = __DIR__ . '/playit'; // maybe it was put directly in folder as 'playit'
        if (!file_exists($bin) || !is_executable($bin)) {
            $found = realpath(__DIR__ . '/playit/playit');
            if ($found) $bin = $found;
        }
        
        $this->playitBinary = $bin;
        $this->secretFile = __DIR__ . '/playit/secret.txt';
        $this->tunnelsDir = __DIR__ . '/playit/tunnels';
        
        if (!file_exists($this->tunnelsDir)) {
            @mkdir($this->tunnelsDir, 0755, true);
        }
    }
    
    /**
     * Check if Playit is configured
     */
    public function isConfigured() {
        return file_exists($this->secretFile) && file_exists($this->playitBinary);
    }
    
    /**
     * Get the secret key (global or per-server)
     */
    private function getSecret($serverId = null) {
        // Try server-specific secret first
        if ($serverId) {
            $serverSecretFile = __DIR__ . "/playit/secrets/{$serverId}.txt";
            if (file_exists($serverSecretFile)) {
                return trim(file_get_contents($serverSecretFile));
            }
        }
        
        // Fall back to global secret
        if (!file_exists($this->secretFile)) {
            return null;
        }
        return trim(file_get_contents($this->secretFile));
    }
    
    /**
     * Create a tunnel for a server
     * @param string $serverId
     * @param int $localPort
     * @return array ['success' => bool, 'public_address' => string, 'tunnel_id' => string, 'error' => string]
     */
    public function createTunnel($serverId, $localPort) {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Playit not configured. Run setup_playit.sh first.'];
        }
        
        $secret = $this->getSecret($serverId);
        if (!$secret) {
            return ['success' => false, 'error' => 'No secret key found for this server'];
        }
        
        $logFile = $this->tunnelsDir . "/{$serverId}.log";
        $pidFile = $this->tunnelsDir . "/{$serverId}.pid";
        
        // Start Playit agent for this server
        $cmd = sprintf(
            'nohup %s --secret %s start > %s 2>&1 & echo $!',
            escapeshellarg($this->playitBinary),
            escapeshellarg($secret),
            escapeshellarg($logFile)
        );
        
        $pid = trim(shell_exec($cmd));
        
        if (!$pid || !is_numeric($pid)) {
            return ['success' => false, 'error' => 'Failed to start Playit agent'];
        }
        
        file_put_contents($pidFile, $pid);
        
        // Wait for tunnel to be created and parse the public address
        sleep(3);
        
        $publicAddress = $this->parsePublicAddress($logFile);
        
        if (!$publicAddress) {
            $this->destroyTunnel($serverId);
            return ['success' => false, 'error' => 'Failed to get public address from Playit'];
        }
        
        return [
            'success' => true,
            'public_address' => $publicAddress,
            'tunnel_id' => $pid,
            'log_file' => $logFile
        ];
    }
    
    /**
     * Parse public address from Playit log
     */
    private function parsePublicAddress($logFile) {
        if (!file_exists($logFile)) {
            return null;
        }
        
        $log = file_get_contents($logFile);
        
        // Look for patterns like "playit.gg address: example.playit.gg:12345"
        // or "tunnel created: tcp://example.playit.gg:12345"
        if (preg_match('/(?:address|tunnel):\s*(?:tcp:\/\/)?([a-z0-9\-\.]+\.playit\.gg:\d+)/i', $log, $matches)) {
            return $matches[1];
        }
        
        // Alternative pattern
        if (preg_match('/([a-z0-9\-]+\.playit\.gg:\d+)/i', $log, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Destroy a tunnel
     */
    public function destroyTunnel($serverId) {
        $pidFile = $this->tunnelsDir . "/{$serverId}.pid";
        $logFile = $this->tunnelsDir . "/{$serverId}.log";
        
        if (file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            if ($pid && is_numeric($pid)) {
                // Kill the process
                shell_exec("kill $pid 2>/dev/null");
            }
            unlink($pidFile);
        }
        
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        
        return ['success' => true];
    }
    
    /**
     * Get tunnel status
     */
    public function getTunnelStatus($serverId) {
        $pidFile = $this->tunnelsDir . "/{$serverId}.pid";
        $logFile = $this->tunnelsDir . "/{$serverId}.log";
        
        if (!file_exists($pidFile)) {
            return ['active' => false];
        }
        
        $pid = trim(file_get_contents($pidFile));
        
        // Check if process is running
        $running = shell_exec("ps -p $pid -o pid= 2>/dev/null");
        
        if (!$running) {
            return ['active' => false];
        }
        
        $publicAddress = $this->parsePublicAddress($logFile);
        
        return [
            'active' => true,
            'public_address' => $publicAddress,
            'pid' => $pid
        ];
    }
}
