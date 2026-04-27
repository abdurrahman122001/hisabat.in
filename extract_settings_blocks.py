from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle,name in [('e==="printers"','printers_block.txt'),('e==="materials"','materials_block.txt')]:
    idx = s.find(needle)
    text = f'needle={needle}\nindex={idx}\n\n'
    if idx != -1:
        text += s[max(0, idx-3000):min(len(s), idx+18000)]
    Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts', name).write_text(text, encoding='utf-8')
print('done')
