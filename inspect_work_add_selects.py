from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'children:"Printer seç"',
    'children:"Material seç"',
    'v.map(V=>o.jsx("option"',
    'j.filter',
    'printer:"",material:"",width_cm:"",height_cm:"",qty:"",price_per_m2:""',
]
for needle in needles:
    i = s.find(needle)
    print('='*80)
    print(needle, i)
    if i != -1:
        print(s[max(0, i-2500):i+3500])
