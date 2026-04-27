from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    '/hesabat/api/materials_create.php',
    '/hesabat/api/materials_update.php',
    '/hesabat/api/printers_list.php',
    'materials_list.php',
    'category:',
    'label:"Kateqoriya"',
    'children:"Kateqoriya"',
    'mat_key',
]
for needle in needles:
    i = s.find(needle)
    print('='*80)
    print('needle:', needle, 'index:', i)
    if i != -1:
        print(s[max(0, i-3500):i+6000])
