from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needles = {
    'create': 'body:JSON.stringify({key:R,label:W,status:1})',
    'assign': 'body:JSON.stringify({key:qe,label:Ve,categories:ce,category:ce[0]||"",status:1})',
    'table_head': 'children:"Əməliyyat"',
    'materials_tab': 'e==="materials"?o.jsxs(o.Fragment',
    'assign_title': 'Materialı printerə əlavə et',
    'update_call': '/hesabat/api/materials_update.php',
}
out = []
for name, needle in needles.items():
    idx = s.find(needle)
    out.append(f'\n=== {name} {idx} ===\n')
    if idx != -1:
        out.append(s[max(0, idx-1500):min(len(s), idx+3500)])
Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts\ui_material_snippets.txt').write_text(''.join(out), encoding='utf-8')
print('done')
