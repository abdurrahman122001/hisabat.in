from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['Gt(', 'const Gt=', 'Kn(', 'const Kn=', 'm=[{printer:"",material:"",width_cm:"",height_cm:"",qty:"",price_per_m2:""}]', 'price_per_m2:bi||q[U].price_per_m2', 'price_per_m2:we||q[U].price_per_m2']:
    idx = s.find(needle)
    print('\nNEEDLE', needle, 'IDX', idx)
    if idx != -1:
        print(s[max(0, idx-4000):min(len(s), idx+12000)])
