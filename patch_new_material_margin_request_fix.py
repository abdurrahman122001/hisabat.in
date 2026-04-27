from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

for path in files:
    s = path.read_text(encoding='utf-8')
    s = s.replace(
        'body:JSON.stringify({key:R,label:W,status:1})',
        'body:JSON.stringify({key:R,label:W,stock_margin:Ze,status:1})'
    )
    s = s.replace(
        'H(""),J(""),l("Material yaradıldı")',
        'H(""),J(""),rt("0"),l("Material yaradıldı")'
    )
    path.write_text(s, encoding='utf-8')
    print('patched', path)
