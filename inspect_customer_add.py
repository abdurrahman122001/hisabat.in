from pathlib import Path

for rel in [r'ui/assets/index-CC2b_5k0.js', r'deploy_hesabat_root/assets/index-CC2b_5k0.js']:
    path = Path(rel)
    s = path.read_text(encoding='utf-8')
    start = s.find('function jk(){')
    end = s.find('function _k(){')
    print('=' * 80)
    print(path)
    print('start=', start, 'end=', end)
    if start == -1 or end == -1 or end <= start:
        print('COMPONENT NOT FOUND')
        continue
    chunk = s[start:end]
    print(chunk[:12000])
