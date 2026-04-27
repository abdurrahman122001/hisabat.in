from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['Materialı printerə əlavə et','Mövcud material seç','value:qe','value:Ve','value:W']:
    idx = s.find(needle)
    print('NEEDLE', needle, idx)
    if idx != -1:
        print(s[max(0, idx-500):idx+900])
        print('\n---\n')
