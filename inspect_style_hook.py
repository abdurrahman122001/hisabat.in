from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
needle = 'S.useEffect(()=>{ce()},[ce]);const g='
i = s.find(needle)
print(i)
if i != -1:
    print(s[i-1200:i+1200])
