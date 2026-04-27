from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = ['const Gt=', 'Gt=(', 'const Kn=', 'Kn=(', 'const xi=', 'xi=(', 'Çap İşi Əlavə Et']
for needle in needles:
    idx = s.find(needle)
    print('\nNEEDLE', needle, 'IDX', idx)
    if idx != -1:
        print(s[max(0, idx-8000):min(len(s), idx+20000)])
        print('\n' + '='*120 + '\n')
