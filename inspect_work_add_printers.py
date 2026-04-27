from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in [
    '/hesabat/api/printers_list.php',
    'Printer seç',
    'printer:"",material:""',
    'Printer seç (işə görə)',
    'Seçin...'
]:
    i = s.find(needle)
    print('=' * 80)
    print('needle:', needle, 'index:', i)
    if i != -1:
        start = max(0, i - 1800)
        end = min(len(s), i + 3200)
        print(s[start:end])
