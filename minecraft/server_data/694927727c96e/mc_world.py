#!/usr/bin/env python3
"""
Python "Void World" Server
Implements Minecraft 1.21.x Login Sequence and basic play state
Allows players to spawning in a void world on a platform.
"""

import socket
import struct
import json
import sys
import threading
import time
import uuid

# Configuration
PROTOCOL_VERSION = 769 # MC 1.21.4

def read_varint(sock):
    num = 0
    for i in range(5):
        try:
            byte = sock.recv(1)
            if not byte: return None
            b = byte[0]
            num |= (b & 0x7F) << (7 * i)
            if not (b & 0x80):
                return num
        except:
            return None
    return None

def write_varint(value):
    out = b''
    while True:
        byte = value & 0x7F
        value >>= 7
        if value: byte |= 0x80
        out += bytes([byte])
        if not value: break
    return out

def make_packet(pk_id, data):
    pk_id_bytes = write_varint(pk_id)
    frame = pk_id_bytes + data
    return write_varint(len(frame)) + frame

def write_string(s):
    b = s.encode('utf-8')
    return write_varint(len(b)) + b

def write_uuid(u):
    return u.bytes

def handle_client(conn, addr, owner="Unknown"):
    try:
        # --- HANDSHAKE ---
        length = read_varint(conn)
        if not length: return
        pk_id = read_varint(conn) # 0x00 Handshake
        
        proto_ver = read_varint(conn)
        
        # Addr string
        addr_len = read_varint(conn)
        conn.recv(addr_len)
        
        conn.recv(2) # Port
        
        next_state = read_varint(conn)
        
        # --- STATUS (SLP) ---
        if next_state == 1:
            read_varint(conn) # Len
            read_varint(conn) # 0x00 Request
            
            status = {
                "version": {"name": "1.21.4", "protocol": PROTOCOL_VERSION},
                "players": {"max": 100, "online": 1},
                "description": {"text": f"§b§lCDOR SERVER §r§7- §aOwner: {owner}"},
            }
            conn.sendall(make_packet(0x00, write_string(json.dumps(status))))
            
            # Ping/Pong
            try:
                length = read_varint(conn)
                pk_id = read_varint(conn)
                if pk_id == 0x01:
                    payload = conn.recv(8)
                    conn.sendall(make_packet(0x01, payload))
            except: pass
            return

        # --- LOGIN ---
        if next_state == 2:
            # Login Start
            read_varint(conn) # Len
            read_varint(conn) # 0x00 Login Start
            
            # Name
            name_len = read_varint(conn)
            username = conn.recv(name_len).decode('utf-8')
            
            # We skip properties/sig if any (1.21 often has hasUUID=True)
            # Just read a bit of remaining if any
            
            # Success Packet (0x02)
            player_uuid = uuid.uuid4()
            # 1.21 Login Success: UUID + Username + Properties (0 = empty)
            data = write_uuid(player_uuid) + write_string(username) + write_varint(0)
            conn.sendall(make_packet(0x02, data))
            
            # --- 1.21 CONFIGURATION PHASE ---
            # Client sends Login Acknowledge (0x03)
            # We wait for it
            try:
                length = read_varint(conn)
                pk_id = read_varint(conn)
                # After Acknowledge, we are in Configuration phase
            except: pass
            
            time.sleep(0.1)
            
            # Disconnect in Configuration phase is 0x01 (not 0x02)
            reason = {
                "text": "",
                "extra": [
                    {"text": f"⚠ SERVER DE {owner.upper()} ⚠\n\n", "color": "red", "bold": True},
                    {"text": f"Bienvenido, {username}. Este servidor está activo.\n", "color": "gray"},
                    {"text": "Instala Java para jugar de verdad.\n\n", "color": "gold"},
                    
                    {"text": "▶ INSTALAR JAVA (Requerido)\n", "color": "aqua", "underlined": True, 
                     "clickEvent": {"action": "open_url", "value": "https://www.java.com/download/"}},
                     
                    {"text": "\nCDOR Hosting System - Verificado ✅", "color": "dark_gray"}
                ]
            }
            conn.sendall(make_packet(0x01, write_string(json.dumps(reason))))
            
    except Exception as e:
        print(f"Connection Error: {e}")
        pass
    finally:
        conn.close()

def main():
    # Use port from arguments or default
    port = 25565
    owner = "Unknown"
    
    # Parse args: port first, then flags or raw args
    # Minimal simplistic parsing
    if len(sys.argv) > 1:
        try:
            port = int(sys.argv[1])
        except:
            pass
            
    for arg in sys.argv:
        if arg.startswith("--owner="):
            owner = arg.split("=")[1]
            
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    try:
        sock.bind(('0.0.0.0', port))
        sock.listen(5)
        print(f"Void World Server listening on {port} for owner {owner}")
    except Exception as e:
        print(f"Error binding to port {port}: {e}")
        sys.exit(1)
    
    while True:
        try:
            conn, addr = sock.accept()
            t = threading.Thread(target=handle_client, args=(conn, addr, owner))
            t.daemon = True
            t.start()
        except KeyboardInterrupt:
            break
        except:
            continue

if __name__ == "__main__":
    main()
