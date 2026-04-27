from pathlib import Path

files = [
    Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js'),
    Path(r'c:\xampp\htdocs\Hesabat\deploy_hesabat_root\assets\index-CC2b_5k0.js'),
]

for path in files:
    s = path.read_text(encoding='utf-8')
    count = s.count('key:re,className:')
    if count == 0:
        raise SystemExit(f'No broken key found in {path}')
    s = s.replace('key:re,className:', 'className:', count)
    path.write_text(s, encoding='utf-8')
    print(f'Patched {path} (removed {count} broken key refs)')
