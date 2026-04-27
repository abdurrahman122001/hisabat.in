from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['const[R,H]=S.useState("")', 'const[W,J]=S.useState("")', 'const[ce,ee]=S.useState([])', 'materials_create.php', 'function Rk(']:
    idx = s.find(needle)
    print('NEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-400):idx+600])
        print('\n---\n')
