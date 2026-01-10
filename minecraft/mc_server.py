#!/usr/bin/env python3
"""
Minecraft Server Emulator - Responds to Server List Ping and Login attempts
Compatible with Minecraft Java Edition 1.7+
"""
import socket
import struct
import json
import sys
import threading

def read_varint(sock):
    """Read a VarInt from socket"""
    num = 0
    for i in range(5):
        try:
            byte = sock.recv(1)
            if not byte:
                return None
            b = byte[0]
            num |= (b & 0x7F) << (7 * i)
            if not (b & 0x80):
                return num
        except:
            return None
    return None

def write_varint(value):
    """Encode a VarInt"""
    out = b''
    while True:
        byte = value & 0x7F
        value >>= 7
        if value:
            byte |= 0x80
        out += bytes([byte])
        if not value:
            break
    return out

def make_packet(packet_id, data=b''):
    """Create a Minecraft packet with length prefix"""
    packet_id_bytes = write_varint(packet_id)
    packet_data = packet_id_bytes + data
    return write_varint(len(packet_data)) + packet_data

def handle_client(conn, addr):
    """Handle a single client connection"""
    try:
        # Read first packet (Handshake)
        length = read_varint(conn)
        if length is None or length <= 0:
            return
            
        packet_id = read_varint(conn)
        if packet_id != 0:
            return
            
        # Protocol version
        protocol = read_varint(conn)
        
        # Server address (string)
        addr_len = read_varint(conn)
        if addr_len and addr_len > 0:
            conn.recv(addr_len)
        
        # Port
        conn.recv(2)
        
        # Next state (1=status, 2=login)
        next_state = read_varint(conn)
        
        if next_state == 1:
            # STATUS - Server List Ping
            # Read status request (packet 0x00)
            read_varint(conn)  # length
            read_varint(conn)  # packet id
            
            # Build response
            response = {
                "version": {
                    "name": "1.21.4",
                    "protocol": 769
                },
                "players": {
                    "max": 20,
                    "online": 0,
                    "sample": []
                },
                "description": {
                    "text": "\u00a7a\u00a7lCDOR HOSTING \u00a7r\u00a77- Servidor Activo"
                },
                "favicon": ""
            }
            json_str = json.dumps(response)
            json_bytes = json_str.encode('utf-8')
            
            # Send response packet (0x00)
            data = write_varint(len(json_bytes)) + json_bytes
            conn.sendall(make_packet(0x00, data))
            
            # Handle ping (0x01)
            try:
                read_varint(conn)  # length
                pid = read_varint(conn)  # packet id
                if pid == 1:
                    payload = conn.recv(8)
                    if payload:
                        conn.sendall(make_packet(0x01, payload))
            except:
                pass
                
        elif next_state == 2:
            # LOGIN
            # Read login start packet
            length = read_varint(conn)
            packet_id = read_varint(conn)
            
            # Read player name
            name_len = read_varint(conn)
            player_name = conn.recv(name_len).decode('utf-8') if name_len else "Player"
            
            # Send disconnect with message
            disconnect_msg = json.dumps({
                "text": "\u00a7e\u00a7lBienvenido a CDOR Hosting!\n\n\u00a77Este es un servidor de demostración.\n\u00a7aLa conexión funciona correctamente!"
            })
            msg_bytes = disconnect_msg.encode('utf-8')
            data = write_varint(len(msg_bytes)) + msg_bytes
            conn.sendall(make_packet(0x00, data))
            
    except Exception as e:
        pass
    finally:
        try:
            conn.close()
        except:
            pass

def main():
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 25565
    
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    
    try:
        server.bind(('0.0.0.0', port))
        server.listen(5)
        print(f"[MC Server] Listening on 0.0.0.0:{port}")
        
        while True:
            try:
                conn, addr = server.accept()
                thread = threading.Thread(target=handle_client, args=(conn, addr))
                thread.daemon = True
                thread.start()
            except KeyboardInterrupt:
                break
            except:
                continue
    except Exception as e:
        print(f"[Error] {e}")
        sys.exit(1)
    finally:
        server.close()

if __name__ == "__main__":
    main()
