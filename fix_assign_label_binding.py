from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'o.jsx(z,{label:"label",value:W,onChange:T=>J(T.target.value)})'
new = 'o.jsx(z,{label:"label",value:Ve,onChange:T=>Be(T.target.value)})'

for path in paths:
    s = path.read_text(encoding='utf-8')
    count = s.count(old)
    if count < 1:
        raise SystemExit(f'label binding not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(path, 'replaced', count)
