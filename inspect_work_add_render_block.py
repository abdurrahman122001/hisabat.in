from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'Xt=V=>v.find(F=>F.name===V)||null',
    'children:"Çap İşi Əlavə Et"',
    'children:"Printer seç"',
    'children:"Material seç"',
    'create_work.php',
]
for needle in needles:
    i = s.find(needle)
    print('='*80)
    print('needle:', needle, 'index:', i)
    if i != -1:
        print(s[max(0, i-4000):i+7000])
