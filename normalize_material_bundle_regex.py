from pathlib import Path
import re

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

patterns = [
    (re.compile(r'\.replace\(\\w/g,L=>L\.toUpperCase\(\)\)'), r'.replace(/\\b\\w/g,L=>L.toUpperCase())'),
    (re.compile(r'\.replace\(\\w/g,w=>w\.toUpperCase\(\)\)'), r'.replace(/\\b\\w/g,w=>w.toUpperCase())'),
    (re.compile(r'\.replace\(\\\\w/g,L=>L\.toUpperCase\(\)\)'), r'.replace(/\\b\\w/g,L=>L.toUpperCase())'),
    (re.compile(r'\.replace\(\\\\w/g,w=>w\.toUpperCase\(\)\)'), r'.replace(/\\b\\w/g,w=>w.toUpperCase())'),
]

for path in paths:
    s = path.read_text(encoding='utf-8')
    total = 0
    for pattern, repl in patterns:
        s, count = pattern.subn(repl, s)
        total += count
    path.write_text(s, encoding='utf-8')
    print(path)
    print('total_fixed', total)
    idx = s.find('replace(/\\b\\w/g')
    print('sample', s[idx-80:idx+80] if idx != -1 else 'no sample')
