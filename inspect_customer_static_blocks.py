from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'children:"Konica"',
    'children:"Roland"',
    'children:"Laser Cutter"',
    'banner_440_white',
    'graw_wood',
    'Object.entries(ue).map'
]
for needle in needles:
    idx = s.find(needle)
    print('\nNEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-1800):idx+4200])
        print('\n---\n')
