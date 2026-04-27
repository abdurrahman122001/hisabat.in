from pathlib import Path
s = Path(r'c:\xampp\htdocs\Hesabat\ui\assets\index-CC2b_5k0.js').read_text(encoding='utf-8')
start = s.find('ue=S.useMemo(()=>{const h={},b=A=>String(A??"").trim().toLowerCase();for(const _ of N){')
if start == -1:
    start = s.find('ue=S.useMemo(()=>{')
print('start', start)
if start != -1:
    end = s.find('},[N,w,ne]);', start)
    print('end', end)
    print(s[start:end+11])
