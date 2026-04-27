from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
keys = [
    '/hesabat/api/find_customer.php',
    'price_per_m2',
    'items:',
    'printer:"",material:"",width_cm:"",height_cm:"",qty:"",price_per_m2:""',
    'Material seç',
    'Printer seç'
]
for key in keys:
    idx = s.find(key)
    print('\nKEY:', key, 'IDX:', idx)
    if idx != -1:
        start = max(0, idx-2500)
        end = min(len(s), idx+7000)
        print(s[start:end])
        print('\n' + '='*120 + '\n')
