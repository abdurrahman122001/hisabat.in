from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['Xt(V)', 'const U=Xt(V)', 'return"";const U=Xt(V)', 'Y!=="konica"&&Y!=="roland"', 'const Z=(se=a==null?void 0:a[Y])==null?void 0:se[F]', 'const Gt=', 'Gt=V=>', 'Gt=(V,F)=>']:
    idx = s.find(needle)
    print('\n===', needle, idx, '===')
    if idx != -1:
        print(s[max(0, idx-700):min(len(s), idx+2200)])
