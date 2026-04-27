from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

for path in files:
    s = path.read_text(encoding='utf-8')

    old = '[qe,Ke]=S.useState(""),[Ve,Be]=S.useState("")'
    new = '[qe,Ke]=S.useState(""),[Ve,Be]=S.useState(""),[Ze,rt]=S.useState("0")'
    if old in s and new not in s:
        s = s.replace(old, new, 1)

    old = 'body:JSON.stringify({key:R,label:W,categories:ce,category:ce[0]||"",status:1})'
    new = 'body:JSON.stringify({key:R,label:W,categories:ce,category:ce[0]||"",stock_margin:Ze,status:1})'
    if old in s:
        s = s.replace(old, new, 1)

    old = 'H(""),J(""),ee([]),l("Material yaradıldı")'
    new = 'H(""),J(""),ee([]),rt("0"),l("Material yaradıldı")'
    if old in s:
        s = s.replace(old, new, 1)

    old = 'o.jsx(z,{label:"key",value:R,disabled:!0,readOnly:!0})'
    new = 'o.jsxs(o.Fragment,{children:[o.jsx(z,{label:"key",value:R,disabled:!0,readOnly:!0}),o.jsx(z,{label:"Stock margin (+0.1 / +0.05)",type:"number",step:"0.01",value:Ze,onChange:T=>rt(T.target.value)})]})'
    if old in s and 'Stock margin (+0.1 / +0.05)' not in s:
        s = s.replace(old, new, 1)

    old = 'grid grid-cols-1 md:grid-cols-3 gap-4'
    new = 'grid grid-cols-1 md:grid-cols-4 gap-4'
    marker = 'children:"Yeni material"}),o.jsxs("div",{className:"grid grid-cols-1 md:grid-cols-3 gap-4"'
    if marker in s:
        s = s.replace(marker, marker.replace('md:grid-cols-3', 'md:grid-cols-4'), 1)

    path.write_text(s, encoding='utf-8')
    print('patched', path)
