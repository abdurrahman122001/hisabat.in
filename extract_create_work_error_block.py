from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = '/hesabat/api/create_work.php'
idx = s.find(needle)
print('idx', idx)
if idx != -1:
    start = s.rfind('try{const', 0, idx)
    if start == -1:
        start = max(0, idx - 5000)
    end = s.find('finally{_(!1)}', idx)
    if end == -1:
        end = idx + 12000
    else:
        end += len('finally{_(!1)}')
    out = s[start:end]
    Path(r'c:\xampp\htdocs\Hesabat\create_work_error_block.txt').write_text(out, encoding='utf-8')
    print(out)
