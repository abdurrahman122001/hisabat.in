from pathlib import Path
for rel in [r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js', r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js']:
    s = Path(rel).read_text(encoding='utf-8')
    i = s.find('Object.keys(ue).length>0&&o.jsx("div"')
    print('='*80)
    print(rel)
    print(i)
    if i != -1:
        print(s[i:i+2600])
