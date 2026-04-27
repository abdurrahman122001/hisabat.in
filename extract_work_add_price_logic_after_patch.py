from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = [
    'const Gt=',
    'const Kn=',
    'const mr=',
    'Number.isFinite(mr)?mr:0',
    'Y?Y.toFixed(2):"0.00"',
    'const yi=async',
    'find_customer.php?client_id=',
]
for needle in needles:
    idx = s.find(needle)
    print(f'\n=== {needle} @ {idx} ===')
    if idx != -1:
        print(s[max(0, idx-800):min(len(s), idx+2200)])
