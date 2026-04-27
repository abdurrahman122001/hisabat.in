from pathlib import Path
import re
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
patterns = [
    r'price_per_m2[^\n]{0,800}width_cm[^\n]{0,800}height_cm[^\n]{0,800}qty',
    r'width_cm[^\n]{0,800}height_cm[^\n]{0,800}qty[^\n]{0,800}price_per_m2',
    r'toFixed\(2\)',
    r'reduce\([^\n]{0,1200}',
    r'useMemo\([^\n]{0,1800}',
    r'm\.reduce\([^\n]{0,1800}',
    r'Gt\([^\)]*\)[^\n]{0,500}Number',
]
for pat in patterns:
    print('\n===', pat, '===')
    ms = list(re.finditer(pat, s))
    print('count', len(ms))
    for m in ms[:8]:
        a=max(0,m.start()-600); b=min(len(s),m.end()+1800)
        print('--- at', m.start(), '---')
        print(s[a:b])
