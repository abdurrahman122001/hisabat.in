from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('function Mk(){')
print('start', start)
if start != -1:
    print(s[start:start+7000])
