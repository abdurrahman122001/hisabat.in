from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'o.jsx(z,{label:"key",value:R,disabled:!0,readOnly:!0})',
    'children:"Mövcud material seç"',
    'children:"Printer seç"',
    'children:"Category"',
    'children:"Status"',
    'p.map(T=>o.jsxs("tr"',
    'onClick:()=>C(T.id,{status:Number(N.target.value)})',
    'onClick:()=>q()',
]
out = []
for needle in needles:
    idx = s.find(needle)
    out.append(f'\n=== {needle} {idx} ===\n')
    if idx != -1:
        out.append(s[max(0, idx-1800):min(len(s), idx+4200)])
Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts\ui_material_details.txt').write_text(''.join(out), encoding='utf-8')
print('done')
