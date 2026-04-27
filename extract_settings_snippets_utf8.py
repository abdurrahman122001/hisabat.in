from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = {
    'settings_api_block': '/hesabat/api/materials_update.php',
    'settings_printers_header': 'Yeni printer',
    'settings_materials_header': 'Yeni material',
    'settings_printers_tab': '"Printers"',
    'settings_materials_tab': '"Materials"',
}
base = Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts')
base.mkdir(exist_ok=True)
for name, needle in needles.items():
    idx = s.find(needle)
    text = f'needle={needle}\nindex={idx}\n\n'
    if idx != -1:
        text += s[max(0, idx-6000):min(len(s), idx+14000)]
    (base / f'{name}.txt').write_text(text, encoding='utf-8')
print('done')
