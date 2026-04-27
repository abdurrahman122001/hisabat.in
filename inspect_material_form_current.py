from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'children:"Yeni material"',
    'label:"key (məs: banner_matt)"',
    'label:"label"',
    'body:JSON.stringify({key:R,label:W,status:1})'
]
for needle in needles:
    idx = s.find(needle)
    print('\nNEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-1000):idx+2500])
        print('\n---\n')
