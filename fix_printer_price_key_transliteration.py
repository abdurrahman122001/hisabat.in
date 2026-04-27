from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

old = 'o.jsx(z,{label:"Printer adı",value:b,onChange:T=>{const N=T.target.value,L=N.trim().toLowerCase().replace(/[^a-z0-9]+/g,"_").replace(/^_+|_+$/g,"");_(N),k(L)}}),o.jsx(z,{label:"price_key (konica/roland/laser və ya boş)",value:E,onChange:T=>k(T.target.value)})'
new = 'o.jsx(z,{label:"Printer adı",value:b,onChange:T=>{const N=T.target.value,L=N.toLowerCase().replace(/[ə]/g,"e").replace(/[ü]/g,"u").replace(/[ö]/g,"o").replace(/[ğ]/g,"g").replace(/[ş]/g,"s").replace(/[ç]/g,"c").replace(/[ı]/g,"i").replace(/[^a-z0-9]+/g,"_").replace(/^_+|_+$/g,"");_(N),k(L)}}),o.jsx(z,{label:"price_key (konica/roland/laser və ya boş)",value:E,onChange:T=>k(T.target.value)})'

for path in paths:
    s = path.read_text(encoding='utf-8')
    if old not in s:
        raise SystemExit(f'target not found in {path}')
    s = s.replace(old, new, 1)
    path.write_text(s, encoding='utf-8')
    print(f'patched {path}')
