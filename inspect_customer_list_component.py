from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('function Mk(){')
end = s.find('const Ak=[{id:"stock"', start)
print('start', start)
print('end', end)
if start != -1 and end != -1:
    chunk = s[start:end]
    print(chunk[:40000])
