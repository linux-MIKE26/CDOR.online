<?php
/**
 * Native PHP Minecraft Server Emulator
 * Responds to Server List Ping (SLP) protocol
 */

if (php_sapi_name() !== 'cli') {
    die("Run from CLI only\n");
}

$port = (int)($argv[1] ?? 25565);

echo "[Server] Starting on port $port...\n";

// Create TCP socket
$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$socket) {
    die("[Error] Cannot create socket\n");
}

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

if (!@socket_bind($socket, '0.0.0.0', $port)) {
    die("[Error] Cannot bind to port $port\n");
}

if (!@socket_listen($socket, 5)) {
    die("[Error] Cannot listen\n");
}

echo "[Server] Listening on 0.0.0.0:$port\n";

// VarInt helpers
function readVarInt($conn) {
    $result = 0;
    $shift = 0;
    while (true) {
        $byte = @socket_read($conn, 1);
        if ($byte === false || $byte === '') return -1;
        $b = ord($byte);
        $result |= ($b & 0x7F) << $shift;
        if (($b & 0x80) === 0) break;
        $shift += 7;
        if ($shift >= 35) return -1;
    }
    return $result;
}

function writeVarInt($value) {
    $out = '';
    while (true) {
        if (($value & ~0x7F) === 0) {
            $out .= chr($value);
            return $out;
        }
        $out .= chr(($value & 0x7F) | 0x80);
        $value >>= 7;
    }
}

// Main loop
while (true) {
    $conn = @socket_accept($socket);
    if (!$conn) continue;
    
    try {
        // Read packet length + ID
        $len = readVarInt($conn);
        if ($len < 0) throw new Exception("Invalid packet");
        
        $packetId = readVarInt($conn);
        
        if ($packetId === 0) {
            // Handshake
            $protoVer = readVarInt($conn);
            $addrLen = readVarInt($conn);
            if ($addrLen > 0) @socket_read($conn, $addrLen);
            @socket_read($conn, 2); // port
            $nextState = readVarInt($conn);
            
            if ($nextState === 1) {
                // Status request - read empty packet
                readVarInt($conn);
                readVarInt($conn);
                
                // Send response
                $motd = json_encode([
                    'version' => ['name' => '1.21', 'protocol' => 767],
                    'players' => ['max' => 20, 'online' => 0, 'sample' => []],
                    'description' => ['text' => '§a§lCDOR HOSTING §r§7- Online']
                ]);
                
                $packet = writeVarInt(0) . writeVarInt(strlen($motd)) . $motd;
                $frame = writeVarInt(strlen($packet)) . $packet;
                @socket_write($conn, $frame);
                
                // Handle ping
                $len = readVarInt($conn);
                $pingId = readVarInt($conn);
                if ($pingId === 1) {
                    $payload = @socket_read($conn, 8);
                    if ($payload) {
                        $pong = writeVarInt(1) . $payload;
                        @socket_write($conn, writeVarInt(strlen($pong)) . $pong);
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Ignore
    }
    
    @socket_close($conn);
}
