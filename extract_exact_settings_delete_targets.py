from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = ['/hesabat/api/printers_create.php','/hesabat/api/printers_update.php','/hesabat/api/materials_create.php','/hesabat/api/materials_update.php']
for needle in needles:
    idx = s.find(needle)
    print('\n===', needle, idx, '===')
    if idx != -1:
        start = max(0, idx-2500)
        end = min(len(s), idx+3500)
        print(s[start:end])
