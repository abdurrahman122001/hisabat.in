from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'b.name==="Laser"?"Laser Cutter":b.name',
    'b.materials.map(_=>o.jsx(z,{label:String(_.label??"")',
    'fetch("/hesabat/api/materials_list.php")',
    'price_key:Ii,status:1})}),Z.forEach',
]
for needle in needles:
    idx = s.find(needle)
    print('NEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-1500):idx+3000])
        print('\n---\n')
