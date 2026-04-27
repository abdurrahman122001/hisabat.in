from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = '/hesabat/api/find_customer.php'
idx = s.find(needle)
print('needle idx', idx)
if idx != -1:
    start = s.rfind('function ', 0, idx)
    print('function start', start)
    print(s[start:start+22000])
