from pathlib import Path
import re
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('function _k(){')
end = s.find('function Nk(){', start)
chunk = s[start:end]
patterns = [
    r'Yt=S\.useCallback\(async .*?\),Mt=',
    r'Mt=async .*?},Ue=',
    r'Gt=\(.*?\),Kn=',
    r'Kn=\(.*?\),mr=',
    r'vi=async .*?finally\{_\(!1\)\}'
]
out = []
for pat in patterns:
    m = re.search(pat, chunk)
    out.append(f'PATTERN: {pat}\n')
    if m:
        out.append(m.group(0))
    else:
        out.append('NOT FOUND')
    out.append('\n' + '='*120 + '\n')
Path(r'c:\xampp\htdocs\Hesabat\work_add_helpers_precise.txt').write_text(''.join(out), encoding='utf-8')
print('written')
