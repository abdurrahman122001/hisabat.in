from pathlib import Path

path = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js')
s = path.read_text(encoding='utf-8')
replacements = {
    'price_per_m2:se||Y[0].price_per_m2': 'price_per_m2:se||""',
    'price_per_m2:Z||Y[0].price_per_m2': 'price_per_m2:Z||""',
    'price_per_m2:bi||q[U].price_per_m2': 'price_per_m2:bi||""',
    'price_per_m2:we||q[U].price_per_m2': 'price_per_m2:we||""',
}
counts = {old: s.count(old) for old in replacements}
print(counts)
for old, new in replacements.items():
    if s.count(old) != 1:
        raise SystemExit(f'Expected exactly one match for: {old}')
    s = s.replace(old, new, 1)
path.write_text(s, encoding='utf-8')
print('patched')
