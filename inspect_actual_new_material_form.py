from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
anchor = 'children:"Yeni material"'
idx = s.find(anchor)
print('anchor', idx)
if idx != -1:
    snippet = s[idx:idx+2500]
    Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts\actual_new_material_form.txt').write_text(snippet, encoding='utf-8')
    print(snippet)
