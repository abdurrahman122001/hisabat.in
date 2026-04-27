from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = 'children:[o.jsx(Fs,{size:16}),"Yarat"]})})]})]}),o.jsxs(Pt'
idx = s.find(needle)
start = max(0, idx-900)
end = idx + len(needle) + 100
print(s[start:end])
