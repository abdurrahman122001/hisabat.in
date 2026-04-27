from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = 'Object.keys(ue).length>0&&o.jsx("div",{className:"space-y-6"'
i = s.find(needle)
print(i)
if i != -1:
    print(s[i:i+3000])
