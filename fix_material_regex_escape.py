from pathlib import Path

for rel in [r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js', r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js']:
    path = Path(rel)
    s = path.read_text(encoding='utf-8')
    s = s.replace('.replace(\w/g,L=>L.toUpperCase())', '.replace(/\\b\\w/g,L=>L.toUpperCase())')
    s = s.replace('.replace(\w/g,w=>w.toUpperCase())', '.replace(/\\b\\w/g,w=>w.toUpperCase())')
    path.write_text(s, encoding='utf-8')
    print('fixed', rel)
