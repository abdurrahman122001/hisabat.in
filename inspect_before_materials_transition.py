from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
idx = s.find(',e==="materials"?o.jsxs(o.Fragment,{children:[')
print('idx', idx)
print(s[idx-700:idx+120])
