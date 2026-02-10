
import os
import shutil
import glob

# Configuration
SOURCE_DIR = os.getcwd() # Root of the workspace
BACKUP_DIR = os.path.join(SOURCE_DIR, 'Backup_2026')

# Define categories and their patterns
ORGANIZATION = {
    'SQL_Dumps': ['*.sql'],
    'Old_Scripts': ['*copy*.php', '*_old*.php', '*_backup*.php', 'index.ph*.gdoc'],
    'Debug_Fix_Scripts': ['debug_*.php', 'fix_*.php', 'test_*.php', 'diag_*.php', 'temp_*.php', 'valida_demo.php', 'ver_*.php', 'check_*.php'],
    'Misplaced_Assets': ['*.png', '*.json', '*.jpg', '*.jpeg'],
    'Docs_and_Misc': ['*.txt', '*.md', '*.pdf', '*.zip', '*.lnk', '*.ini', 'sugestao_*.txt', 'veo_*.txt'],
    'External_Tools': [] # Folders handled separately
}

# Whitelist (Files/Patterns to NEVER move)
WHITELIST = [
    'composer.json', 'package.json', 'manifest.json', 'sitemap.xml', 'robots.txt',
    'GEMINI.md', 'README.md', 'LICENSE', 'task.md',
    'config.php', 'db.php', 'conexao.php', 'database.php',
    'index.php', 'login.php', 'logout.php', 'painel.php', 'dashboard.php',
    'editor_dinamico.php', 'salvar_proposta.php', 'gerar_proposta_html.php',
    'termos_uso.php', 'politica_privacidade.php', 'service-worker.js',
    'version.php', 'versao.php'
]

# Folders to move entire trees
FOLDERS_TO_MOVE = [
    'Sugestao_kimi_CRM', 'Sugestão_mobile_Kimi', 'nova_interface_temp', 'temp_extract_check', 'HTML'
]

def is_whitelisted(filename):
    if filename in WHITELIST:
        return True
    # Add logic for critical system prefixes if needed, but the pattern matching above is specific
    return False

def ensure_dir(path):
    if not os.path.exists(path):
        os.makedirs(path)
        print(f"Created directory: {path}")

def move_files():
    print(f"Starting cleanup from {SOURCE_DIR} to {BACKUP_DIR}")
    ensure_dir(BACKUP_DIR)

    # 1. Handle Files by Pattern
    for subfolder, patterns in ORGANIZATION.items():
        dest_path = os.path.join(BACKUP_DIR, subfolder)
        ensure_dir(dest_path)
        
        for pattern in patterns:
            # Glob files in root only (non-recursive)
            files = glob.glob(os.path.join(SOURCE_DIR, pattern))
            
            for file_path in files:
                filename = os.path.basename(file_path)
                
                # Skip directories matched by glob
                if os.path.isdir(file_path):
                    continue
                    
                if is_whitelisted(filename):
                    print(f"Skipping whitelisted file: {filename}")
                    continue
                
                # Special check for assets: only move if they look like scene assets or misplaced
                if subfolder == 'Misplaced_Assets':
                    if filename == 'manifest.json': continue 
                
                try:
                    dest_file = os.path.join(dest_path, filename)
                    if os.path.exists(dest_file):
                        # Rename if exists
                        base, ext = os.path.splitext(filename)
                        dest_file = os.path.join(dest_path, f"{base}_dup{ext}")
                    
                    shutil.move(file_path, dest_file)
                    print(f"Moved: {filename} -> {subfolder}/")
                except Exception as e:
                    print(f"Error moving {filename}: {e}")

    # 2. Handle Specific Directories
    dest_tools = os.path.join(BACKUP_DIR, 'External_Tools')
    ensure_dir(dest_tools)
    
    for folder_name in FOLDERS_TO_MOVE:
        src_path = os.path.join(SOURCE_DIR, folder_name)
        if os.path.exists(src_path) and os.path.isdir(src_path):
            try:
                dest_path = os.path.join(dest_tools, folder_name)
                if os.path.exists(dest_path):
                    print(f"Destination folder exists, skipping merge: {folder_name}")
                    # In a real script we might merge, but here we skip to be safe
                else:
                    shutil.move(src_path, dest_path)
                    print(f"Moved directory: {folder_name} -> External_Tools/")
            except Exception as e:
                print(f"Error moving directory {folder_name}: {e}")

if __name__ == '__main__':
    move_files()
