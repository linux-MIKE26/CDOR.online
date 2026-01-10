import socket
import struct
import json
import sys
import threading

def read_varint(sock):
    data = b''
    while True:
        byte = sock.recv(1)
        if not byte: return 0
        data += byte
        if (byte[0] & 0x80) == 0:
            break
    return data

def send_packet(sock, packet_id, data):
    packet_id_bytes = bytes([packet_id])  # Simple VarInt for low IDs
    length = len(packet_id_bytes) + len(data)
    length_bytes = bytes([length]) # Simplified VarInt
    sock.sendall(length_bytes + packet_id_bytes + data)

def handle_client(conn):
    try:
        # 1. Read Handshake length (VarInt)
        length = read_varint(conn) 
        # 2. Read Packet ID (0x00)
        pid = conn.recv(1)
        
        # 3. Read Protocol Version (VarInt) - Just skip
        read_varint(conn)
        
        # 4. Read Server Address (String)
        addr_len_byte = conn.recv(1)
        if not addr_len_byte: return
        addr_len = addr_len_byte[0]
        conn.recv(addr_len)
        
        # 5. Read Port (Short)
        conn.recv(2)
        
        # 6. Read Next State (VarInt)
        next_state_byte = conn.recv(1)
        if not next_state_byte: return
        next_state = next_state_byte[0]

        # STATE 1: STATUS
        if next_state == 1:
            # Client sends Request (0x00)
            conn.recv(2) # Len + ID
            
            # Send Response (0x00)
            motd = {
                "version": {
                    "name": "1.21", 
                    "protocol": 767
                },
                "players": {
                    "max": 20,
                    "online": 0,
                    "sample": []
                },
                "description": {
                    "text": "§a§lCONNECTED! §eServer is Online via Python"
                }
            }
            json_resp = json.dumps(motd)
            json_bytes = json_resp.encode('utf-8')
            
            # Packet: ID 0x00 | String Length (VarInt) | String
            # Simple VarInt logic for length
            def encode_varint(val):
                out = b''
                while True:
                    byte = val & 0x7F
                    val >>= 7
                    if val != 0:
                        byte |= 0x80
                    out += bytes([byte])
                    if val == 0: break
                return out

            data = encode_varint(len(json_bytes)) + json_bytes
            
            # Final Frame
            frame = encode_varint(len(data) + 1) + b'\x00' + data
            conn.sendall(frame)
            
            # Handle Ping (0x01)
            # data = conn.recv(1024)
            # if data: conn.sendall(data) # Echo back
            
    except Exception as e:
        pass
    finally:
        conn.close()

def main():
    if len(sys.argv) < 2:
        print("Usage: python3 mock_mc_server.py <port>")
        return

    port = int(sys.argv[1])
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    
    try:
        server.bind(('0.0.0.0', port))
        server.listen(5)
        print(f"Mock MC Server listening on 0.0.0.0:{port}")
        
        while True:
            conn, addr = server.accept()
            t = threading.Thread(target=handle_client, args=(conn,))
            t.start()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main () 