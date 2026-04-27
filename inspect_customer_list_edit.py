from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = 'const w=L.client;W({client_id:String(w.client_id||T.client_id)'
i = s.find(needle)
print('index', i)
if i != -1:
    print(s[max(0, i-5000):i+22000])
