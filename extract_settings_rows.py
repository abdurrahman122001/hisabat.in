from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['Printer yaradıldı','Material yaradıldı','Yeni printer','Yeni material','Active','Deaktiv','status']:
    idx = s.find(needle)
    print('\n===', needle, idx, '===')
    if idx != -1:
        print(s[max(0, idx-3500):min(len(s), idx+7000)])
