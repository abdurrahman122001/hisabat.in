from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['ne=S.useMemo(()=>({konica:new Set([', 'Object.keys(ue).length>0&&o.jsx("div",{className:"space-y-6",children:Object.entries(ue).map', 'const h={},b=A=>String(A??"").trim().toLowerCase()']:
    idx = s.find(needle)
    print('NEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-1200):idx+3500])
        print('\n---\n')
