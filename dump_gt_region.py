from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
idx = s.find('Gt=(')
print('idx', idx)
if idx != -1:
    snippet = s[max(0, idx-6000):min(len(s), idx+20000)]
    Path(r'c:\xampp\htdocs\Hesabat\gt_region.txt').write_text(snippet, encoding='utf-8')
    print(snippet)
