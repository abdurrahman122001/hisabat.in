from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'className:"w-full flex items-center gap-2"',
    'Material yaradıldı',
    'Materialı printerə əlavə et',
    'md:col-span-4',
]
for needle in needles:
    idx = s.find(needle)
    print('NEEDLE', needle, 'IDX', idx)
    if idx != -1:
        start = max(0, idx-180)
        end = idx + 260
        print(repr(s[start:end]))
        print('---')
