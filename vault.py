import os
import sys
import base64
import hashlib
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
from cryptography.hazmat.backends import default_backend

# CDOR v7.0 CYBER-VAULT SYSTEM
# Encriptación Determinista AES-256 (CTR Mode) para compatibilidad con Git

def generate_key_from_password(password: str, salt: bytes) -> bytes:
    kdf = PBKDF2HMAC(
        algorithm=hashes.SHA256(),
        length=32,
        salt=salt,
        iterations=100000,
        backend=default_backend()
    )
    return kdf.derive(password.encode())

class CryptoEngine:
    def __init__(self, key):
        self.key = key
        self.backend = default_backend()

    def encrypt(self, data: bytes) -> bytes:
        # Deterministic IV derived from content hash
        # This ensures that git filters produce the same blob for the same content
        h = hashlib.sha256(data).digest()
        iv = h[:16]
        
        cipher = Cipher(algorithms.AES(self.key), modes.CTR(iv), backend=self.backend)
        encryptor = cipher.encryptor()
        ciphertext = encryptor.update(data) + encryptor.finalize()
        
        return iv + ciphertext

    def decrypt(self, data: bytes) -> bytes:
        if len(data) < 16:
            raise ValueError("Datos corruptos o demasiado cortos")
        
        iv = data[:16]
        ciphertext = data[16:]
        
        cipher = Cipher(algorithms.AES(self.key), modes.CTR(iv), backend=self.backend)
        decryptor = cipher.decryptor()
        plaintext = decryptor.update(ciphertext) + decryptor.finalize()
        
        return plaintext

def get_salt(salt_file='.vault_salt'):
    if not os.path.exists(salt_file):
        # Si no existe, creamos una nueva sal.
        # NOTA: Para un repo ya existente, esto debería existir.
        salt = os.urandom(16)
        with open(salt_file, 'wb') as f: f.write(salt)
        return salt
    with open(salt_file, 'rb') as f: return f.read()

def stream_process(action, password, salt_file='.vault_salt'):
    # Para git attributes, la CWD suele ser el root del repo, así que .vault_salt debería ser accesible.
    # Si no, intentamos buscarlo en el directorio del script.
    if not os.path.exists(salt_file):
        script_dir = os.path.dirname(os.path.abspath(__file__))
        alt_salt = os.path.join(script_dir, salt_file)
        if os.path.exists(alt_salt):
            salt_file = alt_salt
    
    try:
        salt = get_salt(salt_file)
        key = generate_key_from_password(password, salt)
        engine = CryptoEngine(key)

        # Leer de stdin bufferizado
        data = sys.stdin.buffer.read()
        
        if action == 'encrypt':
            result = engine.encrypt(data)
        else:
            try:
                result = engine.decrypt(data)
            except Exception:
                # Si falla decriptar, asumimos que es plaintext (caso borde para git)
                # o devolvemos error. Para git, devolver data original a veces salva diffs.
                result = data 
                
        sys.stdout.buffer.write(result)
    except Exception as e:
        sys.stderr.write(f"Error en stream processing: {e}\n")
        sys.exit(1)

def dir_process(action, password, directory='.', salt_file='.vault_salt'):
    salt = get_salt(salt_file)
    key = generate_key_from_password(password, salt)
    engine = CryptoEngine(key)

    ignore_files = ['vault.py', '.vault_salt', '.git', '.gitignore', 'README.md', 'LICENSE', '.gitattributes']
    
    count = 0
    for root, dirs, files in os.walk(directory):
        if '.git' in dirs: dirs.remove('.git')
        
        for file in files:
            if file in ignore_files: continue
            
            file_path = os.path.join(root, file)
            try:
                with open(file_path, 'rb') as f: data = f.read()
                
                if action == 'encrypt':
                    processed_data = engine.encrypt(data)
                else:
                    processed_data = engine.decrypt(data)

                with open(file_path, 'wb') as f: f.write(processed_data)
                print(f"[+] {action.capitalize()}: {file_path}")
                count += 1
            except Exception as e:
                print(f"[-] Fallo en {file_path}: {str(e)}")

    print(f"\n[!] Operación finalizada. {count} archivos procesados.")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Uso: python3 vault.py <encrypt|decrypt|stream> <password> [sub-command]")
        print("Ejemplo Stream: python3 vault.py stream <password> encrypt")
        sys.exit(1)

    mode = sys.argv[1]
    password = sys.argv[2]
    
    if mode == 'stream':
        if len(sys.argv) < 4:
            print("Uso Stream: python3 vault.py stream <password> <encrypt|decrypt>")
            sys.exit(1)
        sub_action = sys.argv[3]
        stream_process(sub_action, password)
    elif mode in ['encrypt', 'decrypt']:
        dir_process(mode, password)
    else:
        print("Modo desconocido.")
        sys.exit(1)
