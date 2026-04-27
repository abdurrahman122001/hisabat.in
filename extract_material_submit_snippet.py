from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['materials_create.php','[ce,ee]=S.useState("")','Printer seç']:
    idx = s.find(needle)
    print('NEEDLE', needle, 'IDX', idx)
    if idx != -1:
        start = max(0, idx-700)
        end = min(len(s), idx+1800)
        out_dir = Path(r'c:\xampp\htdocs\Hesabat\tmp_settings_extracts')
        out_name = needle.replace('/','_').replace('"','').replace('[','').replace(']','').replace(',','_').replace(' ','_') + '.txt'
        (out_dir / out_name).write_text(s[start:end], encoding='utf-8')
