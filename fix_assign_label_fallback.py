from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'value:qe,onChange:T=>{const N=T.target.value,L=p.find(w=>String((w==null?void 0:w.key)??"")===N);Ke(N),Be(String((L==null?void 0:L.label)??""))}'
new = 'value:qe,onChange:T=>{const N=T.target.value,L=p.find(w=>String((w==null?void 0:w.key)??"")===N),w=String((L==null?void 0:L.label)??"").trim()||String((L==null?void 0:L.key)??N).replace(/_/g," ").replace(/\\b\\w/g,O=>O.toUpperCase());Ke(N),Be(w)}'

for path in paths:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'old snippet not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print('patched', path)
