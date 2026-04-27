from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
for idx in [317800,318500,319700,320000]:
    print('\nIDX', idx)
    print(s[idx:idx+1800])
