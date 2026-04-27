from pathlib import Path

replacements = {
    r'.replace(\w/g,L=>L.toUpperCase())': r'.replace(/\b\w/g,L=>L.toUpperCase())',
    r'.replace(\w/g,w=>w.toUpperCase())': r'.replace(/\b\w/g,w=>w.toUpperCase())',
}

for rel in [
    r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js',
    r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js',
]:
    path = Path(rel)
    s = path.read_text(encoding='utf-8')
    for old, new in replacements.items():
        s = s.replace(old, new)
    path.write_text(s, encoding='utf-8')
    print('repaired', rel)
