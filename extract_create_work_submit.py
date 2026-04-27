from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['/hesabat/api/create_work.php', 'Xəta baş verdi', 'Material sətri natamamdır', 'stock', 'price_per_m2']:
    idx = s.find(needle)
    print('\nNEEDLE', needle, 'IDX', idx)
    if idx != -1:
        print(s[max(0, idx-12000):min(len(s), idx+18000)])
        print('\n' + '='*120 + '\n')
