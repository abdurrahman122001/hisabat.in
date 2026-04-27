from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'const Gt=',
    'const Kn=',
    'const xi=',
    'price_per_m2:se||Y[0].price_per_m2',
    'price_per_m2:Z||Y[0].price_per_m2',
    'price_per_m2:bi||q[U].price_per_m2',
    'price_per_m2:we||q[U].price_per_m2',
    'Number.isFinite(mr)?mr:0',
    'Y?Y.toFixed(2):"0.00"'
]
for needle in needles:
    i = s.find(needle)
    print('\n===', needle, 'IDX', i, '===')
    if i != -1:
        print(s[max(0, i-300):min(len(s), i+900)])
