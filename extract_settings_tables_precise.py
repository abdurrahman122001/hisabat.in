from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = ['Yeni printer','Yeni material','Printer yaradıldı','Material yaradıldı','Printers']
out = []
for needle in needles:
    idx = s.find(needle)
    out.append(f'\n=== {needle} {idx} ===\n')
    if idx != -1:
        snippet = s[max(0, idx-8000):min(len(s), idx+16000)]
        out.append(snippet)
Path(r'c:\xampp\htdocs\Hesabat\settings_tables_dump.txt').write_text(''.join(out), encoding='utf-8')
print('written')
