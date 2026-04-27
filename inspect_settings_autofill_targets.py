from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'price_key (konica/roland/laser',
    'Yeni printer',
    'Yeni material',
    'label:"price_key',
    'label:"label",value:W',
    'label:"key",value:R',
]
for needle in needles:
    idx = s.find(needle)
    print('\nNEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-1200):idx+2800])
        print('\n---\n')
