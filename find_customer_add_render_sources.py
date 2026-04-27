from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['Object.entries(ue)','Object.keys(ue)','ue&&','materials.map(_=>o.jsx(z,{label:String(_.label??"")','Laravel','Texta']:
    idx = s.find(needle)
    print('\nNEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-1200):idx+2500])
