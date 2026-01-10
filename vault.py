import os
import sys
import base64
from cryptography.fernet import Fernet
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC

# CDOR v6.0 CYBER-VAULT SYSTEM
# Autocifrado simétrico AES-256 para protección total del repositorio

def generate_key_from_password(password: str, salt: bytes) -> bytes:
    kdf = PBKDF2HMAC(
        algorithm=hashes.SHA256(),
        length=32,
        salt=salt,
        iterations=100000,
    )
    return base64.urlsafe_b64encode(kdf.derive(password.encode()))

def process_files(action, password, directory='.', salt_file='.vault_salt'):
    if not os.path.exists(salt_file):
        if action == 'encrypt':
            salt = os.urandom(16)
            with open(salt_file, 'wb') as f: f.write(salt)
        else:
            print("[-] Error: No se encontró el archivo de sal (.vault_salt)")
            return
    else:
        with open(salt_file, 'rb') as f: salt = f.read()

    key = generate_key_from_password(password, salt)
    fernet = Fernet(key)

    # Extensiones a ignorar para no romper el sistema
    ignore_files = ['vault.py', '.vault_salt', '.git', '.gitignore', 'README.md', 'LICENSE']
    
    count = 0
    for root, dirs, files in os.walk(directory):
        if '.git' in dirs: dirs.remove('.git')
        
        for file in files:
            if file in ignore_files: continue
            
            file_path = os.path.join(root, file)
            
            try:
                with open(file_path, 'rb') as f:
                    data = f.read()

                if action == 'encrypt':
                    processed_data = fernet.encrypt(data)
                else:
                    processed_data = fernet.decrypt(data)

                with open(file_path, 'wb') as f:
                    f.write(processed_data)
                
                print(f"[+] {action.capitalize()}: {file_path}")
                count += 1
            except Exception as e:
                print(f"[-] Fallo en {file_path}: {str(e)}")

    print(f"\n[!] Operación finalizada. {count} archivos procesados.")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Uso: python3 vault.py <encrypt|decrypt> <password>")
        sys.exit(1)

    action = sys.argv[1]
    password = sys.argv[2]
    process_files(action, password)
