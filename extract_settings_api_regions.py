from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for needle in ['printers_create.php','printers_update.php','materials_create.php','materials_update.php','printers_list.php','materials_list.php']:
    idx = s.find(needle)
    print('\n===', needle, idx, '===')
    if idx != -1:
        print(s[max(0, idx-1800):min(len(s), idx+2600)])
