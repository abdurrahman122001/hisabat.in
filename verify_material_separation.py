from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
print('create_card', 'Yeni material' in s and 'categories:[]' in s)
print('assign_card', 'Materialı printerə əlavə et' in s)
print('printer_select_present', s.count('Printer seç'))
