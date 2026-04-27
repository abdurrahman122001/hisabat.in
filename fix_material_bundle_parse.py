from pathlib import Path

paths = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

bad_1 = r'.replace(\w/g,L=>L.toUpperCase())'
good_1 = r'.replace(/\b\w/g,L=>L.toUpperCase())'
bad_2 = r'.replace(\w/g,w=>w.toUpperCase())'
good_2 = r'.replace(/\b\w/g,w=>w.toUpperCase())'

for path in paths:
    s = path.read_text(encoding='utf-8')
    before = s.count(bad_1) + s.count(bad_2)
    s = s.replace(bad_1, good_1)
    s = s.replace(bad_2, good_2)
    path.write_text(s, encoding='utf-8')
    print(path)
    print('replaced', before)
    print('remaining_bad', s.count(bad_1) + s.count(bad_2))
