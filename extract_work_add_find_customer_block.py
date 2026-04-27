from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = '/hesabat/api/find_customer.php'
idx = s.find(needle)
print('idx', idx)
if idx != -1:
    start = max(0, idx - 12000)
    end = min(len(s), idx + 18000)
    print(s[start:end])
