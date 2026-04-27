from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = 'Çap İşi Əlavə Et'
idx = s.find(needle)
print('idx', idx)
if idx != -1:
    start = s.rfind('function ', 0, idx)
    end = s.find('function ', idx + 1)
    print('start', start, 'end', end)
    print(s[start:end if end != -1 else idx + 30000])
