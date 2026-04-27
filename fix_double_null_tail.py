from pathlib import Path

bad = '})]})})})]}):null]}):null]})}'
good = '})]})})})]}):null]})}'

for rel in [r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js', r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js']:
    path = Path(rel)
    s = path.read_text(encoding='utf-8')
    count = s.count(bad)
    print(rel, 'bad_count', count)
    s = s.replace(bad, good)
    path.write_text(s, encoding='utf-8')
