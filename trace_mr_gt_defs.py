from pathlib import Path
import re
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
patterns = [
    r'mr\s*=\s*[^;]{0,1200}',
    r'Gt\s*=\s*\([^\)]*\)=>[^;]{0,1500}',
    r'Kn\s*=\s*\([^\)]*\)=>[^;]{0,1500}',
    r'\[mr,[^\]]*\]=',
    r'useMemo\([^\)]{0,2000}mr',
    r'Gt\([^\)]*\)',
]
for pat in patterns:
    print('\n=== PATTERN ===', pat)
    matches = list(re.finditer(pat, s))
    print('count=', len(matches))
    for m in matches[:5]:
        a=max(0,m.start()-400); b=min(len(s),m.end()+1200)
        print('--- match at', m.start(), '---')
        print(s[a:b])
