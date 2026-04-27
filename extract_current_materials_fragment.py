from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find(',e==="materials"?o.jsxs(o.Fragment,{children:[')
end = s.find(']}):null]})}function Lk(){')
frag = s[start:end]
print(frag)
