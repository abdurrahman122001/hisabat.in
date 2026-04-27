from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'u.printers.map',
    'printers.map(',
    'N.map(',
    'w.map(',
    'children:"Printer adı"',
    'children:"Material adı"',
    'children:"Price key"',
    'children:"Kateqoriya"',
    'children:"Əməliyyat"',
    'children:"Actions"',
    'children:"Status"'
]
out=[]
for needle in needles:
    idx=s.find(needle)
    out.append(f'\n=== {needle} {idx} ===\n')
    if idx!=-1:
        out.append(s[max(0, idx-2500):min(len(s), idx+9000)])
Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts\settings_actions.txt').write_text(''.join(out), encoding='utf-8')
print('done')
