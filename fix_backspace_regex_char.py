from pathlib import Path

bad = '\x08\\w/g'
good = '\\b\\w/g'

for rel in [r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js', r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js']:
    path = Path(rel)
    s = path.read_text(encoding='utf-8')
    count = s.count(bad)
    s = s.replace(bad, good)
    path.write_text(s, encoding='utf-8')
    print(rel, count)
