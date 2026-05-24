import os
import re

BASE_DIR = r"c:\xampp\htdocs\sistema"
DIRS_TO_SCAN = [
    os.path.join(BASE_DIR, "app"),
    os.path.join(BASE_DIR, "resources"),
    os.path.join(BASE_DIR, "database"),
    os.path.join(BASE_DIR, "routes")
]

REPLACEMENTS = [
    # Full words replacement with case sensitivity boundaries
    (r'\bPnfs\b', 'Coordinaciones'),
    (r'\bpnfs\b', 'coordinaciones'),
    
    (r'\bPnf\b', 'Coordinacion'),
    (r'\bpnf_id\b', 'coordinacion_id'),
    
    # Common variables and relations, note we already hit $pnf if we did \bpnf\b
    # Let's replace 'pnf' but AVOID 'coordinador pnf' or similar string if needed.
    # Wait, 'coordinador pnf' and 'coordinador pnf' -> we can just let it become 'coordinador pnf' or 'coordinador_pnf' 
    # Actually, let's keep the role name exact: if we do replacing 'pnf' we might break the exact role name 'coordinador pnf'
    (r'\bpnf\b(?!\s*\])(?!\s*\'\])(?!\'\s*\])', 'coordinacion'), # but 'coordinador pnf' has 'pnf' at the end.
    
    # We can temporarily hide 'coordinador pnf'
    (r'coordinador pnf', 'COORDINADOR_PNF_TEMP_PLACEHOLDER'),
    (r'Coordinador PNF', 'COORDINADOR_PNF_TITLE_TEMP_PLACEHOLDER'),
]



def file_replacements(content):
    # Hide role names
    content = content.replace("coordinador pnf", "COORDINADOR_PNF_TEMP_PLACEHOLDER")
    content = content.replace("Coordinador PNF", "COORDINADOR_PNF_TITLE_TEMP_PLACEHOLDER")
    content = content.replace("Coordinación de PNF", "COORDINACION_DE_PNF_TITLE_TEMP_PLACEHOLDER")
    
    # Common specific fixes
    content = content.replace("pnf_id", "coordinacion_id")
    content = content.replace("pnfs", "coordinaciones")
    content = content.replace("Pnfs", "Coordinaciones")
    content = content.replace("PNFs", "Coordinaciones")
    content = content.replace("Pnf", "Coordinacion")
    content = content.replace("PNF", "Coordinación")
    content = content.replace("pnf", "coordinacion")

    # Restore role names
    content = content.replace("COORDINADOR_PNF_TEMP_PLACEHOLDER", "coordinador pnf")
    content = content.replace("COORDINADOR_PNF_TITLE_TEMP_PLACEHOLDER", "Coordinador PNF")
    content = content.replace("COORDINACION_DE_PNF_TITLE_TEMP_PLACEHOLDER", "Coordinación")

    # specific livewire fixes 
    # livewire properties that were changed inappropriately if they were camelCase, etc:
    content = content.replace("filterCoordinacion", "filterCoordinacion")
    content = content.replace("CoordinacionManager", "CoordinacionManager")

    return content

for d in DIRS_TO_SCAN:
    for root, dirs, files in os.walk(d):
        for file in files:
            file_path = os.path.join(root, file)
            # rename inside contents
            if not file.endswith(('.php', '.blade.php', '.js', '.css', '.json')):
                continue

            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()

            new_content = file_replacements(content)

            if new_content != content:
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f"Updated content in {file_path}")

print("Content replacement finished. Starting file renaming...")

# Now rename files/directories
# We do this bottom up so we don't break paths
rename_queue = []
for d in DIRS_TO_SCAN:
    for root, dirs, files in os.walk(d, topdown=False):
        for name in files:
            if 'pnf' in name.lower():
                old_path = os.path.join(root, name)
                # Keep case for file replacement if possible
                if 'Pnf.php' in name:
                    new_name = name.replace('Pnf', 'Coordinacion')
                elif 'pnfs' in name:
                    new_name = name.replace('pnfs', 'coordinaciones')
                elif 'pnf' in name:
                    new_name = name.replace('pnf', 'coordinacion')
                new_path = os.path.join(root, new_name)
                rename_queue.append((old_path, new_path))
                
        for name in dirs:
            if 'pnf' in name.lower():
                old_path = os.path.join(root, name)
                if 'pnfs' in name:
                    new_name = name.replace('pnfs', 'coordinaciones')
                elif 'pnf' in name:
                    new_name = name.replace('pnf', 'coordinacion')
                new_path = os.path.join(root, new_name)
                rename_queue.append((old_path, new_path))

for old, new in rename_queue:
    os.rename(old, new)
    print(f"Renamed {old} to {new}")

print("Refactoring complete.")
